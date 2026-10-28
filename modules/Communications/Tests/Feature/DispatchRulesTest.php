<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\CommunicationChannelCode;
use App\Enums\DispatchMode;
use App\Enums\MessageStatus;
use App\Enums\MessageTrigger;
use App\Models\CommunicationChannel;
use App\Models\DispatchRule;
use App\Models\MessageTemplate;
use App\Models\PermissionGroup;
use App\Models\Position;
use App\Models\SentMessage;
use App\Models\TrackingDevice;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Communications\Support\WhatsAppConsents;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC20', partial: true)]
class DispatchRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        CommunicationChannel::forCode(CommunicationChannelCode::Email)->update(['is_auto_dispatch_enabled' => true]);
    }

    private function silentVessel(): Vessel
    {
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);

        return $device->vessel;
    }

    public function test_an_automatic_rule_sends_one_message_per_silent_vessel_and_skips_emitting_ones(): void
    {
        $silent = $this->silentVessel();
        $emitting = TrackingDevice::factory()->create();
        Position::factory()->for($emitting->vessel)->create(['recorded_at' => now()->subMinutes(5)]);
        DispatchRule::factory()->create();

        $this->artisan('communications:dispatch')->assertSuccessful();

        $message = SentMessage::sole();
        $this->assertSame($silent->id, $message->vessel_id);
        $this->assertSame(MessageStatus::Sent, $message->status);
        $this->assertSame(MessageTrigger::Rule, $message->triggered_by);
        $this->assertSame('armement@example.ci', $message->recipient);
    }

    #[Assumes('H-5-5')]
    public function test_the_daily_cap_limits_the_messages_of_a_rule_per_day(): void
    {
        foreach (range(1, 4) as $ignored) {
            $this->silentVessel();
        }
        DispatchRule::factory()->create(['daily_cap' => 2, 'frequency_minutes' => null]);

        $this->artisan('communications:dispatch');
        $this->artisan('communications:dispatch');

        $this->assertSame(2, SentMessage::count());

        $this->travel(1)->day();
        $this->artisan('communications:dispatch');

        $this->assertSame(4, SentMessage::count());
    }

    public function test_the_frequency_spaces_the_reminders_of_a_vessel(): void
    {
        $this->silentVessel();
        DispatchRule::factory()->create(['frequency_minutes' => 120, 'daily_cap' => 10]);

        $this->artisan('communications:dispatch');
        $this->travel(60)->minutes();
        $this->artisan('communications:dispatch');
        $this->assertSame(1, SentMessage::count());

        $this->travel(90)->minutes();
        $this->artisan('communications:dispatch');
        $this->assertSame(2, SentMessage::count());
    }

    #[Assumes('H-5-4')]
    public function test_an_acknowledged_message_stops_the_reminders_of_that_vessel_only(): void
    {
        $first = $this->silentVessel();
        $second = $this->silentVessel();
        $rule = DispatchRule::factory()->create(['frequency_minutes' => 30, 'daily_cap' => 10, 'stop_condition' => 'acknowledged']);
        $this->artisan('communications:dispatch');
        SentMessage::where('vessel_id', $first->id)->update(['status' => MessageStatus::Acknowledged->value, 'acknowledged_at' => now()]);

        $this->travel(60)->minutes();
        $this->artisan('communications:dispatch');

        $this->assertSame(1, SentMessage::where('vessel_id', $first->id)->count());
        $this->assertSame(2, SentMessage::where('vessel_id', $second->id)->count());
        $this->assertSame(3, $rule->messages()->count());
    }

    #[Assumes('H-5-4')]
    public function test_a_received_reply_stops_the_reminders_when_it_is_the_stop_condition(): void
    {
        $this->silentVessel();
        DispatchRule::factory()->create(['frequency_minutes' => 30, 'daily_cap' => 10, 'stop_condition' => 'reply_received']);
        $this->artisan('communications:dispatch');
        SentMessage::query()->update(['reply_received' => true]);

        $this->travel(60)->minutes();
        $this->artisan('communications:dispatch');

        $this->assertSame(1, SentMessage::count());
    }

    #[Assumes('H-5-6')]
    public function test_a_rule_with_validation_prepares_the_message_until_a_user_sends_it(): void
    {
        $this->silentVessel();
        DispatchRule::factory()->create(['mode' => DispatchMode::WithValidation]);
        $agent = $this->signInAs(Role::Agent);

        $this->artisan('communications:dispatch');

        $message = SentMessage::sole();
        $this->assertSame(MessageStatus::Prepared, $message->status);
        $this->assertNull($message->sent_at);
        $this->assertSame(1, $agent->notifications()->where('data->key', 'communications.prepared_messages')->count());

        $this->post(route('communications.messages.send', $message))->assertRedirect();

        $this->assertSame(MessageStatus::Sent, $message->fresh()->status);
        $this->assertSame($agent->id, $message->fresh()->user_id);
        $this->post(route('communications.messages.send', $message))->assertSessionHasErrors('message');
    }

    #[Assumes('H-5-6')]
    public function test_an_automatic_rule_only_sends_when_the_channel_allows_automatic_dispatch(): void
    {
        CommunicationChannel::forCode(CommunicationChannelCode::Email)->update(['is_auto_dispatch_enabled' => false]);
        $this->silentVessel();
        DispatchRule::factory()->create();

        $this->artisan('communications:dispatch');

        $this->assertSame(MessageStatus::Prepared, SentMessage::sole()->status);
    }

    #[Assumes('H-5-2')]
    public function test_a_whatsapp_rule_does_nothing_without_consent(): void
    {
        CommunicationChannel::forCode(CommunicationChannelCode::WhatsApp)->update(['is_auto_dispatch_enabled' => true]);
        $this->silentVessel();
        $template = MessageTemplate::factory()->whatsapp()->create();
        DispatchRule::factory()->create(['message_template_id' => $template->id, 'conditions' => ['recipient' => '+2250700000005']]);

        $this->artisan('communications:dispatch');
        $this->assertSame(0, SentMessage::count());

        app(WhatsAppConsents::class)->grant('+2250700000005');
        $this->artisan('communications:dispatch');
        $this->assertSame(MessageStatus::Sent, SentMessage::sole()->status);
    }

    public function test_inactive_rules_and_rules_without_recipient_are_ignored(): void
    {
        $this->silentVessel();
        DispatchRule::factory()->create(['is_active' => false]);
        DispatchRule::factory()->create(['conditions' => []]);

        $this->artisan('communications:dispatch');

        $this->assertSame(0, SentMessage::count());
    }

    public function test_the_command_does_nothing_when_dispatch_is_disabled(): void
    {
        config(['communications.dispatch_enabled' => false]);
        $this->silentVessel();
        DispatchRule::factory()->create();

        $this->artisan('communications:dispatch')->assertSuccessful();

        $this->assertSame(0, SentMessage::count());
    }

    public function test_the_run_is_audited_under_the_system_actor_in_the_dispatch_group(): void
    {
        $this->silentVessel();
        DispatchRule::factory()->create();

        $this->artisan('communications:dispatch');

        $system = User::where('email', config('communications.system_actor_email'))->sole();
        $this->assertSame('system', $system->type->value);
        $this->assertTrue($system->permissionGroups()->where('code', 'automatic_dispatch')->exists());
        $this->assertSame(1, Activity::where('event', 'communications.dispatch_rule')->where('causer_id', $system->id)->count());
    }

    public function test_the_prepared_messages_alert_respects_its_cooldown(): void
    {
        $this->silentVessel();
        DispatchRule::factory()->create(['mode' => DispatchMode::WithValidation, 'daily_cap' => 10]);
        $agent = $this->signInAs(Role::Agent);

        $this->artisan('communications:dispatch');
        $this->artisan('communications:dispatch');
        $this->assertSame(1, $agent->notifications()->count());

        $this->travel(5)->hours();
        $this->artisan('communications:dispatch');
        $this->assertSame(2, $agent->notifications()->count());
    }

    #[Assumes('H-5-7')]
    public function test_a_rule_restricted_to_a_group_can_only_be_edited_by_its_members(): void
    {
        $group = PermissionGroup::where('code', 'automatic_dispatch')->sole();
        $rule = DispatchRule::factory()->create(['editable_by_permission_group_id' => $group->id]);
        $director = $this->signInAs(Role::Director);
        $payload = [
            'message_template_id' => $rule->message_template_id,
            'trigger_event' => 'beacon_silent',
            'recipient' => 'other@example.ci',
            'mode' => 'automatic',
            'is_active' => 1,
        ];

        $this->put(route('communications.rules.update', $rule), $payload)->assertForbidden();
        $this->assertSame('armement@example.ci', $rule->fresh()->conditions['recipient']);

        $director->permissionGroups()->attach($group);
        $this->put(route('communications.rules.update', $rule), $payload)->assertRedirect(route('communications.rules.index'));
        $this->assertSame('other@example.ci', $rule->fresh()->conditions['recipient']);
    }

    #[Assumes('H-5-7')]
    public function test_only_holders_of_the_dispatch_permission_manage_rules(): void
    {
        $this->signInAs(Role::Agent);
        $payload = ['message_template_id' => MessageTemplate::factory()->create()->id, 'trigger_event' => 'beacon_silent', 'recipient' => 'a@example.ci', 'mode' => 'automatic', 'is_active' => 1];

        $this->get(route('communications.rules.index'))->assertOk();
        $this->get(route('communications.rules.create'))->assertForbidden();
        $this->post(route('communications.rules.store'), $payload)->assertForbidden();

        $this->signInAs(Role::Director);
        $this->post(route('communications.rules.store'), $payload)->assertRedirect(route('communications.rules.index'));
        $this->assertSame(1, DispatchRule::count());
        $this->post(route('communications.rules.store'), [...$payload, 'trigger_event' => 'unknown'])->assertSessionHasErrors('trigger_event');
        $this->get(route('communications.rules.create'))->assertOk();
    }
}

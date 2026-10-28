<?php

namespace Modules\Communications\Tests\Feature;

use App\Models\AiAgentAction;
use App\Models\CommunicationChannel;
use App\Models\DispatchRule;
use App\Models\EmailConnector;
use App\Models\InternalNote;
use App\Models\MessageTemplate;
use App\Models\SentMessage;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommunicationsSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_factory_builds_a_valid_row(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        foreach ([CommunicationChannel::class, EmailConnector::class, MessageTemplate::class, DispatchRule::class, SentMessage::class, AiAgentAction::class, InternalNote::class] as $model) {
            $this->assertTrue($model::factory()->create()->exists, $model);
        }
    }

    public function test_enumerations_are_guarded_by_check_constraints(): void
    {
        $this->expectException(QueryException::class);

        DB::table('message_templates')->insert(['code' => 'bad', 'channel' => 'sms', 'body' => 'x']);
    }

    public function test_the_event_summaries_are_translated_for_each_reader(): void
    {
        $this->signInAs(Role::Agent);
        $template = MessageTemplate::factory()->create(['code' => 'vms-reminder']);
        $this->post(route('communications.messages.store'), [
            'channel' => 'email',
            'message_template_id' => $template->id,
            'vessel_id' => Vessel::factory()->create()->id,
            'recipient' => 'armateur@example.ci',
        ]);
        $event = VesselEvent::sole();

        app()->setLocale('fr');
        $this->assertStringContainsString('envoyé à armateur@example.ci', $event->summary());
        app()->setLocale('en');
        $this->assertStringContainsString('sent to armateur@example.ci', $event->summary());
    }

    public function test_every_screen_renders_in_both_languages_without_missing_translations(): void
    {
        $this->signInAs(Role::Director);
        $message = SentMessage::factory()->create();
        $template = MessageTemplate::factory()->create();
        $rule = DispatchRule::factory()->create();
        InternalNote::factory()->create(['recipient_user_id' => auth()->id()]);

        $urls = [
            route('communications.messages.index'), route('communications.messages.create'), route('communications.messages.show', $message),
            route('communications.notes.index'), route('communications.notes.create'),
            route('communications.templates.index'), route('communications.templates.create'), route('communications.templates.edit', $template),
            route('communications.rules.index'), route('communications.rules.create'), route('communications.rules.edit', $rule),
            route('communications.channels.index'),
        ];

        foreach (['en', 'fr'] as $locale) {
            $this->withSession(['locale' => $locale]);
            app()->setLocale($locale);

            foreach ($urls as $url) {
                $this->get($url)->assertOk()->assertDontSee('communications::')->assertDontSee('enums.');
            }
        }
    }
}

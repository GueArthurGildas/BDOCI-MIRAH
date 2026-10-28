<?php

namespace Modules\Communications\Tests\Feature;

use App\Enums\MessageStatus;
use App\Models\SentMessage;
use App\Models\VesselEvent;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC20', partial: true)]
class DeliveryReportWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        config(['communications.webhook_secret' => 'top-secret']);
    }

    private function report(array $payload, ?string $secret = 'top-secret')
    {
        return $this->postJson(route('communications.webhooks.delivery'), $payload, $secret === null ? [] : ['X-Webhook-Secret' => $secret]);
    }

    public function test_a_provider_acknowledges_a_sent_message_without_a_session_or_csrf_token(): void
    {
        $message = SentMessage::factory()->create();

        $this->report(['message_id' => $message->id, 'event' => 'acknowledged', 'reply_received' => true])
            ->assertOk()
            ->assertJson(['message_id' => $message->id, 'status' => 'acknowledged']);

        $message->refresh();
        $this->assertSame(MessageStatus::Acknowledged, $message->status);
        $this->assertNotNull($message->acknowledged_at);
        $this->assertTrue($message->reply_received);
        $this->assertSame('message.acknowledged', VesselEvent::sole()->type);
    }

    public function test_a_bounce_marks_the_message_and_reaches_the_vessel_journal(): void
    {
        $message = SentMessage::factory()->create();

        $this->report(['message_id' => $message->id, 'event' => 'bounced'])->assertOk();

        $this->assertSame(MessageStatus::Bounced, $message->fresh()->status);
        $this->assertSame('message.bounced', VesselEvent::sole()->type);
    }

    public function test_a_report_about_a_message_that_is_not_in_the_sent_state_changes_nothing(): void
    {
        $message = SentMessage::factory()->status(MessageStatus::Prepared)->create();

        $this->report(['message_id' => $message->id, 'event' => 'acknowledged'])->assertOk()->assertJson(['status' => 'prepared']);

        $this->assertSame(MessageStatus::Prepared, $message->fresh()->status);
        $this->assertSame(0, VesselEvent::count());
    }

    public function test_the_shared_secret_is_required(): void
    {
        $message = SentMessage::factory()->create();

        $this->report(['message_id' => $message->id, 'event' => 'acknowledged'], null)->assertForbidden();
        $this->report(['message_id' => $message->id, 'event' => 'acknowledged'], 'wrong')->assertForbidden();

        config(['communications.webhook_secret' => null]);
        $this->report(['message_id' => $message->id, 'event' => 'acknowledged'], '')->assertForbidden();

        $this->assertSame(MessageStatus::Sent, $message->fresh()->status);
    }

    public function test_the_report_is_validated(): void
    {
        $this->report(['message_id' => 999999, 'event' => 'acknowledged'])->assertUnprocessable();
        $this->report(['message_id' => SentMessage::factory()->create()->id, 'event' => 'sent'])->assertUnprocessable();
    }
}

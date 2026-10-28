<?php

namespace Tests\Feature\Core;

use App\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use App\Models\Document;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Support\Core\ReferenceLists;
use App\Support\Core\VesselEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class VesselCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_are_recorded_in_the_vessel_journal(): void
    {
        $vessel = Vessel::factory()->create();

        app(VesselEventRecorder::class)->record($vessel, 'logbook', 'channel_message', 'WhatsApp message received');

        $this->assertSame(1, $vessel->events()->count());
        $this->assertSame('logbook', $vessel->events()->first()->module);
    }

    public function test_current_state_is_unique_per_vessel(): void
    {
        $vessel = Vessel::factory()->create();

        VesselCurrentState::create(['vessel_id' => $vessel->id]);

        $this->assertSame(0, $vessel->currentState->open_infractions);
        $this->assertSame('compliant', $vessel->currentState->traffic_light->value);
    }

    public function test_custom_fields_store_values_and_reject_unknown_codes(): void
    {
        CustomFieldDefinition::create([
            'subject_type' => (new Vessel)->getMorphClass(),
            'code' => 'hull_color',
            'label' => ['fr' => 'Couleur de coque', 'en' => 'Hull colour'],
            'type' => CustomFieldType::Text,
        ]);
        $vessel = Vessel::factory()->create();

        $vessel->setCustomField('hull_color', 'bleu');
        $this->assertSame('bleu', $vessel->customField('hull_color'));

        $this->expectException(InvalidArgumentException::class);
        $vessel->setCustomField('unknown', 'x');
    }

    public function test_frozen_document_is_immutable(): void
    {
        $document = Document::create(['name' => 'rapport.pdf', 'mime_type' => 'application/pdf', 'disk' => 'local', 'path' => 'x/rapport.pdf', 'sha256' => str_repeat('a', 64)]);
        $document->freeze();

        $this->expectException(LogicException::class);
        $document->fresh()->update(['name' => 'autre.pdf']);
    }

    public function test_reference_lists_are_defined_and_listed(): void
    {
        $lists = app(ReferenceLists::class);
        $lists->define('logbook_channel', ['whatsapp' => ['fr' => 'WhatsApp', 'en' => 'WhatsApp'], 'sms' => ['fr' => 'SMS', 'en' => 'SMS']]);

        $this->assertSame(['whatsapp', 'sms'], array_keys($lists->options('logbook_channel')));
    }
}

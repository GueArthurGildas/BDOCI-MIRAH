<?php

namespace Tests\Feature\Kernel;

use App\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Documents\CommentService;
use App\Support\Documents\DocumentStore;
use App\Support\Fields\CustomFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GenericServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_stored_document_carries_its_fingerprint_and_links_to_an_object(): void
    {
        Storage::fake(DocumentStore::DISK);
        $store = app(DocumentStore::class);
        $vessel = Vessel::factory()->create();
        $user = User::factory()->create();

        $document = $store->store(UploadedFile::fake()->createWithContent('license.pdf', 'content'), $user);
        $store->attach($document, $vessel);
        $store->attach($document, $vessel);

        $this->assertSame(hash('sha256', 'content'), $document->sha256_fingerprint);
        $this->assertSame($user->id, $document->filed_by);
        Storage::disk(DocumentStore::DISK)->assertExists($document->path);
        $this->assertCount(1, $store->for($vessel));
        $this->assertTrue($store->isIntact($document));
    }

    public function test_tampering_with_a_stored_file_is_detected(): void
    {
        Storage::fake(DocumentStore::DISK);
        $store = app(DocumentStore::class);
        $document = $store->storeContent('report.pdf', 'original', 'application/pdf', freeze: true);

        Storage::disk(DocumentStore::DISK)->put($document->path, 'tampered');

        $this->assertTrue($document->is_frozen);
        $this->assertFalse($store->isIntact($document));
    }

    public function test_comments_are_listed_per_object_latest_first(): void
    {
        $service = app(CommentService::class);
        $user = User::factory()->create();
        $vessel = Vessel::factory()->create();
        $other = Vessel::factory()->create();

        $service->add($vessel, $user, 'first');
        $service->add($vessel, $user, 'second');
        $service->add($other, $user, 'elsewhere');

        $this->assertSame(['second', 'first'], $service->for($vessel)->pluck('body')->all());
    }

    public function test_custom_field_values_are_typed_and_read_back(): void
    {
        $vessel = Vessel::factory()->create();
        foreach (['crew' => CustomFieldType::Number, 'inspected_on' => CustomFieldType::Date, 'insured' => CustomFieldType::Boolean, 'note' => CustomFieldType::Text] as $code => $type) {
            CustomFieldDefinition::create(['object_type' => $vessel->getMorphClass(), 'code' => $code, 'label' => $code, 'type' => $type]);
        }
        $fields = app(CustomFields::class);

        $fields->set($vessel, 'crew', '12');
        $fields->set($vessel, 'inspected_on', '2026-10-01');
        $fields->set($vessel, 'insured', true);
        $fields->set($vessel, 'crew', 14);

        $values = $fields->valuesFor($vessel);

        $this->assertSame(14, $values['crew']);
        $this->assertTrue($values['insured']);
        $this->assertSame('2026-10-01', $values['inspected_on']->toDateString());
        $this->assertNull($values['note']);
    }

    public function test_a_value_that_does_not_match_the_field_type_is_refused(): void
    {
        $vessel = Vessel::factory()->create();
        CustomFieldDefinition::create(['object_type' => $vessel->getMorphClass(), 'code' => 'crew', 'label' => 'Crew', 'type' => CustomFieldType::Number]);

        $this->expectException(ValidationException::class);

        app(CustomFields::class)->set($vessel, 'crew', 'many');
    }

    public function test_hidden_definitions_are_not_returned(): void
    {
        $vessel = Vessel::factory()->create();
        CustomFieldDefinition::create(['object_type' => $vessel->getMorphClass(), 'code' => 'secret', 'label' => 'Secret', 'type' => CustomFieldType::Text, 'is_visible' => false]);

        $this->assertSame([], app(CustomFields::class)->valuesFor($vessel));
    }
}

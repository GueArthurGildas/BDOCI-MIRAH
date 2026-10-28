<?php

namespace Tests\Feature\Kernel;

use App\Enums\CustomFieldType;
use App\Models\Comment;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use App\Models\Document;
use App\Models\ReferenceList;
use App\Models\ReferenceListValue;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenericElementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_frozen_document_cannot_be_modified_or_deleted(): void
    {
        $document = Document::create(['name' => 'report.pdf', 'path' => 'documents/report.pdf', 'is_frozen' => true]);

        $this->expectException(QueryException::class);

        DB::table('documents')->where('id', $document->id)->update(['name' => 'other.pdf']);
    }

    public function test_a_frozen_document_cannot_be_deleted(): void
    {
        $document = Document::create(['name' => 'report.pdf', 'path' => 'documents/report.pdf', 'is_frozen' => true]);

        $this->expectException(QueryException::class);

        DB::table('documents')->where('id', $document->id)->delete();
    }

    public function test_an_unfrozen_document_can_be_modified(): void
    {
        $document = Document::create(['name' => 'draft.pdf', 'path' => 'documents/draft.pdf']);

        $document->update(['name' => 'final.pdf']);

        $this->assertSame('final.pdf', $document->fresh()->name);
    }

    public function test_a_document_links_to_several_objects(): void
    {
        $document = Document::create(['name' => 'a.pdf', 'path' => 'documents/a.pdf']);

        DB::table('document_links')->insert([
            ['document_id' => $document->id, 'object_type' => 'vessel', 'object_id' => 1],
            ['document_id' => $document->id, 'object_type' => 'license', 'object_id' => 1],
        ]);

        $this->assertSame(2, DB::table('document_links')->where('document_id', $document->id)->count());
    }

    public function test_a_comment_belongs_to_an_object_and_an_author(): void
    {
        $user = User::factory()->create();

        $comment = Comment::create(['object_type' => 'vessel', 'object_id' => 7, 'user_id' => $user->id, 'body' => 'Check the beacon']);

        $this->assertTrue($comment->author->is($user));
        $this->assertSame(1, Comment::where('object_type', 'vessel')->where('object_id', 7)->count());
    }

    public function test_reference_list_values_are_ordered_and_unique_per_list(): void
    {
        $list = ReferenceList::create(['code' => 'gear_kinds', 'label' => 'Gear kinds']);
        ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => 'b', 'label' => 'Beta', 'sort_order' => 2]);
        ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => 'a', 'label' => 'Alpha', 'sort_order' => 1]);

        $this->assertSame(['a', 'b'], $list->values->pluck('code')->all());

        $this->expectException(QueryException::class);

        ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => 'a', 'label' => 'Duplicate']);
    }

    public function test_a_custom_field_holds_one_value_per_object(): void
    {
        $definition = CustomFieldDefinition::create(['object_type' => 'vessel', 'code' => 'crew_size', 'label' => 'Crew size', 'type' => CustomFieldType::Number]);
        CustomFieldValue::create(['custom_field_definition_id' => $definition->id, 'object_id' => 3, 'value' => '12']);

        $this->assertSame(CustomFieldType::Number, $definition->fresh()->type);

        $this->expectException(QueryException::class);

        CustomFieldValue::create(['custom_field_definition_id' => $definition->id, 'object_id' => 3, 'value' => '13']);
    }

    public function test_a_custom_field_type_outside_the_catalog_is_refused(): void
    {
        $this->expectException(QueryException::class);

        DB::table('custom_field_definitions')->insert(['object_type' => 'vessel', 'code' => 'x', 'label' => 'X', 'type' => 'unknown']);
    }
}

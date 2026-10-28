<?php

namespace Modules\Administration\Tests\Feature;

use App\Enums\CustomFieldType;
use App\Models\CustomFieldDefinition;
use App\Models\ReferenceList;
use App\Models\ReferenceListValue;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ReferenceDataAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_reach_the_screens(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->get(route('administration.reference-lists.index'))->assertForbidden();
        $this->get(route('administration.custom-fields.index'))->assertForbidden();
        $this->post(route('administration.reference-lists.store'), ['code' => 'gear', 'label' => 'Gear'])->assertForbidden();
    }

    public function test_an_administrator_creates_a_list_and_adds_values(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.reference-lists.store'), ['code' => 'gear_kinds', 'label' => 'Gear kinds'])->assertRedirect();
        $list = ReferenceList::where('code', 'gear_kinds')->firstOrFail();

        $this->post(route('administration.reference-lists.values.store', $list), ['code' => 'trawl', 'label' => 'Trawl', 'sort_order' => 2])->assertRedirect();
        $this->post(route('administration.reference-lists.values.store', $list), ['code' => 'seine', 'label' => 'Seine', 'sort_order' => 1])->assertRedirect();

        $this->get(route('administration.reference-lists.edit', $list))->assertOk()->assertSee('Trawl')->assertSeeInOrder(['Seine', 'Trawl']);
        $this->get(route('administration.reference-lists.index'))->assertOk()->assertSee('Gear kinds');
        $this->assertSame(2, Activity::where('subject_type', ReferenceListValue::class)->where('event', 'created')->count());
    }

    public function test_a_list_code_is_unique_and_final(): void
    {
        $this->signInAs(Role::Admin);
        $list = ReferenceList::create(['code' => 'gear_kinds', 'label' => 'Gear kinds']);

        $this->post(route('administration.reference-lists.store'), ['code' => 'gear_kinds', 'label' => 'Again'])->assertSessionHasErrors('code');
        $this->post(route('administration.reference-lists.store'), ['code' => 'Bad Code', 'label' => 'Bad'])->assertSessionHasErrors('code');
        $this->put(route('administration.reference-lists.update', $list), ['code' => 'other', 'label' => 'Gear'])->assertSessionHasErrors('code');
    }

    public function test_a_value_is_deactivated_instead_of_deleted(): void
    {
        $this->signInAs(Role::Admin);
        $list = ReferenceList::create(['code' => 'gear_kinds', 'label' => 'Gear kinds']);
        $value = ReferenceListValue::create(['reference_list_id' => $list->id, 'code' => 'trawl', 'label' => 'Trawl']);

        $this->patch(route('administration.reference-lists.values.update', [$list, $value]), ['label' => 'Bottom trawl', 'sort_order' => 3, 'is_active' => 0])->assertRedirect();

        $value->refresh();
        $this->assertSame('Bottom trawl', $value->label);
        $this->assertSame(3, $value->sort_order);
        $this->assertFalse($value->is_active);
    }

    public function test_a_value_code_is_unique_within_its_list_only(): void
    {
        $this->signInAs(Role::Admin);
        $first = ReferenceList::create(['code' => 'first', 'label' => 'First']);
        $second = ReferenceList::create(['code' => 'second', 'label' => 'Second']);
        ReferenceListValue::create(['reference_list_id' => $first->id, 'code' => 'x', 'label' => 'X']);

        $this->post(route('administration.reference-lists.values.store', $first), ['code' => 'x', 'label' => 'X again'])->assertSessionHasErrors('code');
        $this->post(route('administration.reference-lists.values.store', $second), ['code' => 'x', 'label' => 'X'])->assertSessionHasNoErrors();
    }

    public function test_an_administrator_defines_a_custom_field_and_can_only_relabel_it(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.custom-fields.store'), [
            'object_type' => Vessel::class, 'code' => 'crew_size', 'label' => 'Crew size', 'type' => 'number', 'is_visible' => 1,
        ])->assertRedirect(route('administration.custom-fields.index'));

        $field = CustomFieldDefinition::where('code', 'crew_size')->firstOrFail();
        $this->assertSame(CustomFieldType::Number, $field->type);
        $this->get(route('administration.custom-fields.index'))->assertOk()->assertSee('Crew size');

        $this->put(route('administration.custom-fields.update', $field), ['label' => 'Crew', 'is_visible' => 0])->assertRedirect();
        $this->put(route('administration.custom-fields.update', $field), ['label' => 'Crew', 'type' => 'text'])->assertSessionHasErrors('type');

        $field->refresh();
        $this->assertSame('Crew', $field->label);
        $this->assertFalse($field->is_visible);
        $this->assertSame(CustomFieldType::Number, $field->type);
    }

    public function test_a_custom_field_needs_a_known_record_type_and_a_unique_code(): void
    {
        $this->signInAs(Role::Admin);
        CustomFieldDefinition::create(['object_type' => Vessel::class, 'code' => 'crew_size', 'label' => 'Crew', 'type' => CustomFieldType::Number]);

        $this->post(route('administration.custom-fields.store'), ['object_type' => 'App\\Models\\Nothing', 'code' => 'x', 'label' => 'X', 'type' => 'text'])->assertSessionHasErrors('object_type');
        $this->post(route('administration.custom-fields.store'), ['object_type' => Vessel::class, 'code' => 'crew_size', 'label' => 'Crew', 'type' => 'text'])->assertSessionHasErrors('code');
        $this->post(route('administration.custom-fields.store'), ['object_type' => Vessel::class, 'code' => 'x', 'label' => 'X', 'type' => 'unknown'])->assertSessionHasErrors('type');
    }

    public function test_the_create_forms_render_in_both_languages(): void
    {
        $this->signInAs(Role::Admin);

        foreach (['en', 'fr'] as $locale) {
            app()->setLocale($locale);
            $this->get(route('administration.reference-lists.create'))->assertOk()->assertSee(__('administration::reference_lists.create_title'));
            $this->get(route('administration.custom-fields.create'))->assertOk()->assertSee(__('administration::custom_fields.create_title'));
        }
    }
}

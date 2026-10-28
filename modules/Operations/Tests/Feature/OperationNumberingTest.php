<?php

namespace Modules\Operations\Tests\Feature;

use App\Enums\OperationCategoryCode;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class OperationNumberingTest extends TestCase
{
    use RefreshDatabase;

    private function open(OperationCategory $category, string $name, array $extra = [])
    {
        return $this->post(route('operations.store'), ['category_id' => $category->id, 'name' => $name, ...$extra]);
    }

    #[Covers('UC11', partial: true)]
    #[Assumes('H-3a-3')]
    public function test_the_code_follows_the_ops_number_category_year_name_format(): void
    {
        $this->signInAs(Role::Agent);
        $category = OperationCategory::factory()->create(['code' => OperationCategoryCode::Vms]);

        $this->open($category, 'Silence balise été')->assertRedirect();

        $operation = Operation::sole();
        $this->assertSame('OPS-001-VMS-'.now()->year.'-Silence_balise_ete', $operation->code);
        $this->assertSame(1, $operation->number);
        $this->assertSame(now()->year, $operation->year);
    }

    #[Covers('UC11', partial: true)]
    public function test_numbers_are_counted_per_category(): void
    {
        $this->signInAs(Role::Agent);
        $vms = OperationCategory::factory()->create(['code' => OperationCategoryCode::Vms]);
        $inn = OperationCategory::factory()->create(['code' => OperationCategoryCode::Inn]);

        $this->open($vms, 'Premier');
        $this->open($vms, 'Deuxième');
        $this->open($inn, 'Autre catégorie');

        $this->assertSame(
            ['OPS-001-VMS-'.now()->year.'-Premier', 'OPS-002-VMS-'.now()->year.'-Deuxieme', 'OPS-001-INN-'.now()->year.'-Autre_categorie'],
            Operation::orderBy('id')->pluck('code')->all(),
        );
    }

    #[Covers('UC11', partial: true)]
    public function test_counters_restart_on_the_first_of_january(): void
    {
        $this->signInAs(Role::Agent);
        $category = OperationCategory::factory()->create(['code' => OperationCategoryCode::Ers]);

        $this->travelTo(now()->setDate(2026, 12, 31)->setTime(10, 0));
        $this->open($category, 'Fin décembre');
        $this->open($category, 'Dernier de 2026');

        $this->travelTo(now()->setDate(2027, 1, 1)->setTime(9, 0));
        $this->open($category, 'Premier de 2027');

        $this->assertSame(
            ['OPS-001-ERS-2026-Fin_decembre', 'OPS-002-ERS-2026-Dernier_de_2026', 'OPS-001-ERS-2027-Premier_de_2027'],
            Operation::orderBy('id')->pluck('code')->all(),
        );
        $this->assertDatabaseHas('operation_counters', ['category_id' => $category->id, 'year' => 2026, 'last_number' => 2]);
        $this->assertDatabaseHas('operation_counters', ['category_id' => $category->id, 'year' => 2027, 'last_number' => 1]);
    }

    #[Covers('UC11', partial: true)]
    public function test_the_name_loses_accents_and_punctuation_and_spaces_become_underscores(): void
    {
        $this->signInAs(Role::Agent);
        $category = OperationCategory::factory()->create(['code' => OperationCategoryCode::Div]);

        $this->open($category, "  Navire d'Abidjan : côte   ouest !  ");

        $this->assertStringEndsWith('-'.now()->year.'-Navire_dAbidjan_cote_ouest', Operation::sole()->code);
    }
}

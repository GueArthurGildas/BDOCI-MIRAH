<?php

namespace Modules\Licenses\Tests\Feature;

use App\Enums\CurrentLicenseStatus;
use App\Enums\LicenseRecordStatus;
use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\LicenseSuspension;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC03', 'RG07', partial: true)]
class SuspensionAndRevocationTest extends TestCase
{
    use RefreshDatabase;

    private function suspend(License $license, array $overrides = [])
    {
        return $this->post(route('licenses.suspensions.store', $license), array_merge([
            'reason' => 'art_105',
            'started_on' => today()->toDateString(),
            'planned_end_on' => today()->addMonths(2)->toDateString(),
        ], $overrides));
    }

    public function test_a_suspension_updates_the_license_the_journal_the_state_and_the_audit_log(): void
    {
        $actor = $this->signInAs(Role::Director);
        $license = License::factory()->expiringIn(200)->create();

        $this->suspend($license)->assertRedirect(route('licenses.show', $license));

        $suspension = LicenseSuspension::sole();
        $this->assertSame($actor->id, $suspension->decided_by);
        $this->assertSame(LicenseRecordStatus::Suspended, $license->fresh()->status);
        $this->assertSame(LicenseStatus::Suspended, $license->fresh()->standing);
        $this->assertSame(CurrentLicenseStatus::Suspended, $license->vessel->fresh()->currentState->license_status);
        $this->assertSame('license.suspended', VesselEvent::sole()->type);
        $this->assertSame($actor->id, Activity::where('event', 'licenses.suspend')->sole()->causer_id);
    }

    public function test_a_suspension_cannot_exceed_three_months_unless_force_majeure(): void
    {
        $this->signInAs(Role::Director);
        $license = License::factory()->create();

        $this->suspend($license, ['planned_end_on' => today()->addMonths(3)->addDay()->toDateString()])->assertSessionHasErrors('planned_end_on');
        $this->assertSame(0, LicenseSuspension::count());
        $this->assertSame(LicenseRecordStatus::Active, $license->fresh()->status);

        $this->suspend($license, ['planned_end_on' => today()->addMonths(3)->toDateString()])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, LicenseSuspension::count());

        $other = License::factory()->create();
        $this->suspend($other, ['reason' => 'force_majeure', 'planned_end_on' => today()->addMonths(8)->toDateString()])->assertSessionDoesntHaveErrors();
        $this->assertSame(2, LicenseSuspension::count());
    }

    #[Assumes('H-4a-2')]
    public function test_a_planned_end_is_required_except_for_force_majeure(): void
    {
        $this->signInAs(Role::Director);
        $license = License::factory()->create();

        $this->suspend($license, ['planned_end_on' => null])->assertSessionHasErrors('planned_end_on');
        $this->suspend($license, ['reason' => 'force_majeure', 'planned_end_on' => null])->assertSessionDoesntHaveErrors();
        $this->assertSame(1, LicenseSuspension::count());
    }

    public function test_lifting_the_only_suspension_reactivates_the_license(): void
    {
        $this->signInAs(Role::Director);
        $license = License::factory()->expiringIn(200)->create();
        $this->suspend($license);
        $suspension = LicenseSuspension::sole();

        $this->post(route('licenses.suspensions.lift', $suspension))->assertRedirect(route('licenses.show', $license));

        $this->assertNotNull($suspension->fresh()->lifted_on);
        $this->assertSame(LicenseRecordStatus::Active, $license->fresh()->status);
        $this->assertSame(CurrentLicenseStatus::Active, $license->vessel->fresh()->currentState->license_status);
        $this->post(route('licenses.suspensions.lift', $suspension))->assertSessionHasErrors('suspension');
    }

    public function test_a_license_stays_suspended_while_another_suspension_is_in_force(): void
    {
        $this->signInAs(Role::Director);
        $license = License::factory()->create();
        $this->suspend($license);
        $this->suspend($license, ['reason' => 'stocks']);

        $this->post(route('licenses.suspensions.lift', LicenseSuspension::orderBy('id')->first()));

        $this->assertSame(LicenseRecordStatus::Suspended, $license->fresh()->status);
    }

    #[Assumes('H-4a-2')]
    public function test_a_suspension_past_its_planned_end_stays_in_force_and_is_flagged_overdue(): void
    {
        $this->signInAs(Role::Director);
        $license = License::factory()->suspended()->create();
        $suspension = LicenseSuspension::factory()->create(['license_id' => $license->id, 'started_on' => today()->subMonths(4), 'planned_end_on' => today()->subMonth()]);

        $this->assertTrue($suspension->isOverdue());
        $this->assertSame(LicenseStatus::Suspended, $license->fresh()->standing);
        $this->get(route('licenses.show', $license))->assertSee(__('licenses::licenses.suspension.overdue'));
    }

    public function test_a_license_is_revoked_once_and_can_no_longer_be_suspended(): void
    {
        $actor = $this->signInAs(Role::Director);
        $license = License::factory()->create();

        $this->post(route('licenses.revocation.store', $license), ['reason' => 'Illegal fishing', 'revoked_on' => today()->toDateString(), 'is_repeat_offense' => 1])
            ->assertRedirect(route('licenses.show', $license));

        $revocation = $license->fresh()->revocation;
        $this->assertSame($actor->id, $revocation->decided_by);
        $this->assertTrue($revocation->is_repeat_offense);
        $this->assertSame(LicenseStatus::Revoked, $license->fresh()->standing);
        $this->assertSame(CurrentLicenseStatus::Revoked, $license->vessel->fresh()->currentState->license_status);
        $this->assertSame('license.revoked', VesselEvent::sole()->type);

        $this->post(route('licenses.revocation.store', $license), ['reason' => 'Again', 'revoked_on' => today()->toDateString()])->assertSessionHasErrors('license');
        $this->suspend($license)->assertSessionHasErrors('license');
        $this->assertSame(1, $license->revocation()->count());
    }

    public function test_agents_cannot_suspend_or_revoke(): void
    {
        $this->signInAs(Role::Agent);
        $license = License::factory()->create();

        $this->suspend($license)->assertForbidden();
        $this->post(route('licenses.revocation.store', $license), ['reason' => 'x', 'revoked_on' => today()->toDateString()])->assertForbidden();
        $this->assertSame(0, LicenseSuspension::count());
        $this->assertSame(0, VesselEvent::count());
    }
}

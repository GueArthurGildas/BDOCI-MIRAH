<?php

namespace Tests\Feature\Kernel;

use App\Enums\CurrentLicenseStatus;
use App\Models\License;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActionRunnerTest extends TestCase
{
    use RefreshDatabase;

    private function action(bool $fails = false): Action
    {
        return new class($fails) extends Action
        {
            public function __construct(private readonly bool $fails) {}

            public function code(): string
            {
                return 'tests.create_license';
            }

            public function permission(): Permission
            {
                return Permission::LicensesManage;
            }

            public function handle(ActionContext $context, array $input): License
            {
                $license = License::factory()->create(['vessel_id' => $input['vessel_id'], 'number' => 'LIC-ACTION']);
                $context->publish($license->vessel_id, 'licenses', 'license.recorded', $license, 'licenses::events.recorded', ['number' => $license->number, 'expires_on' => $license->expires_on->toDateString()]);

                if ($this->fails) {
                    throw new RuntimeException('failure after the write');
                }

                return $license;
            }
        };
    }

    public function test_an_action_writes_publishes_the_event_refreshes_the_state_and_is_audited(): void
    {
        $actor = $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $license = app(ActionRunner::class)->run($this->action(), $actor, ['vessel_id' => $vessel->id]);

        $event = VesselEvent::sole();
        $this->assertSame($vessel->id, $event->vessel_id);
        $this->assertSame('license.recorded', $event->type);
        $this->assertSame('licenses', $event->module->code);
        $this->assertSame($license->getMorphClass(), $event->object_type);
        $this->assertSame($license->id, $event->object_id);
        $this->assertSame($actor->id, $event->user_id);
        $this->assertStringContainsString('LIC-ACTION', $event->summary());

        $this->assertSame(CurrentLicenseStatus::Active, $vessel->currentState->license_status);

        $audit = Activity::where('event', 'tests.create_license')->sole();
        $this->assertSame($actor->id, $audit->causer_id);
        $this->assertSame($license->id, $audit->subject_id);
    }

    public function test_an_action_is_refused_without_the_permission_and_writes_nothing(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->expectException(AuthorizationException::class);

        try {
            app(ActionRunner::class)->run($this->action(), $agent, ['vessel_id' => $vessel->id]);
        } finally {
            $this->assertSame(0, License::count());
            $this->assertSame(0, VesselEvent::count());
        }
    }

    public function test_a_failure_rolls_back_the_write_the_event_and_the_state(): void
    {
        $actor = $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        try {
            app(ActionRunner::class)->run($this->action(fails: true), $actor, ['vessel_id' => $vessel->id]);
            $this->fail('The failure should propagate.');
        } catch (RuntimeException) {
            $this->assertSame(0, License::count());
            $this->assertSame(0, VesselEvent::count());
            $this->assertNull($vessel->fresh()->currentState);
            $this->assertSame(0, Activity::where('event', 'tests.create_license')->count());
        }
    }

    public function test_the_requester_is_kept_when_an_action_is_run_on_behalf_of_someone(): void
    {
        $actor = $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        app(ActionRunner::class)->run($this->action(), $actor, ['vessel_id' => $vessel->id], requestedBy: $actor);

        $this->assertSame($actor->id, Activity::where('event', 'tests.create_license')->sole()->properties['requested_by']);
    }
}

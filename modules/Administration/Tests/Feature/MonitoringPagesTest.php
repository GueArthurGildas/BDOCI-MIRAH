<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\DataSource;
use App\Models\DataSourceRejection;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_connectors_page_shows_each_data_source(): void
    {
        $this->signInAs(Role::Supervisor);
        $source = DataSource::factory()->create(['name' => 'e-inspection']);
        DataSourceRejection::factory()->count(3)->for($source)->create();

        $this->get('/administration/connectors')
            ->assertOk()
            ->assertSee('e-inspection')
            ->assertViewHas('dataSources', fn ($sources) => $sources->first()->pending_rejections_count === 3);
    }

    public function test_sign_ins_appear_in_the_audit_log(): void
    {
        $user = $this->signInAs(Role::Supervisor);
        auth()->logout();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->get('/administration/audit?event=login')
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee(__('administration::administration.audit.events.login'));
    }

    public function test_inspectors_cannot_read_the_audit_log(): void
    {
        $this->signInAs(Role::Inspector);

        $this->get('/administration/audit')->assertForbidden();
    }
}

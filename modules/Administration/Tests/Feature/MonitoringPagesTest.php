<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\DataSource;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_connectors_page_shows_each_data_source(): void
    {
        $this->signInAs(Role::TeamLead);
        DataSource::factory()->create(['name' => 'e-inspection', 'rejected_count' => 3]);

        $this->get('/administration/connectors')->assertOk()->assertSee('e-inspection');
    }

    public function test_sign_ins_appear_in_the_audit_log(): void
    {
        $user = $this->signInAs(Role::TeamLead);
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

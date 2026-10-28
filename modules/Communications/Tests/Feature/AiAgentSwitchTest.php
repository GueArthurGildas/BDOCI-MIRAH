<?php

namespace Modules\Communications\Tests\Feature;

use App\Models\ActionCatalogEntry;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Communications\Support\AiAgentAccount;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC27', partial: true)]
class AiAgentSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_administrator_switches_the_ai_agent_off_and_on(): void
    {
        $admin = $this->signInAs(Role::Admin);
        $account = app(AiAgentAccount::class);
        $account->provision();
        $this->assertTrue($account->isEnabledFor(Permission::CommunicationsCreateNote));

        $this->get(route('communications.ai-agent.edit'))->assertOk()->assertSee(route('communications.ai-agent.update'));

        $this->put(route('communications.ai-agent.update'), ['enabled' => 0])->assertRedirect(route('communications.ai-agent.edit'));
        $this->assertFalse($account->isEnabledFor(Permission::CommunicationsCreateNote));
        $this->assertSame($admin->id, Activity::where('event', 'communications.set_ai_agent_enabled')->latest('id')->firstOrFail()->causer_id);

        $this->put(route('communications.ai-agent.update'), ['enabled' => 1]);
        $this->assertTrue($account->isEnabledFor(Permission::CommunicationsCreateNote));
        $this->assertTrue(ActionCatalogEntry::where('code', Permission::CommunicationsCreateNote->value)->sole()->ai_agent_enabled);
    }

    public function test_only_users_who_manage_settings_can_switch_the_ai_agent(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('communications.ai-agent.edit'))->assertForbidden();
        $this->put(route('communications.ai-agent.update'), ['enabled' => 0])->assertForbidden();
    }

    public function test_the_value_is_required(): void
    {
        $this->signInAs(Role::Admin);

        $this->put(route('communications.ai-agent.update'), [])->assertSessionHasErrors('enabled');
    }
}

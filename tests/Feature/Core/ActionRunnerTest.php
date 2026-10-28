<?php

namespace Tests\Feature\Core;

use App\Enums\UserKind;
use App\Models\User;
use App\Support\Actions\ActionContext;
use App\Support\Actions\ActionRegistry;
use App\Support\Actions\ActionRunner;
use App\Support\Actions\NamedAction;
use App\Support\Authorization\Permission;
use App\Support\Authorization\PermissionGroup;
use App\Support\Authorization\Role;
use App\Support\Settings\Settings;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActionRunnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(ActionRegistry::class)->register(TestOpenAction::class);
        app(ActionRegistry::class)->register(TestHumanOnlyAction::class);
    }

    private function agent(): User
    {
        return User::factory()->create(['kind' => UserKind::AiAgent]);
    }

    public function test_human_with_permission_can_run(): void
    {
        $user = $this->signInAs(Role::TeamLead);

        $this->assertSame('done', app(ActionRunner::class)->run('test_open', ActionContext::human($user)));
        $this->assertTrue(Activity::where('event', 'test_open')->exists());
    }

    public function test_human_without_permission_is_refused(): void
    {
        $user = $this->signInAs(Role::Inspector);

        $this->expectException(AuthorizationException::class);
        app(ActionRunner::class)->run('test_open', ActionContext::human($user));
    }

    public function test_ai_needs_setting_group_and_permission(): void
    {
        $operator = $this->signInAs(Role::TeamLead);
        $context = ActionContext::aiOnBehalfOf($this->agent(), $operator);

        // 1. not enabled
        try {
            app(ActionRunner::class)->run('test_open', $context);
            $this->fail('Should be refused: action not enabled.');
        } catch (AuthorizationException) {
        }

        app(Settings::class)->setAiActionEnabled('test_open', true);

        // 2. operator lacks the ai.trigger group
        try {
            app(ActionRunner::class)->run('test_open', $context);
            $this->fail('Should be refused: operator lacks the AI group.');
        } catch (AuthorizationException) {
        }

        // 3. all conditions met
        $operator->assignRole(PermissionGroup::AiTrigger->roleName());
        $operator->forgetCachedPermissions();

        $this->assertSame('done', app(ActionRunner::class)->run('test_open', $context));

        $log = Activity::where('event', 'test_open')->latest('id')->first();
        $this->assertTrue($log->properties['executed_by_ai']);
        $this->assertSame($operator->id, $log->properties['requested_by_id']);
    }

    public function test_ai_cannot_run_a_human_only_action(): void
    {
        $operator = $this->signInAs(Role::Director);
        $operator->assignRole(PermissionGroup::AiTrigger->roleName());
        app(Settings::class)->setAiActionEnabled('test_human_only', true);

        $this->expectException(AuthorizationException::class);
        app(ActionRunner::class)->run('test_human_only', ActionContext::aiOnBehalfOf($this->agent(), $operator));
    }

    public function test_ai_without_operator_is_refused(): void
    {
        $this->signInAs(Role::Director);

        $this->expectException(AuthorizationException::class);
        app(ActionRunner::class)->run('test_open', new ActionContext($this->agent()));
    }
}

class TestOpenAction implements NamedAction
{
    public static function code(): string { return 'test_open'; }
    public static function permission(): Permission { return Permission::VesselsManage; }
    public static function aiExecutable(): bool { return true; }
    public function handle(ActionContext $context, array $input): mixed { return 'done'; }
}

class TestHumanOnlyAction extends TestOpenAction
{
    public static function code(): string { return 'test_human_only'; }
    public static function aiExecutable(): bool { return false; }
}

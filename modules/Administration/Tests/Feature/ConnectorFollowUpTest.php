<?php

namespace Modules\Administration\Tests\Feature;

use App\Enums\ConnectorCommentKind;
use App\Enums\RejectionStatus;
use App\Models\Comment;
use App\Models\DataSource;
use App\Models\DataSourceError;
use App\Models\DataSourceRejection;
use App\Models\DataSourceSync;
use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC23', partial: true)]
class ConnectorFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_page_lists_errors_rejected_records_syncs_and_comments(): void
    {
        $this->signInAs(Role::TeamLead);
        $source = DataSource::factory()->create();
        $error = DataSourceError::factory()->for($source)->create(['message' => 'Remote host refused the connection']);
        $rejection = DataSourceRejection::factory()->for($source)->create(['external_ref' => 'REC-4242', 'details' => 'Field "latitude" is required']);
        DataSourceSync::factory()->for($source)->create(['message' => 'Partial import']);
        Comment::factory()->forDataSource($source)->create(['body' => 'Supplier contacted this morning']);

        $this->get(route('administration.connectors.show', $source))
            ->assertOk()
            ->assertSee($error->message)
            ->assertSee('REC-4242')
            ->assertSee('Field &quot;latitude&quot; is required', escape: false)
            ->assertSee('Partial import')
            ->assertSee('Supplier contacted this morning');
    }

    public function test_the_most_relevant_tab_is_opened_by_default(): void
    {
        $this->signInAs(Role::TeamLead);
        $withErrors = DataSource::factory()->create();
        DataSourceError::factory()->for($withErrors)->create();
        $quiet = DataSource::factory()->create();

        $this->get(route('administration.connectors.show', $withErrors))->assertViewHas('tab', 'errors');
        $this->get(route('administration.connectors.show', $quiet))->assertViewHas('tab', 'comments');
        $this->get(route('administration.connectors.show', [$quiet, 'tab' => 'syncs']))->assertViewHas('tab', 'syncs');
    }

    public function test_agents_cannot_open_connectors(): void
    {
        $this->signInAs(Role::Agent);
        $source = DataSource::factory()->create();

        $this->get(route('administration.connectors.show', $source))->assertForbidden();
    }

    public function test_viewers_without_the_manage_permission_cannot_act(): void
    {
        $this->signInWithPermissions(Permission::ConnectorsView);
        $source = DataSource::factory()->create();
        $error = DataSourceError::factory()->for($source)->create();

        $this->get(route('administration.connectors.show', $source))->assertOk()->assertDontSee(route('administration.connectors.errors.resolve', $error));
        $this->post(route('administration.connectors.errors.resolve', $error))->assertForbidden();
        $this->post(route('administration.connectors.comments.store', $source), ['kind' => 'note', 'body' => 'x'])->assertForbidden();
    }

    public function test_a_comment_is_published_and_notifies_the_other_managers_only(): void
    {
        $author = $this->signInAs(Role::Admin);
        $colleague = User::factory()->create()->assignRole(Role::Admin->value);
        $agent = User::factory()->create()->assignRole(Role::Agent->value);
        $source = DataSource::factory()->create(['name' => 'THEMIS feed']);
        $this->actingAs($author);

        $this->post(route('administration.connectors.comments.store', $source), [
            'kind' => ConnectorCommentKind::Action->value,
            'body' => 'Restart scheduled tonight',
            'notify' => 1,
        ])->assertRedirect(route('administration.connectors.show', [$source, 'tab' => 'comments']));

        $comment = Comment::sole();
        $this->assertSame($author->id, $comment->user_id);
        $this->assertSame(ConnectorCommentKind::Action->value, $comment->kind);
        $this->assertSame(1, $colleague->notifications()->count());
        $this->assertSame('THEMIS feed', $colleague->notifications()->first()->data['params']['connector']);
        $this->assertSame(0, $author->notifications()->count());
        $this->assertSame(0, $agent->notifications()->count());
    }

    public function test_notification_is_optional(): void
    {
        $author = $this->signInAs(Role::Admin);
        $colleague = User::factory()->create()->assignRole(Role::Admin->value);
        $this->actingAs($author);
        $source = DataSource::factory()->create();

        $this->post(route('administration.connectors.comments.store', $source), ['kind' => 'note', 'body' => 'For later', 'notify' => 0]);

        $this->assertSame(1, Comment::count());
        $this->assertSame(0, $colleague->notifications()->count());
    }

    public function test_comments_are_validated(): void
    {
        $this->signInAs(Role::Admin);
        $source = DataSource::factory()->create();

        $this->post(route('administration.connectors.comments.store', $source), ['kind' => 'nonsense', 'body' => ''])
            ->assertSessionHasErrors(['kind', 'body']);

        $this->assertSame(0, Comment::count());
    }

    public function test_errors_can_be_resolved_and_reopened(): void
    {
        $manager = $this->signInAs(Role::Admin);
        $error = DataSourceError::factory()->create();

        $this->post(route('administration.connectors.errors.resolve', $error))->assertRedirect();
        $this->assertTrue($error->fresh()->isResolved());
        $this->assertSame($manager->id, $error->fresh()->resolved_by);

        $this->post(route('administration.connectors.errors.reopen', $error))->assertRedirect();
        $this->assertFalse($error->fresh()->isResolved());
        $this->assertNull($error->fresh()->resolved_by);
    }

    public function test_rejected_records_can_be_corrected_dismissed_and_reopened(): void
    {
        $manager = $this->signInAs(Role::Admin);
        $rejection = DataSourceRejection::factory()->create();

        $this->patch(route('administration.connectors.rejections.handle', $rejection), ['status' => 'corrected'])->assertRedirect();
        $this->assertSame(RejectionStatus::Corrected, $rejection->fresh()->status);
        $this->assertSame($manager->id, $rejection->fresh()->handled_by);

        $this->patch(route('administration.connectors.rejections.handle', $rejection), ['status' => 'pending'])->assertRedirect();
        $this->assertNull($rejection->fresh()->handled_by);
        $this->assertNull($rejection->fresh()->handled_at);

        $this->patch(route('administration.connectors.rejections.handle', $rejection), ['status' => 'bogus'])->assertSessionHasErrors('status');
    }

    public function test_handling_a_rejection_is_recorded_in_the_audit_log_but_creating_it_is_not(): void
    {
        $this->signInAs(Role::Admin);
        $rejection = DataSourceRejection::factory()->create();
        $rejectionActivities = fn () => Activity::where('subject_type', $rejection->getMorphClass());
        $this->assertSame(0, $rejectionActivities()->count());

        $this->patch(route('administration.connectors.rejections.handle', $rejection), ['status' => 'dismissed']);

        $this->assertSame(1, $rejectionActivities()->where('event', 'updated')->count());
    }

    public function test_issue_counts_only_include_open_errors_and_pending_rejections(): void
    {
        $source = DataSource::factory()->create();
        DataSourceError::factory()->count(2)->for($source)->create();
        DataSourceError::factory()->resolved()->for($source)->create();
        DataSourceRejection::factory()->count(3)->for($source)->create();
        DataSourceRejection::factory()->handled(RejectionStatus::Dismissed)->for($source)->create();

        $counted = DataSource::withIssueCounts()->find($source->id);

        $this->assertSame(2, $counted->open_errors_count);
        $this->assertSame(3, $counted->pending_rejections_count);
    }
}

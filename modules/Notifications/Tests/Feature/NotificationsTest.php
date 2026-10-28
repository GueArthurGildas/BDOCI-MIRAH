<?php

namespace Modules\Notifications\Tests\Feature;

use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Licenses\Notifications\LicensesExpiringAlert;
use Modules\Tracking\Notifications\BeaconsSilentAlert;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->get(route('notifications.feed'))->assertRedirect(route('login'));
    }

    public function test_feed_returns_translated_unread_notifications_of_the_current_user_only(): void
    {
        $user = $this->signInAs(Role::Agent);
        $user->notify(new BeaconsSilentAlert(3));
        $this->signInAs(Role::Admin)->notify(new LicensesExpiringAlert(1));
        $this->actingAs($user);

        $this->getJson(route('notifications.feed'))
            ->assertOk()
            ->assertJsonPath('unread', 1)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.level', 'danger')
            ->assertJsonPath('items.0.title', __('tracking::alerts.beacons_silent.title'))
            ->assertJsonPath('items.0.message', trans_choice('tracking::alerts.beacons_silent.message', 3, ['count' => 3, 'hours' => 4]));
    }

    public function test_opening_a_notification_marks_it_read_and_redirects_to_its_target(): void
    {
        $user = $this->signInAs(Role::Agent);
        $user->notify(new BeaconsSilentAlert(2));
        $notification = $user->notifications()->first();

        $this->get(route('notifications.open', $notification->id))->assertRedirect(route('tracking.beacons'));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->getJson(route('notifications.feed'))->assertJsonPath('unread', 0);
    }

    public function test_a_user_cannot_open_the_notification_of_someone_else(): void
    {
        $owner = $this->signInAs(Role::Admin);
        $owner->notify(new LicensesExpiringAlert(1));
        $notification = $owner->notifications()->first();

        $this->signInAs(Role::Agent);

        $this->get(route('notifications.open', $notification->id))->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $user = $this->signInAs(Role::Admin);
        $user->notify(new BeaconsSilentAlert(2));
        $user->notify(new LicensesExpiringAlert(1));

        $this->post(route('notifications.read-all'))->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_index_lists_notifications_and_the_header_shows_the_beacon(): void
    {
        $user = $this->signInAs(Role::Admin);
        $user->notify(new LicensesExpiringAlert(2));

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee(__('licenses::alerts.expiring.title'))
            ->assertSee('data-notification-beacon', escape: false);
    }
}

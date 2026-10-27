---
name: add-alert
description: Add a notification alert (toast and blinking beacon) raised by a scheduled command, with cooldown, recipients by permission, admin-editable settings and translations. Use when asked for a new alert, reminder or automatic notification to users.
---

# New alert

Kernel: `app/Support/Notifications/{Alert,AlertLevel,Notifier}.php`. Delivery (feed, toasts, notifications page): `modules/Notifications`. Reference: `modules/Tracking` (silent beacons, every 15 min) and `modules/Licenses` (expiring licenses, daily).

## Steps

1. **Alert class** `modules/<Module>/Notifications/<Name>Alert.php` extends `Alert`: `key()` (stable string, used for the cooldown), `level()` (`AlertLevel::Danger|Warning|Info`), `icon()` (feather `icon-*`), `translationKey()` (`'<module>::alerts.<name>'`), `params()` (must contain `count` when the message pluralizes), `target()` (`[routeName, routeParams]` the toast opens).
2. **One summary message** per scan (not one per vessel): the alert carries a count and links to the filtered list.
3. **Command** `modules/<Module>/Console/Notify<Name>.php`: return early when `config('surveillance.alerts.<name>.enabled')` is false; count facts; `$notifier->notifyPermissionHolders(Permission::X, new <Name>Alert($count), config('surveillance.alerts.<name>.cooldown_hours'))`. Only active users holding the permission are notified.
4. **Provider**: `schedule()` registers the command, `consoleCommands()` lists it.
5. **Config + admin settings**: defaults in `config/surveillance.php` (`alerts.<name>.enabled`, `.cooldown_hours`, plus the threshold); declare them in `App\Support\Settings\Settings::definitions()` (see the `add-setting` skill).
6. **Translations** `modules/<Module>/lang/{en,fr}/alerts.php`: `'<name>' => ['title' => ..., 'message' => '{1} :count ... |[2,*] :count ...']`.
7. **Tests** (copy `modules/Tracking/Tests/Feature/SilentBeaconsNotificationTest.php`): holders notified once per cooldown, others (for example the Admin, who holds no business permission) not notified, nothing sent when nothing to report, nothing sent when disabled, a new notification after the cooldown (`$this->travel(n)->hours()`).
8. Restart the scheduler container after changing `.env` or config defaults: `docker compose restart scheduler`.

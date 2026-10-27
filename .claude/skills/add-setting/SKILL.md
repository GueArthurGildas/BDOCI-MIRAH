---
name: add-setting
description: Add an administrator-editable setting (non-sensitive threshold, delay or toggle) that overrides a config value. Use when asked to make a value configurable from the admin panel.
---

# New admin setting

Mechanism: `App\Support\Settings\Settings` layers values stored in the `settings` table over `config('surveillance.*')` at boot, so existing code keeps reading `config(...)`. Never put secrets here.

## Steps

1. **Default** in `config/surveillance.php` (env-backed when sensible).
2. **Definition** in `Settings::definitions()`: `new SettingDefinition('<group.key>', '<config key>', SettingType::Integer|Boolean, '<group>', min, max)`. The form field name is the key with `.` replaced by `_` (PHP rewrites dots in input names).
3. **Translations** in `modules/Administration/lang/{en,fr}/settings.php`: `fields.<field>`; and `groups.<group>.title/description` if the group is new.
4. **Use** it with `config('surveillance....')` in code and views (read at request time, so a saved change applies immediately and in the same process).
5. **Test**: `modules/Administration/Tests/Feature/SettingsTest.php` builds a full payload in `validPayload()`: add the new field there, and add an assertion that the setting overrides the config and that a value equal to the default is not stored.

Values equal to the default are deleted rather than stored; "Restore defaults" clears everything. Changes are audited (the `Setting` model is `Audited`).

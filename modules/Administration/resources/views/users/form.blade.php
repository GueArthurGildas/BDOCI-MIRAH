<x-ui.form.input name="name" :label="__('administration::administration.users.fields.name')" :value="$user->name" required />
<x-ui.form.input name="email" type="email" :label="__('administration::administration.users.fields.email')" :value="$user->email" required />
<x-ui.form.select name="team_id" :label="__('administration::administration.users.fields.team')" :options="$teams" :value="$user->team_id" />
<x-ui.form.input name="phone" :label="__('administration::administration.users.fields.phone')" :value="$user->phone" />
<x-ui.form.input name="whatsapp_number" :label="__('administration::administration.users.fields.whatsapp_number')" :value="$user->whatsapp_number" />
<x-ui.form.select name="role" :label="__('administration::administration.users.fields.role')" :options="App\Support\Authorization\Role::options()" :value="$user->roles->first(fn ($role) => App\Support\Authorization\Role::tryFrom($role->name))?->name" required />
@php($selectedGroups = old('groups', $user->roles->map(fn ($role) => str_starts_with($role->name, App\Support\Authorization\PermissionGroup::ROLE_PREFIX) ? substr($role->name, strlen(App\Support\Authorization\PermissionGroup::ROLE_PREFIX)) : null)->filter()->values()->all()))
<div class="mb-3 row">
    <label class="form-label col-sm-3 col-form-label">{{ __('administration::administration.users.fields.groups') }}</label>
    <div class="col-sm-9">
        @foreach (App\Support\Authorization\PermissionGroup::options() as $value => $label)
            <div class="checkbox-fade fade-in-primary d-block">
                <label class="form-label">
                    <input type="checkbox" name="groups[]" value="{{ $value }}" @checked(in_array($value, $selectedGroups, true))>
                    <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                    <span>{{ $label }}</span>
                </label>
            </div>
        @endforeach
        @error('groups.*')
            <div class="text-danger f-12">{{ $message }}</div>
        @enderror
    </div>
</div>
<x-ui.form.input name="password" type="password" :label="__('administration::administration.users.fields.password')" autocomplete="new-password" :placeholder="$user->exists ? __('administration::administration.users.password_hint') : null" />
<x-ui.form.input name="password_confirmation" type="password" :label="__('administration::administration.users.fields.password_confirmation')" autocomplete="new-password" />
<x-ui.form.checkbox name="is_active" :label="__('administration::administration.users.fields.is_active')" :checked="$user->is_active" />

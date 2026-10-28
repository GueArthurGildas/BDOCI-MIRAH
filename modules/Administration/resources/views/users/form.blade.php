<x-ui.form.input name="name" :label="__('administration::administration.users.fields.name')" :value="$user->name" required />
<x-ui.form.input name="email" type="email" :label="__('administration::administration.users.fields.email')" :value="$user->email" required />
<x-ui.form.input name="job_title" :label="__('administration::administration.users.fields.job_title')" :value="$user->job_title" maxlength="120" />
<x-ui.form.input name="phone" type="tel" :label="__('administration::administration.users.fields.phone')" :value="$user->phone" maxlength="40" />
<x-ui.form.input name="whatsapp_number" type="tel" :label="__('administration::administration.users.fields.whatsapp_number')" :value="$user->whatsapp_number" maxlength="40" />
<x-ui.form.select name="team_id" :label="__('administration::administration.users.fields.team')" :options="$teams" :value="$user->team_id" />
<x-ui.form.select name="role" :label="__('administration::administration.users.fields.role')" :options="App\Support\Authorization\Role::options()" :value="$user->roles->first()?->name" required />
<div class="mb-3 row">
    <div class="col-sm-3 form-label col-form-label">{{ __('administration::administration.users.fields.groups') }}</div>
    <div class="col-sm-9">
        @foreach ($groups as $group)
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="group-{{ $group->id }}" name="groups[]" value="{{ $group->id }}" @checked(in_array($group->id, old('groups', $user->permissionGroups->pluck('id')->all())))>
                <label class="form-check-label" for="group-{{ $group->id }}">{{ $group->displayLabel() }}</label>
            </div>
        @endforeach
    </div>
</div>
<x-ui.form.input name="password" type="password" :label="__('administration::administration.users.fields.password')" autocomplete="new-password" :placeholder="$user->exists ? __('administration::administration.users.password_hint') : null" />
<x-ui.form.input name="password_confirmation" type="password" :label="__('administration::administration.users.fields.password_confirmation')" autocomplete="new-password" />
<x-ui.form.checkbox name="is_active" :label="__('administration::administration.users.fields.is_active')" :checked="$user->is_active" />

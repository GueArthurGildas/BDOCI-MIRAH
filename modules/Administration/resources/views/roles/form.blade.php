@php($isSystem ??= false)
@php($isLocked ??= false)

@if ($isLocked)
    <div class="alert alert-info">{{ __('administration::administration.roles.admin_locked') }}</div>
@endif

@if ($isSystem)
    <x-ui.form.input name="name" :label="__('administration::administration.roles.name')" :value="App\Support\Authorization\Role::labelFor($role->name)" disabled />
    <div class="mb-3 row">
        <div class="col-sm-3 form-label col-form-label">{{ __('administration::administration.roles.description') }}</div>
        <div class="col-sm-9 col-form-label">{{ App\Support\Authorization\Role::descriptionFor($role) }}</div>
    </div>
@else
    <x-ui.form.input name="name" :label="__('administration::administration.roles.name')" :value="$role->name" maxlength="60" required />
    <x-ui.form.textarea name="description" :label="__('administration::administration.roles.description')" :value="$role->description" rows="2" maxlength="500" />
@endif

@foreach ($groups as $module => $permissions)
    <div class="gs-permission-group">
        <h6>{{ __('administration::administration.permission_groups.'.$module) }}</h6>
        <div class="row">
            @foreach ($permissions as $permission)
                <div class="col-md-6 col-xl-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="permission-{{ $permission->value }}" name="permissions[]" value="{{ $permission->value }}"
                               @checked(in_array($permission->value, old('permissions', $granted), true)) @disabled($isLocked)>
                        <label class="form-check-label" for="permission-{{ $permission->value }}">{{ __('administration::administration.permissions.'.$permission->value) }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<div class="mt-4">
    @unless ($isLocked)
        <button type="submit" class="btn btn-primary">
            <i class="feather icon-save"></i> {{ __('ui.save') }}
        </button>
    @endunless
    <a href="{{ route('administration.roles.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
</div>

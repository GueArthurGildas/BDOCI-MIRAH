@if ($group->is_system)
    <x-ui.form.input name="label" :label="__('administration::groups.label')" :value="$group->displayLabel()" disabled />
    <div class="mb-3 row">
        <div class="col-sm-3 form-label col-form-label">{{ __('administration::groups.description') }}</div>
        <div class="col-sm-9 col-form-label">{{ $group->description() }}</div>
    </div>
@else
    <x-ui.form.input name="label" :label="__('administration::groups.label')" :value="$group->label" maxlength="120" required />
@endif

<h6 class="m-t-20">{{ __('administration::groups.permissions') }}</h6>
@foreach ($modules as $module => $permissions)
    <div class="gs-permission-group">
        <h6>{{ __('administration::administration.permission_groups.'.$module) }}</h6>
        <div class="row">
            @foreach ($permissions as $permission)
                <div class="col-md-6 col-xl-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="permission-{{ $permission->value }}" name="permissions[]" value="{{ $permission->value }}"
                               @checked(in_array($permission->value, old('permissions', $granted), true))>
                        <label class="form-check-label" for="permission-{{ $permission->value }}">{{ __('administration::administration.permissions.'.$permission->value) }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

<h6 class="m-t-20">{{ __('administration::groups.members') }}</h6>
<div class="gs-member-list">
    @foreach ($users as $user)
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="member-{{ $user->id }}" name="members[]" value="{{ $user->id }}" @checked(in_array($user->id, old('members', $members)))>
            <label class="form-check-label" for="member-{{ $user->id }}">{{ $user->name }} <span class="text-muted f-12">{{ $user->email }}</span></label>
        </div>
    @endforeach
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="feather icon-save"></i> {{ __('ui.save') }}
    </button>
    <a href="{{ route('administration.groups.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
</div>

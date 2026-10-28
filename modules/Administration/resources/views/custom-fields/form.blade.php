@if ($field->exists)
    <x-ui.form.input name="object_type" :label="__('administration::custom_fields.object')" :value="$objects[$field->object_type] ?? $field->object_type" disabled />
    <x-ui.form.input name="code" :label="__('administration::custom_fields.code')" :value="$field->code" disabled />
    <x-ui.form.input name="type" :label="__('administration::custom_fields.type')" :value="$field->type->label()" disabled />
@else
    <x-ui.form.select name="object_type" :label="__('administration::custom_fields.object')" :options="$objects" required />
    <x-ui.form.input name="code" :label="__('administration::custom_fields.code')" maxlength="60" required />
    <x-ui.form.select name="type" :label="__('administration::custom_fields.type')" :options="$types" required />
    <p class="text-muted f-12">{{ __('administration::custom_fields.code_hint') }}</p>
@endif
<x-ui.form.input name="label" :label="__('administration::custom_fields.label')" :value="$field->label" maxlength="120" required />
<x-ui.form.checkbox name="is_visible" :label="__('administration::custom_fields.visible')" :checked="$field->is_visible" />

<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> {{ __('ui.save') }}</button>
    <a href="{{ route('administration.custom-fields.index') }}" class="btn btn-default m-l-10">{{ __('ui.cancel') }}</a>
</div>

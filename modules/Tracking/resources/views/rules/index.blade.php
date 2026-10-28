<x-layouts.app :title="__('tracking::tracking.rules.title')">
    <x-ui.page-header :title="__('tracking::tracking.rules.title')" :subtitle="__('tracking::tracking.rules.subtitle')" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('tracking::tracking.rules.list_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('tracking::tracking.rules.code') }}</th>
                            <th>{{ __('tracking::tracking.rules.type') }}</th>
                            <th>{{ __('tracking::tracking.rules.threshold_hours') }}</th>
                            <th>{{ __('tracking::tracking.rules.is_active') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($rules as $rule)
                            <tr>
                                    <td>{{ $rule->code }}</td>
                                    <td>{{ __($rule->type->label()) }}<input type="hidden" name="type" value="{{ $rule->type->value }}" form="rule-{{ $rule->id }}"></td>
                                    <td><input type="number" step="0.5" min="0.5" name="threshold_hours" value="{{ $rule->threshold_hours }}" form="rule-{{ $rule->id }}" class="form-control form-control-sm" aria-label="{{ __('tracking::tracking.rules.threshold_hours') }}"></td>
                                    <td>
                                        <input type="hidden" name="is_active" value="0" form="rule-{{ $rule->id }}">
                                        <input type="checkbox" name="is_active" value="1" form="rule-{{ $rule->id }}" @checked($rule->is_active) aria-label="{{ __('tracking::tracking.rules.is_active') }}">
                                    </td>
                                    <td class="text-end"><button type="submit" form="rule-{{ $rule->id }}" class="btn btn-primary btn-mini">{{ __('ui.save') }}</button></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="5" />
                        @endforelse
                    </x-ui.table>
                    @foreach ($rules as $rule)
                        <form method="POST" action="{{ route('tracking.rules.update', $rule) }}" id="rule-{{ $rule->id }}">@csrf @method('PUT')</form>
                    @endforeach
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                <x-ui.card :title="__('tracking::tracking.rules.new')">
                    <form method="POST" action="{{ route('tracking.rules.store') }}">
                        @csrf
                        <x-ui.form.input name="code" :label="__('tracking::tracking.rules.code')" required />
                        <x-ui.form.select name="type" :label="__('tracking::tracking.rules.type')" :options="App\Enums\AlertRuleType::options()" required />
                        <x-ui.form.input name="threshold_hours" type="number" step="0.5" :label="__('tracking::tracking.rules.threshold_hours')" />
                        <x-ui.form.checkbox name="is_active" :label="__('tracking::tracking.rules.is_active')" :checked="true" />
                        <x-ui.form.actions :cancel="route('tracking.rules.index')" />
                    </form>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>

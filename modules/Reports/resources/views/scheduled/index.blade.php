<x-layouts.app :title="__('reports::reports.scheduled.title')">
    <x-ui.page-header :title="__('reports::reports.scheduled.title')" :subtitle="__('reports::reports.scheduled.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('reports::reports.scheduled.list_title')" :subtitle="trans_choice('ui.results_count', $reports->count())" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('reports::reports.scheduled.fields.code') }}</th>
                    <th>{{ __('reports::reports.scheduled.fields.frequency') }}</th>
                    <th>{{ __('reports::reports.scheduled.fields.format') }}</th>
                    <th>{{ __('reports::reports.scheduled.fields.recipient_id') }}</th>
                    <th>{{ __('reports::reports.scheduled.fields.is_active') }}</th>
                    <th>{{ __('reports::reports.scheduled.last_documents') }}</th>
                    <th></th>
                </x-slot:head>

                @forelse ($reports as $report)
                    <tr>
                        <td>{{ $catalog[$report->code] ?? $report->code }}</td>
                        <td>{{ __($report->frequency->label()) }}</td>
                        <td>{{ __($report->format->label()) }}</td>
                        <td>{{ $report->recipient->name }}</td>
                        <td>
                            <span class="label label-{{ $report->is_active ? 'success' : 'default' }}">
                                {{ $report->is_active ? __('reports::reports.scheduled.active') : __('reports::reports.scheduled.inactive') }}
                            </span>
                        </td>
                        <td>
                            @foreach ($documents[$report->id] as $document)
                                <div>
                                    <a href="{{ route('reports.documents.download', $document) }}"><i class="feather icon-download"></i> {{ $document->name }}</a>
                                </div>
                            @endforeach
                        </td>
                        <td class="text-end text-nowrap">
                            @can(App\Support\Authorization\Permission::ReportsGenerate->value)
                                <form method="POST" action="{{ route('reports.generate', $report) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-mini">{{ __('reports::reports.scheduled.generate') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                    @can(App\Support\Authorization\Permission::ReportsManage->value)
                        <tr>
                            <td colspan="7" class="bg-light">
                                <form method="POST" action="{{ route('reports.update', $report) }}" class="row g-2 align-items-center">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-3">
                                        <select name="code" class="form-select form-select-sm" aria-label="{{ __('reports::reports.scheduled.fields.code') }}">
                                            @foreach ($catalog as $code => $label)
                                                <option value="{{ $code }}" @selected($code === $report->code)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="frequency" class="form-select form-select-sm" aria-label="{{ __('reports::reports.scheduled.fields.frequency') }}">
                                            @foreach ($frequencies as $value => $label)
                                                <option value="{{ $value }}" @selected($value === $report->frequency->value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="format" class="form-select form-select-sm" aria-label="{{ __('reports::reports.scheduled.fields.format') }}">
                                            @foreach ($formats as $value => $label)
                                                <option value="{{ $value }}" @selected($value === $report->format->value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="recipient_id" class="form-select form-select-sm" aria-label="{{ __('reports::reports.scheduled.fields.recipient_id') }}">
                                            @foreach ($recipients as $id => $name)
                                                <option value="{{ $id }}" @selected($id === $report->recipient_id)>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-auto">
                                        <input type="hidden" name="is_active" value="0">
                                        <label class="form-label m-0"><input type="checkbox" name="is_active" value="1" @checked($report->is_active)> {{ __('reports::reports.scheduled.fields.is_active') }}</label>
                                    </div>
                                    <div class="col-md-auto ms-auto">
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-save"></i> {{ __('ui.save') }}</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endcan
                @empty
                    <x-ui.empty-row colspan="7" />
                @endforelse
            </x-ui.table>
        </x-ui.card>

        @can(App\Support\Authorization\Permission::ReportsManage->value)
            <form method="POST" action="{{ route('reports.store') }}">
                @csrf

                <x-ui.card :title="__('reports::reports.scheduled.new')">
                    <x-ui.form.select name="code" :label="__('reports::reports.scheduled.fields.code')" :options="$catalog" required />
                    <x-ui.form.select name="frequency" :label="__('reports::reports.scheduled.fields.frequency')" :options="$frequencies" required />
                    <x-ui.form.select name="format" :label="__('reports::reports.scheduled.fields.format')" :options="$formats" required />
                    <x-ui.form.select name="recipient_id" :label="__('reports::reports.scheduled.fields.recipient_id')" :options="$recipients" required />
                    <x-ui.form.actions :cancel="route('reports.index')" />
                </x-ui.card>
            </form>
        @endcan
    </div>
</x-layouts.app>

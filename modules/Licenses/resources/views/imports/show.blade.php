<x-layouts.app :title="__('licenses::imports.detail_title', ['id' => $import->id])">
    <x-ui.page-header :title="__('licenses::imports.detail_title', ['id' => $import->id])" :breadcrumbs="[__('licenses::imports.title') => route('licenses.imports.index')]" />

    <div class="page-body">
        <x-ui.card :title="__('licenses::imports.summary')">
            <x-slot:actions>
                @if ($import->isOpen())
                    @can(App\Support\Authorization\Permission::LicensesValidate->value)
                        <form data-confirm="{{ __('licenses::confirm.validate_import') }}" data-confirm-label="{{ __('licenses::confirm.validate_import_yes') }}" method="POST" action="{{ route('licenses.imports.validate', $import) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm"><i class="feather icon-check-circle"></i> {{ __('licenses::imports.validate_all') }}</button>
                        </form>
                    @endcan
                    @can(App\Support\Authorization\Permission::LicensesImport->value)
                        <form data-confirm="{{ __('licenses::confirm.cancel_import') }}" data-confirm-label="{{ __('licenses::confirm.cancel_import_yes') }}" method="POST" action="{{ route('licenses.imports.cancel', $import) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('licenses::imports.cancel') }}</button>
                        </form>
                    @endcan
                @endif
            </x-slot:actions>
            <x-ui.detail-list>
                <x-ui.detail :label="__('licenses::licenses.fields.status')"><x-ui.status-badge :status="$import->status" /></x-ui.detail>
                <x-ui.detail :label="__('licenses::imports.created_by')">{{ $import->creator?->name }}</x-ui.detail>
                <x-ui.detail :label="__('licenses::imports.source_file')">{{ $import->document?->name }}</x-ui.detail>
                <x-ui.detail :label="__('licenses::imports.validated_at')">{{ $import->validated_at?->isoFormat('L LT') }}</x-ui.detail>
            </x-ui.detail-list>
        </x-ui.card>

        <x-ui.card :title="__('licenses::imports.lines_title')" flush>
            <x-ui.table>
                <x-slot:head>
                    <th>{{ __('licenses::licenses.fields.vessel') }}</th>
                    <th>{{ __('licenses::licenses.fields.number') }}</th>
                    <th>{{ __('licenses::licenses.fields.segment') }}</th>
                    <th>{{ __('licenses::licenses.fields.issued_on') }}</th>
                    <th>{{ __('licenses::licenses.fields.expires_on') }}</th>
                    <th>{{ __('licenses::imports.zones_hint') }}</th>
                    <th>{{ __('licenses::imports.gears_hint') }}</th>
                    <th>{{ __('licenses::imports.species_hint') }}</th>
                    <th>{{ __('licenses::licenses.fields.status') }}</th>
                    <th></th>
                </x-slot:head>
                @forelse ($lines as $line)
                    @php($editable = $import->isOpen() && $line->status !== App\Enums\LicenseImportLineStatus::Valid)
                    <tr>
                        <td class="text-nowrap">
                            @if ($line->vessel)
                                <x-ui.vessel-link :vessel="$line->vessel" />
                            @else
                                <span class="text-muted">{{ $line->data['registration_number'] ?? '' }}</span>
                            @endif
                        </td>
                        @if ($editable)
                            <td colspan="6">
                                <form method="POST" id="line-{{ $line->id }}" action="{{ route('licenses.imports.lines.update', $line) }}" class="row g-1">
                                    @csrf
                                    @method('PUT')
                                    <div class="col"><input name="number" value="{{ $line->data['number'] ?? '' }}" class="form-control form-control-sm" aria-label="{{ __('licenses::licenses.fields.number') }}"></div>
                                    <div class="col"><select name="segment" class="form-select form-select-sm" aria-label="{{ __('licenses::licenses.fields.segment') }}">
                                        <option value=""></option>
                                        @foreach (App\Enums\LicenseSegment::options() as $value => $label)
                                            <option value="{{ $value }}" @selected(($line->data['segment'] ?? '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select></div>
                                    <div class="col"><input type="date" name="issued_on" value="{{ $line->data['issued_on'] ?? '' }}" class="form-control form-control-sm" aria-label="{{ __('licenses::licenses.fields.issued_on') }}"></div>
                                    <div class="col"><input type="date" name="expires_on" value="{{ $line->data['expires_on'] ?? '' }}" class="form-control form-control-sm" aria-label="{{ __('licenses::licenses.fields.expires_on') }}"></div>
                                    <div class="col"><input name="zones" value="{{ implode(', ', $line->data['zones'] ?? []) }}" class="form-control form-control-sm" aria-label="{{ __('licenses::imports.zones_hint') }}"></div>
                                    <div class="col"><input name="gears" value="{{ implode(', ', $line->data['gears'] ?? []) }}" class="form-control form-control-sm" aria-label="{{ __('licenses::imports.gears_hint') }}"></div>
                                    <div class="col"><input name="species" value="{{ implode(', ', $line->data['species'] ?? []) }}" class="form-control form-control-sm" aria-label="{{ __('licenses::imports.species_hint') }}"></div>
                                </form>
                            </td>
                        @else
                            <td>{{ $line->data['number'] ?? '' }}</td>
                            <td>{{ filled($line->data['segment'] ?? null) ? __('enums.license_segment.'.$line->data['segment']) : '' }}</td>
                            <td>{{ $line->data['issued_on'] ?? '' }}</td>
                            <td>{{ $line->data['expires_on'] ?? '' }}</td>
                            <td>{{ implode(', ', $line->data['zones'] ?? []) }}</td>
                            <td>{{ implode(', ', $line->data['gears'] ?? []) }}</td>
                            <td>{{ implode(', ', $line->data['species'] ?? []) }}</td>
                        @endif
                        <td>
                            <x-ui.status-badge :status="$line->status" />
                            @if ($line->rejection_reason)
                                <div class="text-danger f-12">{{ __('licenses::imports.rejections.'.$line->rejection_reason) }}</div>
                            @endif
                            @if ($line->license_id)
                                <a class="f-12" href="{{ route('licenses.show', $line->license_id) }}">{{ __('licenses::imports.open_license') }}</a>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if ($editable)
                                @can(App\Support\Authorization\Permission::LicensesImport->value)
                                    <button type="submit" form="line-{{ $line->id }}" class="btn btn-default btn-mini">{{ __('ui.save') }}</button>
                                @endcan
                                @can(App\Support\Authorization\Permission::LicensesValidate->value)
                                    <button type="submit" form="line-{{ $line->id }}" formaction="{{ route('licenses.imports.lines.validate', $line) }}" class="btn btn-outline-success btn-mini">{{ __('licenses::imports.validate_line') }}</button>
                                @endcan
                            @endif
                        </td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="10" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>

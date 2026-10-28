<x-layouts.app :title="__('licenses::imports.title')">
    <x-ui.page-header :title="__('licenses::imports.title')" :subtitle="__('licenses::imports.subtitle')" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('licenses::imports.list_title')" flush>
                    <x-ui.table :paginator="$imports">
                        <x-slot:head>
                            <th>#</th>
                            <th>{{ __('licenses::imports.created_at') }}</th>
                            <th>{{ __('licenses::imports.created_by') }}</th>
                            <th>{{ __('licenses::imports.lines') }}</th>
                            <th>{{ __('licenses::imports.validated') }}</th>
                            <th>{{ __('licenses::imports.rejected') }}</th>
                            <th>{{ __('licenses::licenses.fields.status') }}</th>
                        </x-slot:head>
                        @forelse ($imports as $import)
                            <tr>
                                <td><a href="{{ route('licenses.imports.show', $import) }}">{{ $import->id }}</a></td>
                                <td>{{ $import->created_at->isoFormat('L LT') }}</td>
                                <td>{{ $import->creator?->name }}</td>
                                <td>{{ $import->lines_count }}</td>
                                <td>{{ $import->valid_count }}</td>
                                <td>{{ $import->rejected_count }}</td>
                                <td><x-ui.status-badge :status="$import->status" /></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="7" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>

            <div class="col-xl-4">
                @can(App\Support\Authorization\Permission::LicensesImport->value)
                    <x-ui.card :title="__('licenses::imports.upload_title')">
                        <p class="text-muted f-12">{{ __('licenses::imports.format_help', ['columns' => implode('; ', $columns)]) }}</p>
                        <form method="POST" action="{{ route('licenses.imports.file') }}" enctype="multipart/form-data">
                            @csrf
                            <x-ui.form.input name="file" type="file" :label="__('licenses::imports.file')" accept=".csv,.txt" required />
                            <x-ui.form.actions :cancel="route('licenses.imports.index')" />
                        </form>
                    </x-ui.card>

                    <x-ui.card :title="__('licenses::imports.batch_title')">
                        <p class="text-muted f-12">{{ __('licenses::imports.batch_help') }}</p>
                        <form method="POST" action="{{ route('licenses.imports.batch') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="vessel_ids" class="form-label">{{ __('licenses::imports.vessels') }}</label>
                                <select id="vessel_ids" name="vessel_ids[]" multiple size="8" @class(['form-select', 'is-invalid' => $errors->has('vessel_ids')])>
                                    @foreach ($withoutLicense as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('vessel_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-list"></i> {{ __('licenses::imports.start_batch') }}</button>
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>

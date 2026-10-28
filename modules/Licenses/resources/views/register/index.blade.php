<x-layouts.app :title="__('licenses::register.title')">
    <x-ui.page-header :title="__('licenses::register.title')" :subtitle="__('licenses::register.subtitle')" :breadcrumbs="[__('licenses::licenses.title') => route('licenses.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-8">
                <x-ui.card :title="__('licenses::register.list_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('licenses::register.year') }}</th>
                            <th>{{ __('licenses::register.generated_at') }}</th>
                            <th></th>
                        </x-slot:head>
                        @forelse ($registers as $register)
                            <tr>
                                <td class="f-w-600">{{ $register->year }}</td>
                                <td>{{ $register->generated_at?->isoFormat('L LT') }}</td>
                                <td class="text-end">
                                    @if ($register->document_id)
                                        <a href="{{ route('licenses.register.download', $register->year) }}" class="btn btn-default btn-mini"><i class="feather icon-download"></i> {{ __('licenses::register.download') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>
            </div>
            <div class="col-xl-4">
                @can(App\Support\Authorization\Permission::LicensesPublish->value)
                    <x-ui.card :title="__('licenses::register.generate_title')">
                        <form method="POST" action="{{ route('licenses.register.generate') }}">
                            @csrf
                            <x-ui.form.input name="year" type="number" :label="__('licenses::register.year')" :value="$currentYear" required />
                            <x-ui.form.actions :cancel="route('licenses.register.index')" />
                        </form>
                    </x-ui.card>
                @endcan
            </div>
        </div>
    </div>
</x-layouts.app>

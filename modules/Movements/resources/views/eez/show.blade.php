<x-layouts.app :title="__('movements::eez.title')">
    <x-ui.page-header :title="$declaration->displayName()" :subtitle="__($declaration->type->label()).' · '.$declaration->received_at->isoFormat('L LT')" :breadcrumbs="[__('movements::eez.title') => route('movements.eez.index')]" />

    <div class="page-body">
        @can(App\Support\Authorization\Permission::MovementsDeclare->value)
            <div class="mb-3 d-flex gap-2">
                @if ($declaration->status !== App\Enums\EezDeclarationStatus::Closed)
                    @if (! in_array($declaration->status, [App\Enums\EezDeclarationStatus::Acknowledged, App\Enums\EezDeclarationStatus::Variance], true))
                        <form method="POST" action="{{ route('movements.eez.acknowledge', $declaration) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm"><i class="feather icon-check"></i> {{ __('movements::eez.acknowledge') }}</button>
                        </form>
                    @endif
                    <form data-confirm="{{ __('movements::confirm.close_declaration') }}" data-confirm-label="{{ __('movements::confirm.close_declaration_yes') }}" method="POST" action="{{ route('movements.eez.close', $declaration) }}">
                        @csrf
                        <button type="submit" class="btn btn-default btn-sm"><i class="feather icon-lock"></i> {{ __('movements::eez.close') }}</button>
                    </form>
                @endif
            </div>
        @endcan

        <div class="row">
            <div class="col-lg-6">
                <x-ui.card :title="__('movements::eez.tabs_details')" flush>
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('movements::eez.fields.vessel')">
                            @if ($declaration->vessel)
                                <x-ui.vessel-link :vessel="$declaration->vessel" />
                            @else
                                {{ __('movements::eez.unidentified') }}
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.type')"><x-ui.status-badge :status="$declaration->type" /></x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.status')"><x-ui.status-badge :status="$declaration->status" /></x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.received_at')">{{ $declaration->received_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.reception_channel')">{{ $declaration->receptionChannel?->label }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.entry_origin')">{{ __($declaration->entry_origin->label()) }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.entered_by')">{{ $declaration->enteredBy?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.crossed_at')">{{ $declaration->crossed_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.position')">
                            @if ($declaration->latitude !== null && $declaration->longitude !== null)
                                {{ __('movements::eez.fields.position_value', ['latitude' => $declaration->latitude, 'longitude' => $declaration->longitude]) }}
                            @endif
                        </x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.route')">{{ $declaration->route }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.speed')">{{ $declaration->speed }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.destination')">{{ $declaration->destination }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.reason')">{{ $declaration->reason ? __($declaration->reason->label()) : null }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                <x-ui.card :title="__('movements::eez.identity_section')" flush>
                    <x-ui.detail-list>
                        <x-ui.detail :label="__('movements::eez.fields.declared_name')">{{ $declaration->declared_name }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.declared_imo')">{{ $declaration->declared_imo }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.declared_mmsi')">{{ $declaration->declared_mmsi }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.declared_ircs')">{{ $declaration->declared_ircs }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.declared_flag')">{{ $declaration->declared_flag }}</x-ui.detail>
                        <x-ui.detail :label="__('movements::eez.fields.declared_owner')">{{ $declaration->declared_owner }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>
            </div>

            <div class="col-lg-6">
                <x-ui.card :title="__('movements::eez.checks_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('movements::eez.fields.check_type') }}</th>
                            <th>{{ __('movements::eez.fields.result') }}</th>
                            <th>{{ __('movements::eez.fields.comment') }}</th>
                        </x-slot:head>
                        @forelse ($declaration->checks as $check)
                            <tr>
                                <td>{{ __($check->check_type->label()) }}</td>
                                <td><x-ui.status-badge :status="$check->result" /></td>
                                <td>{{ $check->comment ? (trans()->has('movements::eez.check_comments.'.$check->comment) ? __('movements::eez.check_comments.'.$check->comment) : $check->comment) : '—' }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="3" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.card :title="__('movements::eez.catches_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('movements::eez.fields.species') }}</th>
                            <th class="text-end">{{ __('movements::eez.fields.weight_kg') }}</th>
                        </x-slot:head>
                        @forelse ($declaration->catches as $catch)
                            <tr>
                                <td>{{ $catch->species->fao_code }} - {{ $catch->species->name }}</td>
                                <td class="text-end">{{ number_format($catch->weight_kg, 2, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2">{{ __('movements::eez.no_catches') }}</x-ui.empty-row>
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                @php($link = $declaration->linkAsExit ?? $declaration->linkAsEntry)
                <x-ui.card :title="__('movements::eez.pairing_title')" flush>
                    @if ($link)
                        @php($other = $declaration->linkAsExit ? $link->entry : $link->exit)
                        <x-ui.detail-list>
                            <x-ui.detail :label="$declaration->linkAsExit ? __('movements::eez.paired_entry') : __('movements::eez.paired_exit')">
                                <a href="{{ route('movements.eez.show', $other) }}">{{ $other->received_at->isoFormat('L LT') }}</a>
                            </x-ui.detail>
                            <x-ui.detail :label="__('movements::eez.fields.variance_kg')">{{ number_format($link->variance_kg, 2, ',', ' ') }}</x-ui.detail>
                            <x-ui.detail :label="__('movements::eez.fields.explanation')">
                                @if ($link->explanation)
                                    <x-ui.status-badge :status="$link->explanation" />
                                @endif
                                @can(App\Support\Authorization\Permission::MovementsDeclare->value)
                                    <form method="POST" action="{{ route('movements.eez.explain', $link) }}" class="d-flex gap-2 mt-2">
                                        @csrf
                                        <select name="explanation" class="form-select form-select-sm" aria-label="{{ __('movements::eez.fields.explanation') }}">
                                            @foreach (App\Enums\EntryExitExplanation::options() as $value => $label)
                                                <option value="{{ $value }}" @selected($link->explanation?->value === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-default btn-sm">{{ __('movements::eez.explain') }}</button>
                                    </form>
                                @endcan
                            </x-ui.detail>
                        </x-ui.detail-list>
                    @else
                        <p class="m-3 text-muted">{{ __('movements::eez.not_paired') }}</p>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>

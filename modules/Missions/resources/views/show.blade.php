<x-layouts.app :title="$mission->reference">
    <x-ui.page-header :title="$mission->reference" :subtitle="__($mission->type->label())" :breadcrumbs="[__('missions::missions.title') => route('missions.index')]" />

    <div class="page-body">
        <div class="row">
            <div class="col-xl-6">
                <x-ui.card :title="__('missions::missions.fields.objective')">
                    <p class="m-b-20 text-pre-line">{{ $mission->objective }}</p>

                    <x-ui.detail-list>
                        <x-ui.detail :label="__('missions::missions.fields.status')"><x-ui.status-badge :status="$mission->status" /></x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.starts_at')">{{ $mission->starts_at->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.ends_at')">{{ $mission->ends_at?->isoFormat('L LT') }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.zone')">{{ $mission->zone?->name }}</x-ui.detail>
                        <x-ui.detail :label="__('missions::missions.fields.results')">{{ $mission->results }}</x-ui.detail>
                    </x-ui.detail-list>
                </x-ui.card>

                <x-ui.card :title="__('missions::missions.agents_title')">
                    <ul class="list-unstyled m-0">
                        @forelse ($mission->agents as $agent)
                            <li class="m-b-10">
                                <i class="feather icon-user text-muted m-r-5"></i>{{ $agent->name }}
                                <span class="text-muted f-12">{{ $agent->team?->name }}</span>
                            </li>
                        @empty
                            <li class="text-muted">{{ __('missions::missions.no_agents') }}</li>
                        @endforelse
                    </ul>
                </x-ui.card>
            </div>

            <div class="col-xl-6">
                <x-ui.card :title="__('missions::missions.vessels_title')" flush>
                    <x-ui.table>
                        <x-slot:head>
                            <th>{{ __('missions::missions.vessels_title') }}</th>
                            <th>{{ __('missions::missions.fields.category') }}</th>
                        </x-slot:head>
                        @forelse ($mission->vessels as $vessel)
                            <tr>
                                <td><x-ui.vessel-link :vessel="$vessel" /></td>
                                <td><x-ui.status-badge :status="$vessel->category" /></td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="2" />
                        @endforelse
                    </x-ui.table>
                </x-ui.card>

                <x-ui.audit-history :subject="$mission" />
            </div>
        </div>
    </div>
</x-layouts.app>

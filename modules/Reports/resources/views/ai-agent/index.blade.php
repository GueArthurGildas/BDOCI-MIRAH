<x-layouts.app :title="__('reports::reports.ai_agent.title')">
    <x-ui.page-header :title="__('reports::reports.ai_agent.title')" :subtitle="__('reports::reports.ai_agent.subtitle')" />

    <div class="page-body">
        <x-ui.card :title="__('reports::reports.ai_agent.list_title')" :subtitle="trans_choice('ui.results_count', $requests->total())" flush>
            <x-ui.table :paginator="$requests">
                <x-slot:head>
                    <th>{{ __('reports::reports.ai_agent.columns.requested_at') }}</th>
                    <th>{{ __('reports::reports.ai_agent.columns.requester') }}</th>
                    <th>{{ __('reports::reports.ai_agent.columns.action') }}</th>
                    <th>{{ __('reports::reports.ai_agent.columns.instruction') }}</th>
                    <th>{{ __('reports::reports.ai_agent.columns.status') }}</th>
                    <th>{{ __('reports::reports.ai_agent.columns.executed_at') }}</th>
                </x-slot:head>

                @forelse ($requests as $request)
                    <tr>
                        <td class="text-nowrap">{{ $request->created_at->isoFormat('L LT') }}</td>
                        <td>{{ $request->requester->name }}</td>
                        <td>{{ __('reports::reports.ai_agent.actions.'.str_replace('.', '_', $request->action->code)) }}</td>
                        <td>{{ $request->instruction }}</td>
                        <td><x-ui.status-badge :status="$request->status" /></td>
                        <td class="text-nowrap">{{ $request->executed_at?->isoFormat('L LT') }}</td>
                    </tr>
                @empty
                    <x-ui.empty-row colspan="6" />
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>

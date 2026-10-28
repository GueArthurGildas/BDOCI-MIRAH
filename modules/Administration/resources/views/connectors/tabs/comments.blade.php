@can(App\Support\Authorization\Permission::ConnectorsManage->value)
    <x-ui.card :title="__('administration::administration.connectors.add_comment')">
        <form method="POST" action="{{ route('administration.connectors.comments.store', $dataSource) }}">
            @csrf
            <x-ui.form.select name="kind" :label="__('administration::administration.connectors.comment_kind')" :options="App\Enums\ConnectorCommentKind::options()" value="note" required />
            <x-ui.form.textarea name="body" :label="__('administration::administration.connectors.comment_body')" required maxlength="2000" />
            <x-ui.form.checkbox name="notify" :label="__('administration::administration.connectors.notify_admins')" :checked="true" />
            <div class="row">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="feather icon-send"></i> {{ __('administration::administration.connectors.publish') }}
                    </button>
                </div>
            </div>
        </form>
    </x-ui.card>
@endcan

<x-ui.card :title="__('administration::administration.connectors.tabs.comments')" :subtitle="__('administration::administration.connectors.limit_hint', ['count' => $limit])" flush>
    @forelse ($comments as $comment)
        <div class="gs-comment">
            <div class="gs-comment-head">
                <span>
                    <strong>{{ $comment->author?->name ?? __('ui.history.system') }}</strong>
                    <x-ui.status-badge :status="App\Enums\ConnectorCommentKind::from($comment->kind)" />
                </span>
                <small class="text-muted">{{ $comment->created_at->isoFormat('L LT') }} · {{ $comment->created_at->diffForHumans() }}</small>
            </div>
            <p class="text-pre-line m-b-0">{{ $comment->body }}</p>
        </div>
    @empty
        <p class="text-muted m-0 p-4">{{ __('administration::administration.connectors.no_comments') }}</p>
    @endforelse
</x-ui.card>

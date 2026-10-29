<?php

namespace Modules\Communications\Http\Controllers;

use App\Enums\CommunicationChannelCode;
use App\Enums\MessageStatus;
use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\SentMessage;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Communications\Actions\AcknowledgeMessage;
use Modules\Communications\Actions\SendMessage;
use Modules\Communications\Actions\SendPreparedMessage;
use Modules\Communications\Http\Requests\SendMessageRequest;

class MessageController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(MessageStatus::class)],
            'channel' => ['nullable', Rule::enum(CommunicationChannelCode::class)],
        ]);

        $messages = SentMessage::query()
            ->with(['vessel', 'channel', 'template'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['channel'] ?? null, fn (Builder $query, string $channel) => $query->whereHas('channel', fn (Builder $query) => $query->where('code', $channel)))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('recipient', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->latest('id')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('communications::messages.index', [
            'messages' => $messages,
            'filters' => $filters,
            'countsByStatus' => collect(MessageStatus::cases())->mapWithKeys(fn (MessageStatus $status) => [
                $status->value => SentMessage::where('status', $status->value)->count(),
            ]),
        ]);
    }

    public function create(Request $request): View
    {
        return view('communications::messages.create', [
            'vessels' => Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])
                ->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"])
                ->all(),
            'templates' => MessageTemplate::where('is_active', true)->orderBy('code')->get()
                ->mapWithKeys(fn (MessageTemplate $template) => [$template->id => "{$template->code} ({$template->channel->value})"])
                ->all(),
            'selectedVessel' => $request->integer('vessel') ?: null,
        ]);
    }

    public function store(SendMessageRequest $request, ActionRunner $actions): RedirectResponse
    {
        $message = $actions->run(SendMessage::class, $request->user(), $request->validated());

        return to_route('communications.messages.show', $message)->with('status', __("communications::messages.result.{$message->status->value}"))->with('popup', $this->recap($message));
    }

    public function show(SentMessage $message): View
    {
        return view('communications::messages.show', ['message' => $message->load(['vessel', 'channel', 'template', 'rule', 'sender'])]);
    }

    public function send(SentMessage $message, Request $request, ActionRunner $actions): RedirectResponse
    {
        $sent = $actions->run(SendPreparedMessage::class, $request->user(), ['message_id' => $message->id]);

        return to_route('communications.messages.show', $message)->with('status', __("communications::messages.result.{$sent->status->value}"))->with('popup', $this->recap($sent));
    }

    public function acknowledge(SentMessage $message, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(AcknowledgeMessage::class, $request->user(), [
            'message_id' => $message->id,
            'reply_received' => $request->boolean('reply_received'),
        ]);

        return to_route('communications.messages.show', $message)->with('status', __('communications::messages.acknowledged'));
    }

    /** @return array<int, array{label: string, value: string}> */
    private function recap(SentMessage $message): array
    {
        $message->loadMissing('channel');

        return [
            ['label' => __('communications::fields.channel'), 'value' => __($message->channel->code->label())],
            ['label' => __('communications::fields.recipient'), 'value' => (string) $message->recipient],
        ];
    }
}

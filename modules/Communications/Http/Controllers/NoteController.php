<?php

namespace Modules\Communications\Http\Controllers;

use App\Enums\AiAgentActionStatus;
use App\Enums\NoteType;
use App\Enums\NoteUrgency;
use App\Http\Controllers\Controller;
use App\Models\InternalNote;
use App\Models\Team;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Communications\Actions\CreateNote;
use Modules\Communications\Actions\MarkNoteRead;
use Modules\Communications\Actions\RequestAiNote;
use Modules\Communications\Http\Requests\RequestAiNoteRequest;
use Modules\Communications\Http\Requests\StoreNoteRequest;

class NoteController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'box' => ['nullable', Rule::in(['inbox', 'sent'])],
            'unread' => ['nullable', 'boolean'],
        ]);
        $box = $filters['box'] ?? 'inbox';
        $user = $request->user();

        $notes = InternalNote::query()
            ->with(['vessel', 'author', 'recipient', 'recipientTeam'])
            ->when($box === 'inbox', fn ($query) => $query->addressedTo($user), fn ($query) => $query->where('author_id', $user->id))
            ->when($filters['unread'] ?? false, fn ($query) => $query->where('is_read', false))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('communications::notes.index', [
            'notes' => $notes,
            'box' => $box,
            'filters' => $filters,
            'unreadCount' => InternalNote::addressedTo($user)->where('is_read', false)->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('communications::notes.create', [
            'vessels' => Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])
                ->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"])
                ->all(),
            'recipients' => [
                __('communications::notes.recipient_users') => User::where('is_active', true)->whereKeyNot($request->user()->id)->where('type', 'human')->orderBy('name')->get()
                    ->mapWithKeys(fn (User $user) => ["user:{$user->id}" => $user->name])->all(),
                __('communications::notes.recipient_teams') => Team::orderBy('name')->get()
                    ->mapWithKeys(fn (Team $team) => ["team:{$team->id}" => $team->name])->all(),
            ],
            'types' => NoteType::options(),
            'urgencies' => NoteUrgency::options(),
            'selectedVessel' => $request->integer('vessel') ?: null,
        ]);
    }

    public function store(StoreNoteRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CreateNote::class, $request->user(), $request->payload());

        return to_route('communications.notes.index', ['box' => 'sent'])->with('status', __('communications::notes.created'));
    }

    public function read(InternalNote $note, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(MarkNoteRead::class, $request->user(), ['note_id' => $note->id]);

        return back()->with('status', __('communications::notes.marked_read'));
    }

    public function requestAi(RequestAiNoteRequest $request, ActionRunner $actions): RedirectResponse
    {
        $result = $actions->run(RequestAiNote::class, $request->user(), $request->payload());

        $message = $result->status === AiAgentActionStatus::Executed
            ? __('communications::notes.ai_executed')
            : __('communications::notes.ai_refused');

        return to_route('communications.notes.index', ['box' => 'sent'])->with($result->status === AiAgentActionStatus::Executed ? 'status' : 'error', $message);
    }
}

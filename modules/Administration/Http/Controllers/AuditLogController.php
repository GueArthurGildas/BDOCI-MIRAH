<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Tables\PerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    private const PER_PAGE = 30;

    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'user' => ['nullable', 'exists:users,id'],
            'event' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['user'] ?? null, fn (Builder $query, string $user) => $query->where('causer_type', (new User)->getMorphClass())->where('causer_id', $user))
            ->when($filters['event'] ?? null, fn (Builder $query, string $event) => $query->where('event', $event))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(PerPage::from($request, self::PER_PAGE))
            ->withQueryString();

        return view('administration::audit', [
            'activities' => $activities,
            'filters' => $filters,
            'users' => User::orderBy('name')->pluck('name', 'id'),
            'events' => collect(trans('administration::administration.audit.events')),
        ]);
    }
}

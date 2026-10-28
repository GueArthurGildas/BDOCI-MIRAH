<?php

namespace Modules\Licenses\Http\Controllers;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Licenses\Actions\RecordLicense;
use Modules\Licenses\Http\Requests\StoreLicenseRequest;

class LicenseController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(LicenseStatus::class)],
            'type' => ['nullable', Rule::enum(LicenseType::class)],
        ]);

        $licenses = License::query()
            ->with('vessel')
            ->withStatus(LicenseStatus::tryFrom($filters['status'] ?? ''))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('number', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->orderByDesc('valid_until')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('licenses::index', [
            'licenses' => $licenses,
            'filters' => $filters,
            'countsByStatus' => collect(LicenseStatus::cases())->mapWithKeys(fn (LicenseStatus $status) => [
                $status->value => License::withStatus($status)->count(),
            ]),
        ]);
    }

    public function create(): View
    {
        return view('licenses::create', [
            'vessels' => Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])
                ->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"])
                ->all(),
        ]);
    }

    public function store(StoreLicenseRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordLicense::class, $request->user(), $request->validated());

        return to_route('licenses.index')->with('status', __('licenses::licenses.recorded'));
    }
}

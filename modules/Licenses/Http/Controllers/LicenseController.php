<?php

namespace Modules\Licenses\Http\Controllers;

use App\Enums\LicenseSegment;
use App\Enums\LicenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Gear;
use App\Models\License;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Actions\ActionRunner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Licenses\Actions\RecordLicense;
use Modules\Licenses\Actions\UpdateLicense;
use Modules\Licenses\Http\Requests\LicenseRequest;
use Modules\Licenses\Support\ExpiryTiers;

class LicenseController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(LicenseStatus::class)],
            'segment' => ['nullable', Rule::enum(LicenseSegment::class)],
            'expiring_after' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'expiring_within' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $licenses = License::query()
            ->with('vessel')
            ->withStatus(LicenseStatus::tryFrom($filters['status'] ?? ''))
            ->when($filters['segment'] ?? null, fn (Builder $query, string $segment) => $query->where('segment', $segment))
            ->when($filters['expiring_within'] ?? null, fn (Builder $query, int $days) => $query->expiringBetween((int) ($filters['expiring_after'] ?? 0), $days))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->whereLike('number', "%{$search}%")
                ->orWhereHas('vessel', fn (Builder $query) => $query->search($search))))
            ->orderByDesc('expires_on')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('licenses::index', [
            'licenses' => $licenses,
            'filters' => $filters,
            'tiers' => ExpiryTiers::counts(),
            'countsByStatus' => collect(LicenseStatus::cases())->mapWithKeys(fn (LicenseStatus $status) => [
                $status->value => License::withStatus($status)->count(),
            ]),
        ]);
    }

    public function show(License $license): View
    {
        $license->load(['vessel', 'zones', 'gears', 'species', 'conditions', 'documents.document', 'suspensions.decider', 'revocation.decider', 'enteredBy']);

        return view('licenses::show', [
            'license' => $license,
            'vessels' => $this->vesselOptions(),
        ]);
    }

    public function create(): View
    {
        return view('licenses::form', ['license' => null, ...$this->formData()]);
    }

    public function store(LicenseRequest $request, ActionRunner $actions): RedirectResponse
    {
        $license = $actions->run(RecordLicense::class, $request->user(), $request->actionInput());

        return to_route('licenses.show', $license)->with('status', __('licenses::licenses.recorded'));
    }

    public function edit(License $license): View
    {
        $license->load(['zones', 'gears', 'species', 'conditions']);

        return view('licenses::form', ['license' => $license, ...$this->formData()]);
    }

    public function update(License $license, LicenseRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpdateLicense::class, $request->user(), [...$request->actionInput(), 'license_id' => $license->id]);

        return to_route('licenses.show', $license)->with('status', __('licenses::licenses.updated'));
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'vessels' => $this->vesselOptions(),
            'zones' => Zone::orderBy('name')->pluck('name', 'id')->all(),
            'gears' => Gear::orderBy('label')->get()->mapWithKeys(fn (Gear $gear) => [$gear->id => "{$gear->code_fao} - {$gear->label}"])->all(),
            'species' => Species::orderBy('name')->get()->mapWithKeys(fn (Species $species) => [$species->id => "{$species->fao_code} - {$species->name}"])->all(),
        ];
    }

    /** @return array<int, string> */
    private function vesselOptions(): array
    {
        return Vessel::orderBy('name')->get(['id', 'name', 'registration_number'])
            ->mapWithKeys(fn (Vessel $vessel) => [$vessel->id => "{$vessel->name} ({$vessel->registration_number})"])
            ->all();
    }
}

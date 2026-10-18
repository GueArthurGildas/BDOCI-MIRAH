<?php

namespace Modules\Licenses\Http\Controllers;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
                ->where('number', 'like', "%{$search}%")
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
}

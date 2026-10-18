<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const LATEST_USERS_LIMIT = 5;

    public function __invoke(): View
    {
        return view('dashboard::index', [
            'stats' => $this->userStats(),
            'latestUsers' => User::latest()->limit(self::LATEST_USERS_LIMIT)->get(),
        ]);
    }

    private function userStats(): array
    {
        return [
            [
                'label' => __('dashboard::dashboard.stats.users'),
                'value' => User::count(),
                'icon' => 'icon-users',
                'color' => 'warning',
            ],
            [
                'label' => __('dashboard::dashboard.stats.new_this_month'),
                'value' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'icon' => 'icon-user-plus',
                'color' => 'success',
            ],
            [
                'label' => __('dashboard::dashboard.stats.verified'),
                'value' => User::whereNotNull('email_verified_at')->count(),
                'icon' => 'icon-user-check',
                'color' => 'info',
            ],
            [
                'label' => __('dashboard::dashboard.stats.unverified'),
                'value' => User::whereNull('email_verified_at')->count(),
                'icon' => 'icon-user-x',
                'color' => 'danger',
            ],
        ];
    }
}

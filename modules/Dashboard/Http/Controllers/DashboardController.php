<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'stats' => [
                ['label' => __('Users'), 'value' => User::count(), 'icon' => 'icon-users', 'color' => 'yellow'],
                ['label' => __('New this month'), 'value' => User::where('created_at', '>=', now()->startOfMonth())->count(), 'icon' => 'icon-user-plus', 'color' => 'green'],
                ['label' => __('Verified users'), 'value' => User::whereNotNull('email_verified_at')->count(), 'icon' => 'icon-user-check', 'color' => 'lite-green'],
                ['label' => __('Unverified'), 'value' => User::whereNull('email_verified_at')->count(), 'icon' => 'icon-user-x', 'color' => 'pink'],
            ],
            'latestUsers' => User::latest()->limit(5)->get(),
        ]);
    }
}

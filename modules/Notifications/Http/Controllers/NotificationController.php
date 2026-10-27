<?php

namespace Modules\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use Modules\Notifications\Support\NotificationPresenter;

class NotificationController extends Controller
{
    private const PER_PAGE = 20;

    private const FEED_LIMIT = 20;

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications::index', [
            'notifications' => $user->notifications()->latest()->paginate(self::PER_PAGE)->through(NotificationPresenter::present(...)),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $unread = $request->user()->unreadNotifications();

        return response()->json([
            'unread' => $unread->count(),
            'items' => $unread->latest()->limit(self::FEED_LIMIT)->get()->map(NotificationPresenter::present(...))->all(),
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->findOrFail($notification);
        $record->markAsRead();

        [$route, $parameters] = $record->data['target'];

        return redirect()->route($route, $parameters);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}

<?php

namespace Modules\Auth\Providers;

use App\Support\Modules\ModuleServiceProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;

class AuthServiceProvider extends ModuleServiceProvider
{
    public const AUDIT_LOG = 'authentication';

    public function boot(): void
    {
        parent::boot();

        Event::listen(Login::class, fn (Login $event) => $this->audit('login', $event->user));
        Event::listen(Logout::class, fn (Logout $event) => $event->user && $this->audit('logout', $event->user));
        Event::listen(Failed::class, fn (Failed $event) => $this->audit('login_failed', $event->user, ['email' => $event->credentials['email'] ?? null]));
    }

    private function audit(string $event, mixed $user, array $properties = []): void
    {
        activity(self::AUDIT_LOG)
            ->causedBy($user)
            ->event($event)
            ->withProperties([...$properties, 'ip' => request()->ip()])
            ->log($event);
    }
}

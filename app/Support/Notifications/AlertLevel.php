<?php

namespace App\Support\Notifications;

enum AlertLevel: string
{
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';
}

<?php

namespace App\Support\Navigation;

enum NavigationGroup: string
{
    case Overview = 'overview';
    case Surveillance = 'surveillance';
    case Registry = 'registry';
    case Controls = 'controls';
    case Administration = 'administration';

    public function label(): string
    {
        return "ui.navigation_groups.{$this->value}";
    }
}

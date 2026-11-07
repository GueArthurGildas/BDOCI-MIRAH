<?php

namespace App\Support\Navigation;

enum NavigationFolder: string
{
    case EInspection = 'e_inspection';
    case MorningBoard = 'morning_board';
    case Logbook = 'logbook';

    public function label(): string
    {
        return "ui.navigation_folders.{$this->value}";
    }

    public function icon(): string
    {
        return match ($this) {
            self::EInspection => 'icon-check-square',
            self::MorningBoard => 'icon-sunrise',
            self::Logbook => 'icon-book',
        };
    }

    public function group(): NavigationGroup
    {
        return match ($this) {
            self::EInspection => NavigationGroup::Overview,
            self::MorningBoard, self::Logbook => NavigationGroup::Surveillance,
        };
    }

    public function parent(): ?self
    {
        return match ($this) {
            self::Logbook => self::MorningBoard,
            default => null,
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::EInspection => 10,
            self::MorningBoard => 20,
            self::Logbook => 4,
        };
    }
}

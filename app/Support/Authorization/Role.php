<?php

namespace App\Support\Authorization;

enum Role: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case SurveillanceAgent = 'surveillance_agent';
    case Inspector = 'inspector';

    public function label(): string
    {
        return "ui.roles.{$this->value}";
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => __($role->label())])
            ->all();
    }

    /** @return array<int, Permission> */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Supervisor => array_values(array_filter(
                Permission::cases(),
                fn (Permission $permission) => $permission !== Permission::UsersManage,
            )),
            self::SurveillanceAgent => [
                Permission::DashboardView,
                Permission::VesselsView,
                Permission::LicensesView,
                Permission::TrackingView,
                Permission::LogbookView,
                Permission::LogbookCreate,
                Permission::LogbookManage,
                Permission::MovementsView,
                Permission::MovementsCreate,
                Permission::InspectionsView,
                Permission::InfractionsView,
                Permission::CatchesView,
                Permission::MissionsView,
            ],
            self::Inspector => [
                Permission::DashboardView,
                Permission::VesselsView,
                Permission::LicensesView,
                Permission::InspectionsView,
                Permission::InfractionsView,
                Permission::CatchesView,
                Permission::MissionsView,
            ],
        };
    }
}

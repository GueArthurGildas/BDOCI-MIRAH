<?php

namespace App\Support\Authorization;

enum Role: string
{
    case Admin = 'admin';
    case Director = 'director';
    case TeamLead = 'team_lead';
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

    public function inheritsFrom(): ?self
    {
        return match ($this) {
            self::Director => self::TeamLead,
            self::TeamLead => self::SurveillanceAgent,
            default => null,
        };
    }

    /** @return array<int, Permission> */
    public function permissions(): array
    {
        $own = match ($this) {
            self::Admin => [
                Permission::UsersManage,
                Permission::ConnectorsView,
                Permission::AuditView,
                Permission::SettingsManage,
                Permission::ReferenceDataManage,
                Permission::AiActionsManage,
            ],
            self::Director => [],
            self::TeamLead => [
                Permission::VesselsManage,
                Permission::LicensesManage,
                Permission::InfractionsManage,
                Permission::MissionsManage,
                Permission::ConnectorsView,
                Permission::AuditView,
            ],
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

        $inherited = $this->inheritsFrom()?->permissions() ?? [];

        return collect([...$inherited, ...$own])
            ->unique(fn (Permission $permission) => $permission->value)
            ->values()
            ->all();
    }
}

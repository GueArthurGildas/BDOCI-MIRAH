<?php

namespace App\Support\Authorization;

use App\Models\RoleRecord as RoleModel;

enum Role: string
{
    case Admin = 'admin';
    case Director = 'director';
    case TeamLead = 'team_lead';
    case Agent = 'agent';

    private const TECHNICAL_PERMISSIONS = [
        Permission::UsersManage,
        Permission::RolesManage,
        Permission::SettingsManage,
        Permission::ConnectorsView,
        Permission::ConnectorsManage,
        Permission::AuditView,
    ];

    public function label(): string
    {
        return "ui.roles.{$this->value}";
    }

    public function parent(): ?self
    {
        return match ($this) {
            self::Director => self::TeamLead,
            self::TeamLead => self::Agent,
            default => null,
        };
    }

    public function description(): string
    {
        return "ui.role_descriptions.{$this->value}";
    }

    public static function isSystem(string $name): bool
    {
        return self::tryFrom($name) !== null;
    }

    public static function labelFor(string $name): string
    {
        $system = self::tryFrom($name);

        return $system ? __($system->label()) : $name;
    }

    public static function descriptionFor(RoleModel $role): ?string
    {
        $system = self::tryFrom($role->name);

        return $system ? __($system->description()) : $role->description;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return RoleModel::orderBy('name')->pluck('name')
            ->mapWithKeys(fn (string $name) => [$name => self::labelFor($name)])
            ->all();
    }

    /** @return array<int, Permission> */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => [Permission::DashboardView, ...self::TECHNICAL_PERMISSIONS],
            self::Director, self::TeamLead => [
                ...self::businessPermissions(),
                Permission::ConnectorsView,
            ],
            self::Agent => [
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
                Permission::CommunicationsView,
                Permission::CommunicationsSend,
                Permission::CommunicationsCreateNote,
            ],
        };
    }

    /** @return array<int, Permission> */
    private static function businessPermissions(): array
    {
        return array_values(array_filter(
            Permission::cases(),
            fn (Permission $permission) => ! in_array($permission, self::TECHNICAL_PERMISSIONS, true),
        ));
    }
}

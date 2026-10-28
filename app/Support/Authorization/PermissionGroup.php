<?php

namespace App\Support\Authorization;

enum PermissionGroup: string
{
    case LicenseManagement = 'license_management';
    case InfractionInvestigation = 'infraction_investigation';
    case SurveillanceOpinion = 'surveillance_opinion';
    case AutomaticSending = 'automatic_sending';
    case AiTrigger = 'ai_trigger';

    public const ROLE_PREFIX = 'group.';

    public function roleName(): string
    {
        return self::ROLE_PREFIX.$this->value;
    }

    public function label(): string
    {
        return "ui.permission_groups.{$this->value}";
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $group) => [$group->value => __($group->label())])
            ->all();
    }

    /** @return array<int, Permission> */
    public function permissions(): array
    {
        return match ($this) {
            self::LicenseManagement => [Permission::LicensesManage],
            self::InfractionInvestigation => [Permission::InfractionsManage],
            self::SurveillanceOpinion => [],
            self::AutomaticSending => [],
            self::AiTrigger => [Permission::AiTrigger],
        };
    }
}

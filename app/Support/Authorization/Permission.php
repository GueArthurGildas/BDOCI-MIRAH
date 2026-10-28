<?php

namespace App\Support\Authorization;

enum Permission: string
{
    case DashboardView = 'dashboard.view';
    case VesselsView = 'vessels.view';
    case VesselsManage = 'vessels.manage';
    case LicensesView = 'licenses.view';
    case LicensesManage = 'licenses.manage';
    case TrackingView = 'tracking.view';
    case LogbookView = 'logbook.view';
    case LogbookCreate = 'logbook.create';
    case LogbookManage = 'logbook.manage';
    case MovementsView = 'movements.view';
    case MovementsCreate = 'movements.create';
    case InspectionsView = 'inspections.view';
    case InfractionsView = 'infractions.view';
    case InfractionsManage = 'infractions.manage';
    case CatchesView = 'catches.view';
    case MissionsView = 'missions.view';
    case MissionsManage = 'missions.manage';
    case UsersManage = 'users.manage';
    case ConnectorsView = 'connectors.view';
    case ConnectorsManage = 'connectors.manage';
    case SettingsManage = 'settings.manage';
    case RolesManage = 'roles.manage';

    public function module(): string
    {
        $prefix = str($this->value)->before('.')->toString();

        return in_array($prefix, ['users', 'roles', 'settings', 'connectors', 'audit'], true) ? 'administration' : $prefix;
    }

    public function verb(): string
    {
        return match (str($this->value)->afterLast('.')->toString()) {
            'view' => 'view',
            'create' => 'create',
            default => 'update',
        };
    }
    case AuditView = 'audit.view';
    case MovementsDeclare = 'movements.declare';
}

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
    case AuditView = 'audit.view';
}

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
    case CommunicationsView = 'communications.view';
    case CommunicationsSend = 'communications.send';
    case CommunicationsCreateNote = 'communications.create_note';
    case CommunicationsManageTemplates = 'communications.manage_templates';
    case CommunicationsManageDispatch = 'communications.manage_dispatch';
    case CommunicationsTriggerAiAgent = 'communications.trigger_ai_agent';
    case VesselsMatch = 'vessels.match';
    case RegistryView = 'registry.view';
    case RegistryManage = 'registry.manage';
    case LogbookClose = 'logbook.close';
    case OperationsView = 'operations.view';
    case OperationsCreate = 'operations.create';
    case OperationsManage = 'operations.manage';
    case WatchView = 'watch.view';
    case WatchCreate = 'watch.create';
    case WatchManage = 'watch.manage';
    case ReportsView = 'reports.view';
    case ReportsManage = 'reports.manage';
    case ReportsGenerate = 'reports.generate';
    case ReportsExportOperations = 'reports.export_operations';
    case ReportsExportVessels = 'reports.export_vessels';
    case ReportsExportLogbook = 'reports.export_logbook';
    case ReportsArchive = 'reports.archive';
    case ReportsViewHealth = 'reports.view_health';
    case ConnectorsSync = 'connectors.sync';
    case MrepView = 'mrep.view';
    case LicensesImport = 'licenses.import';
    case LicensesValidate = 'licenses.validate';
    case LicensesSuspend = 'licenses.suspend';
    case LicensesRevoke = 'licenses.revoke';
    case LicensesPublish = 'licenses.publish';
    case InfractionsCreate = 'infractions.create';
    case InfractionsDecide = 'infractions.decide';
    case InfractionsDecideMeasures = 'infractions.decide_measures';
    case InfractionsManageTypes = 'infractions.manage_types';
    case MissionsCreate = 'missions.create';
    case MissionsAuthorize = 'missions.authorize';
    case MissionsAuthorizeAsDirector = 'missions.authorize_as_director';
    case WitnessesView = 'witnesses.view';
    case WitnessesCreate = 'witnesses.create';
    case WitnessesManage = 'witnesses.manage';
}

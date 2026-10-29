<?php

return [
    'intro_title' => 'On this page',
    'next' => 'Next',
    'previous' => 'Previous',
    'done' => 'Done',
    'progress' => 'Step :current of :total',
    'generic_intro' => 'This page has no detailed tour. Here are the application\'s landmarks, the same on every screen.',

    'common' => [
        'menu' => ['title' => 'The side menu', 'text' => 'The screens of the chosen family. The current screen is orange; an orange circle with a number flags items waiting.'],
        'tabs' => ['title' => 'The menu families', 'text' => 'Each orange tab opens a family of screens: Overview, Surveillance, Registry, Controls, Administration (depending on your role).'],
        'tools' => ['title' => 'Your tools', 'text' => 'The notification bell, search, this guide, full screen, language and your user menu.'],
        'stats' => ['title' => 'The indicators', 'text' => 'Each figure card sums up a state; some are clickable to open the matching list.'],
        'toolbar' => ['title' => 'The toolbar', 'text' => 'Choose the number of rows, search, filter by date or category. “Reset” appears when a filter is active.'],
        'filters' => ['title' => 'Column filters', 'text' => 'Pick a value under a column header: the list updates at once.'],
        'table' => ['title' => 'The table', 'text' => 'Each row is an item of the list; click its name or reference to open the detail.'],
        'exports' => ['title' => 'Export the list', 'text' => 'Download the list as CSV or PDF. Every export is logged.'],
        'new' => ['title' => 'Create an item', 'text' => 'The orange button creates a new item in this list.'],
    ],

    'pages' => [
        'dashboard' => [
            'intro' => 'Your dashboard: the state of surveillance at a glance, each time you open the application.',
            'steps' => [
                'stats' => ['title' => 'The key indicators', 'text' => 'Active vessels, expiring licences, pending observations and failing beacons. The figures are recalculated each time the page opens.'],
                'zones' => ['title' => 'Situation by zone', 'text' => 'The number of vessels present in each port zone right now.'],
                'infractions' => ['title' => 'Infractions by status', 'text' => 'The distribution of infraction cases: open, under investigation, awaiting decision, closed.'],
                'sources' => ['title' => 'Data freshness', 'text' => 'For each external source: last synchronisation, errors and rejections. A degraded status needs the Administrator\'s attention.'],
                'observations' => ['title' => 'Latest pending observations', 'text' => 'Open or ongoing observations, to follow up or hand over to the next team.'],
            ],
        ],
        'fleet_index' => [
            'intro' => 'The overview of the fleet, reserved for steering profiles.',
            'steps' => [
                'stats' => ['title' => 'The clickable indicators', 'text' => 'Click an indicator to display the list of vessels that make it up.'],
                'filter_card' => ['title' => 'Period and filters', 'text' => 'Narrow the view by period, zone or segment.'],
                'distribution' => ['title' => 'Fleet traffic-light status', 'text' => 'How many vessels are compliant, to watch, suspect or infringing.'],
                'ranking' => ['title' => 'Highest-vigilance vessels', 'text' => 'Vessels ranked by vigilance score (0 to 100); click one to open its record.'],
            ],
        ],
        'vessels_index' => [
            'intro' => 'The list of all tracked vessels. Find a vessel, see its licence and beacon at a glance, then open its record.',
            'steps' => [
                'table' => ['title' => 'The vessels', 'text' => 'Click a vessel name for its full record, or the eye next to it for a quick view without leaving the list.'],
                'new' => ['title' => 'New vessel', 'text' => 'Creates a vessel record by hand. Matching prevents duplicates.'],
            ],
        ],
        'vessels_show' => [
            'intro' => 'A vessel\'s complete record: everything the application knows about it, in one place.',
            'steps' => [
                'header' => ['title' => 'Vessel situation', 'text' => 'The traffic-light status (compliant, to watch, suspect, infringing) with its reasons, then the identifiers, owner, agent and captain.'],
                'summary' => ['title' => 'Summary', 'text' => 'Category, status, licence and beacon state. The Edit button, if shown, opens the edit form.'],
                'position' => ['title' => 'Last known position', 'text' => 'The vessel\'s position and recent track on the map.'],
                'tabs' => ['title' => 'The tabs', 'text' => 'Licences, beacons, alerts, inspections, infractions, missions, messages, timeline… The tabs shown depend on your rights.'],
            ],
        ],
        'vessels_duplicates_index' => [
            'intro' => 'Incoming records whose identity is doubtful wait here for your decision.',
            'steps' => [
                'table' => ['title' => 'Settle a duplicate', 'text' => 'Compare the incoming record with the existing one, then choose: merge, keep separate or reject. A confirmation is asked before acting.'],
            ],
        ],
        'watch_board_index' => [
            'intro' => 'The morning board: what to read when you start your shift.',
            'steps' => [
                'stats' => ['title' => 'Three counters', 'text' => 'Vessels to watch, open observations and relays to read.'],
                'relays' => ['title' => 'Relays received', 'text' => 'The observations passed on by the previous team; click “Acknowledge” for each one.'],
                'checks' => ['title' => 'Vessels checked too often', 'text' => 'Beyond the check threshold, enhanced monitoring is suggested; confirm it if you agree.'],
                'watched' => ['title' => 'Vessels on watch', 'text' => 'The vessels to watch: Director\'s list, enhanced monitoring, EEZ entries and exits…'],
                'instructions' => ['title' => 'Standing instructions', 'text' => 'Information to apply until it is lifted.'],
                'observations' => ['title' => 'Open observations', 'text' => 'Ongoing findings, to follow up during the shift.'],
                'add' => ['title' => 'Add a vessel to watch', 'text' => 'Place a vessel under enhanced monitoring, or on the Director\'s list if your role allows it.'],
            ],
        ],
        'watch_observations_index' => [
            'intro' => 'Surveillance findings, vessel by vessel, with their relay to the next team.',
            'steps' => [
                'new' => ['title' => 'New observation', 'text' => 'Creates the logbook line, increases the vessel\'s check counter and can relay the observation to the next team.'],
            ],
        ],
        'logbook_days_show' => [
            'intro' => 'The shift logbook: the WHEN / HOW / WHAT journal.',
            'steps' => [
                'entries' => ['title' => 'The shift lines', 'text' => 'Each fact received is a line: when, how, what. After closing, the lines are locked.'],
                'new_entry' => ['title' => 'Add a line', 'text' => 'Enter the time, the means, what happened, and attach the vessels concerned.'],
                'close_day' => ['title' => 'Close the day', 'text' => 'Reserved for the Team lead: generates an unalterable PDF and locks the lines (confirmation asked).'],
                'pdf' => ['title' => 'PDF copy', 'text' => 'The day\'s frozen PDF, whose integrity can be checked.'],
            ],
        ],
        'tracking_map' => [
            'intro' => 'The situation at sea: one point per vessel at its last position.',
            'steps' => [
                'map' => ['title' => 'The map', 'text' => 'Zoom with + and −, drag to move around, click a point to see the vessel. Surveillance zones are drawn.'],
            ],
        ],
        'tracking_alerts_index' => [
            'intro' => 'Alerts raised by the surveillance rules: beacon silences, geofencing, speed.',
            'steps' => [
                'stats' => ['title' => 'Alerts by status', 'text' => 'How many alerts are open, acknowledged or handled.'],
                'table' => ['title' => 'The alerts list', 'text' => 'Open an alert to acknowledge then handle it: check the position and the beacon, record the measures taken.'],
            ],
        ],
        'tracking_slots_index' => [
            'intro' => 'A control slot takes a photo of the positions and offers three assisted tests.',
            'steps' => [
                'new' => ['title' => 'Open a slot', 'text' => 'Loads the positions of the moment; you then confirm or correct the tests vessel by vessel.'],
                'table' => ['title' => 'The slots', 'text' => 'Open a slot to confirm positions and run a sweep.'],
            ],
        ],
        'operations_index' => [
            'intro' => 'Operations opened for a complex fact, with their code OPS-number-category-year-NAME.',
            'steps' => [
                'new' => ['title' => 'New operation', 'text' => 'Opens a file for a complex fact; its code is generated automatically.'],
            ],
        ],
        'operations_show' => [
            'intro' => 'An operation\'s file, from the first finding to closing.',
            'steps' => [
                'details' => ['title' => 'Details', 'text' => 'Category, vessels concerned, team and state of the operation.'],
                'events' => ['title' => 'Timeline', 'text' => 'All the operation\'s events.'],
                'add_event' => ['title' => 'Add an event', 'text' => 'Record each development as it happens.'],
                'instructions' => ['title' => 'Instructions', 'text' => 'The instructions given to the teams for this operation.'],
                'close' => ['title' => 'Close the operation', 'text' => 'Reserved for the Team lead: fill in the outcome then close; the operation\'s watch lines close.'],
            ],
        ],
        'licenses_index' => [
            'intro' => 'All the fishing licences tracked, with their expiry dates.',
            'steps' => [
                'stats' => ['title' => 'Licences by status and expiry', 'text' => 'Each card is clickable: it filters the list (valid, expired, expiring in 30, 60 or 90 days…).'],
                'new' => ['title' => 'New licence', 'text' => 'Records a licence already issued outside the application.'],
            ],
        ],
        'licenses_show' => [
            'intro' => 'A licence record: identity, authorized scope, documents, suspensions and revocation.',
            'steps' => [
                'identity' => ['title' => 'Licence identity', 'text' => 'Number, vessel, segment, dates and status.'],
                'scope' => ['title' => 'Zones, gear and species', 'text' => 'What the licence authorizes: fishing zones, gear, species and conditions.'],
                'documents' => ['title' => 'Documents', 'text' => 'The documents attached to the licence; each addition is traced.'],
                'suspension' => ['title' => 'Suspensions', 'text' => 'Suspend the licence with a reason and a planned end (three months at most except force majeure). A suspension is lifted explicitly.'],
                'revocation' => ['title' => 'Revocation', 'text' => 'A final decision: a confirmation is asked. Reserved for the Team lead and the Director.'],
            ],
        ],
        'infractions_index' => [
            'intro' => 'Infraction cases, from opening to closing.',
            'steps' => [
                'new' => ['title' => 'Open a case', 'text' => 'Builds a case from a finding, an inspection, a mission or a witness report.'],
            ],
        ],
        'infractions_show' => [
            'intro' => 'The complete file of an infraction, in the order of the investigation.',
            'steps' => [
                'case' => ['title' => 'The finding', 'text' => 'The vessel, the parties, the facts and the case status.'],
                'upheld' => ['title' => 'Upheld infractions', 'text' => 'The nomenclature infractions upheld against the vessel.'],
                'evidence' => ['title' => 'The evidence', 'text' => 'Each piece of evidence is recorded with its date and author; seals and seizures follow in the neighbouring cards.'],
                'measures' => ['title' => 'Precautionary measures', 'text' => 'The Team lead proposes them; only the Director decides them.'],
                'violation_report' => ['title' => 'Violation report', 'text' => 'Drawn up once, it is frozen as a PDF.'],
                'follow_ups' => ['title' => 'Follow-ups, decisions, payments and appeals', 'text' => 'The follow-up given to the case, the decision, the payments and any appeals. These decisions stay human.'],
                'status' => ['title' => 'Closing the case', 'text' => 'When everything is settled, “Close the case” (with confirmation) ends the investigation.'],
            ],
        ],
        'missions_index' => [
            'intro' => 'Control missions: scheduled, authorized, ongoing, completed or closed.',
            'steps' => [
                'new' => ['title' => 'Schedule a mission', 'text' => 'The mission will be authorized according to its type (the Director by default).'],
            ],
        ],
        'missions_show' => [
            'intro' => 'A mission\'s file, from scheduling to review.',
            'steps' => [
                'mission' => ['title' => 'The mission', 'text' => 'Its state and the cycle buttons: Authorize, Start, Complete, Close (each with confirmation).'],
                'map' => ['title' => 'Track and encounters', 'text' => 'The imported GPS track and the zones crossed.'],
                'units' => ['title' => 'Encountered units', 'text' => 'Sighted, approached or inspected: one record, three levels; “inspected” requires the link to the inspection.'],
                'briefing' => ['title' => 'Briefing and preparation', 'text' => 'The preparation before departure.'],
                'review' => ['title' => 'Review', 'text' => 'Calculated automatically from the mission\'s data, never entered.'],
                'participants' => ['title' => 'Team', 'text' => 'The people involved in the mission.'],
            ],
        ],
        'witnesses_index' => [
            'intro' => 'Witness reports received by e-mail, phone, fax, mail or paper form.',
            'steps' => [
                'new' => ['title' => 'Record a report', 'text' => 'Enter the report received: the witness never has access to the application.'],
            ],
        ],
        'communications_messages_index' => [
            'intro' => 'The history of messages sent and of those waiting for validation.',
            'steps' => [
                'new' => ['title' => 'Send a message', 'text' => 'Choose a template, a channel and a recipient; a confirmation recaps the sending.'],
            ],
        ],
        'movements_eez_index' => [
            'intro' => 'EEZ entry and exit declarations.',
            'steps' => [
                'new' => ['title' => 'Enter a declaration', 'text' => 'Entry or exit, with the catches as announced by the vessel; the application checks and pairs.'],
            ],
        ],
        'movements_index' => [
            'intro' => 'Vessel movements in ports.',
            'steps' => [
                'new' => ['title' => 'Record a movement', 'text' => 'Port entry or exit, with the reason.'],
            ],
        ],
        'catches_index' => [
            'intro' => 'Declared catches, viewable by species, zone and source.',
        ],
        'inspections_index' => [
            'intro' => 'Inspections read from e-inspection: view only.',
        ],
        'administration_users_index' => [
            'intro' => 'User accounts: creation, role, groups and deactivation.',
            'steps' => [
                'new' => ['title' => 'New user', 'text' => 'Create an account, choose its role and groups; deactivate it to remove access at once.'],
            ],
        ],
        'administration_roles_index' => [
            'intro' => 'The roles and what each one can do.',
            'steps' => [
                'list' => ['title' => 'The roles', 'text' => 'The four system roles (their permissions are editable) and your custom roles.'],
                'matrix' => ['title' => 'The permissions matrix', 'text' => 'Each row is a right, each column a role: see at a glance who can do what.'],
                'new' => ['title' => 'New role', 'text' => 'Create a role, optionally inheriting from a parent role, then tick its permissions.'],
            ],
        ],
        'administration_settings' => [
            'intro' => 'The application\'s thresholds, delays and switches, editable without a developer.',
            'steps' => [
                'group' => ['title' => 'A settings group', 'text' => 'Change a value within the allowed bounds and save: the effect is immediate. “Restore defaults” brings everything back.'],
            ],
        ],
        'administration_connectors' => [
            'intro' => 'External data sources and their state.',
            'steps' => [
                'source' => ['title' => 'A source', 'text' => 'A green, orange or red indicator and a stale-data flag. Open it to see its synchronisations, rejections and errors.'],
            ],
        ],
        'reports_exports_index' => [
            'intro' => 'Data exports as CSV or PDF, all logged.',
            'steps' => [
                'type' => ['title' => 'An export type', 'text' => 'Choose the format and filters then download; each export is written to the log.'],
                'log' => ['title' => 'Export log', 'text' => 'Who exported what, when, with which filters and how many rows: it cannot be altered.'],
            ],
        ],
    ],
];

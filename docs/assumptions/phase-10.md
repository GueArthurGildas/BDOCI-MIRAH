# Hypothèses des retours du 25/11/2026

Statut de toutes les entrées : provisoire, à confirmer par Gildas. Source des demandes : retours de l'utilisateur du 25/11/2026 sur l'application (tableau du matin, main courante, créneaux, situation par zone).

## H-10-1

| Champ | Contenu |
|---|---|
| Identifiant | H-10-1 |
| Question | « Pour les créneaux de contrôle et la situation par zone, rassure-toi qu'on puisse saisir manuellement les éléments clés le temps de trouver l'automatique avec les systèmes externes. » Quels éléments, et où les ranger ? |
| Option retenue | L'élément clé saisi est la **position constatée d'un navire** (date et heure, zone et/ou coordonnées, vitesse et activité facultatives). Elle est rangée dans `positions` avec `system = manual` : elle compte dans la situation par zone, la carte et la photo des créneaux suivants. Sans coordonnées, le navire est placé au centre de la zone choisie (`ST_PointOnSurface`) ; sans zone, la zone est retrouvée à partir des coordonnées. Depuis un créneau, la position est aussi ajoutée à la photo (`loading_source = manual`), les trois tests sont recalculés et les comptages par zone refaits. Une position manuelle **ne vaut jamais émission de balise** : le test d'émission et l'état de la balise ne regardent que les positions VMS, AIS ou radar |
| Alternatives | saisir directement un nombre de navires par zone, sans navire ; une table séparée des constats manuels ; importer un fichier THEMIS |
| Où | `Modules\Tracking\Actions\RecordManualPosition`, `ManualPositionController`, `App\Support\Vms\LocalPositionFeed` (émission), `Vessel::beaconStatus()` |
| Pour changer | brancher un vrai pilote `PositionFeed` (H-3b-1) ; la saisie manuelle reste disponible en secours |
| Tests | `Modules\Tracking\Tests\Feature\ManualPositionTest` |

## H-10-2

| Champ | Contenu |
|---|---|
| Identifiant | H-10-2 |
| Question | « Permettre au Chef d'équipe et au Responsable du CSP de supprimer les enregistrements. » Le manuel (PROCOP_CSP/01) fige la main courante à la clôture et corrige par ligne rectificative : que veut dire supprimer ? |
| Option retenue | Suppression **tracée**, jamais effacement : observations, vérifications de navires et signalements reçoivent `deleted_at`, `deleted_by` et un motif obligatoire, et disparaissent des listes. Leur ligne de main courante reste visible, **barrée**, avec l'auteur, la date et le motif, à l'écran comme sur le PDF et le Word. Une ligne libre de la main courante du jour se supprime de la même façon ; une ligne qui vient d'une observation, d'un signalement, d'une vérification ou d'une opération se supprime par son enregistrement d'origine. Rien ne se supprime dans une journée clôturée (ligne rectificative). Droits `logbook.delete`, `watch.delete`, `witnesses.delete` : Chef d'équipe et Directeur, pas l'agent |
| Alternatives | effacement réel ; suppression possible après clôture par le Directeur ; suppression des navires et des opérations |
| Où | `App\Support\Logbook\RecordDeletion`, actions `DeleteObservation`, `DeleteLogbookEntry`, `DeleteSurveillanceObservation`, `DeleteWitnessReport` |
| Pour changer | modifier `RecordDeletion::ensureDeletable()` (journées autorisées) ou les droits dans l'administration |
| Tests | `Modules\Logbook\Tests\Feature\RecordDeletionTest` |

## H-10-3

| Champ | Contenu |
|---|---|
| Identifiant | H-10-3 |
| Question | « La relève se fait une seule fois avec l'équipe de relais » ; « le responsable du CSP est connu par défaut, son nom modifiable par l'admin » |
| Option retenue | Une seule relève par créneau de relève (08 h et 18 h, créneau le plus proche de l'heure saisie) dans une journée de main courante ; une deuxième est refusée. Le responsable du CSP n'est plus saisi : c'est le réglage « Responsable du CSP » de l'administration, ou à défaut le premier Directeur actif ; son nom est figé sur la relève (`supervisor_name`). Même règle à l'ouverture d'une opération : plus de champ « Responsable du CSP », le nom est figé dans `operations.csp_manager_name` et imprimé sur le dossier |
| Alternatives | responsable choisi parmi les utilisateurs ; plusieurs relèves autorisées par créneau |
| Où | `LogbookCalendar::handoverSlot()`, `RecordShiftHandover`, `App\Support\Logbook\CspManager`, réglage `watch.csp_manager` |
| Pour changer | modifier `handoverSlot()` ou `CspManager::name()` |
| Tests | `Modules\Logbook\Tests\Feature\ShiftHandoverTest`, `Modules\Operations\Tests\Feature\OperationLogbookTest` |

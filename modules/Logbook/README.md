# Logbook

Main courante : journées QUAND / COMMENT / QUOI, clôture avec PDF figé et lignes rectificatives, plus les pages historiques d'observations (`/logbook`).

- Tables : `logbook_days`, `logbook_closings`, `logbook_entries`, `logbook_entry_vessel`, `shift_handovers` (cartouche de relève) (modèles dans `app/Models`) et l'ancienne `observations`.
- Actions : `AddLogbookEntry`, `SetLogbookEntryHandling`, `RectifyLogbookEntry`, `RecordShiftHandover`, `CloseLogbookDay`, `OpenObservation`, `UpdateObservation` (ces deux dernières reflètent l'observation dans `surveillance_observations`).
- Permissions : `logbook.view`, `logbook.create`, `logbook.manage`, `logbook.close` (clôturer la journée) et `logbook.handover` (relève de quart) : tout agent, Chef d'équipe, Directeur.
- Le déclencheur `logbook_entries_locked` (migration `2026_11_03_100003`) interdit la modification d'une ligne d'une journée verrouillée.
- Hypothèses : H-3a-1, H-3a-4, H-3a-6, H-3a-7, H-3a-9, H-3a-10 (`docs/assumptions/phase-3a.md`).

## Filtres, dossiers, document Word et chronologie

- **Filtres** de la liste des journées : texte (contenu d'une ligne, navire cité ou auteur), statut, canal, période ; ils s'appliquent à la saisie.
- **Vue en dossiers** (`?view=folders`) : arbre année, mois, jour ; le jour choisi se déplie en rubriques (entrées par canal, navires impliqués, acteurs impliqués, opérations liées, ordres et décisions) construites par `Support/LogbookDayFolders`. Le volet de droite montre la synthèse du jour (compteurs, navires, acteurs, opérations, chronologie) ou les entrées de la rubrique choisie.
- **Documents imprimés** : `App\Support\Logbook\LogbookDayDocument` construit la main courante selon le modèle `Main_courante_CSP_du_AAAA_MM_JJ` du manuel (en-tête officiel, pour chaque relève son cartouche puis les lignes QUAND / COMMENT / QUOI qui la suivent, ouvertures d'opérations en rouge et clôtures en vert, cartouche de clôture orange). Le même document est rendu en Word (`logbook.days.word`), en PDF à la demande pour une journée ouverte (`logbook.days.print`) et en PDF figé à la clôture ; seul ce dernier est non modifiable.
- **Opérations** : une information saisie pour une opération en cours est inscrite dans la seule main courante de l'opération (action `RecordOperationLogbookLine`, qui partage `App\Support\Operations\OperationLogbook` avec `AddOperationEvent` du module Operations) ; la page d'une journée liste les opérations en cours avec leur statut.
- **Chronologie** : sur la page d'une journée et dans la vue en dossiers, avec une légende (reçu, émis, ordre ou décision, ligne rectificative).

## Journée, relève et clôture (manuel PROCOP_CSP/01, règles validées le 19/11/2026)

- Une journée couvre 24 heures, de la relève du matin (08 h UTC) à celle du lendemain ; `LogbookCalendar::dayFor` rattache chaque ligne et chaque relève à sa journée.
- **Relève de quart** (`logbook.handovers.create`, à 08 h et 18 h) : l'opérateur quittant choisit l'opérateur prenant et le responsable du CSP, saisit les informations complémentaires et consignes ; les compteurs d'opération et les opérations ouvertes sont figés par `App\Support\Logbook\OperationSnapshot`. Le cartouche s'affiche en tête de la journée (`partials/handover`).
- **Clôture** : possible seulement après la fin des 24 heures, à la relève du matin, par tout agent ; le cartouche de clôture (`partials/closing`) reprend compteurs, opérations ouvertes, consignes et nombre d'opérations ouvertes et clôturées par catégorie. Les journées terminées mais non clôturées sont signalées en tête de la page d'une journée.

## Observations dans la main courante (H-3a-10)

- Les pages `/logbook` s'appellent désormais « Observations » : chaque observation enregistrée (`OpenObservation`) écrit une ligne dans la main courante journalière, canal « Interne », et garde le lien (`observations.logbook_entry_id`, recopié dans `surveillance_observations`).
- Les rapports d'observation visuelle des témoins (module Witnesses) écrivent aussi leur ligne à leur heure de réception (`witness_reports.logbook_entry_id`).
- Sur la page d'une journée, une ligne affiche l'observation ou le rapport d'où elle vient, avec son statut (`partials/entry-sources`) ; chaque ligne a l'ancre `#entry-<id>` vers laquelle pointent l'observation, le rapport et la fiche navire. La main courante imprimée cite l'observation ou le rapport dans la colonne QUOI.

# Logbook

Main courante : journées QUAND / COMMENT / QUOI, clôture avec PDF figé et lignes rectificatives, plus les pages historiques d'observations (`/logbook`).

- Tables : `logbook_days`, `logbook_closings`, `logbook_entries`, `logbook_entry_vessel` (modèles dans `app/Models`) et l'ancienne `observations`.
- Actions : `AddLogbookEntry`, `SetLogbookEntryHandling`, `RectifyLogbookEntry`, `CloseLogbookDay`, `OpenObservation`, `UpdateObservation` (ces deux dernières reflètent l'observation dans `surveillance_observations`).
- Permissions : `logbook.view`, `logbook.create`, `logbook.manage`, `logbook.close` (clôturer la journée : Chef d'équipe, Directeur).
- Le déclencheur `logbook_entries_locked` (migration `2026_11_03_100003`) interdit la modification d'une ligne d'une journée verrouillée.
- Hypothèses : H-3a-1, H-3a-4, H-3a-6, H-3a-7 (`docs/assumptions/phase-3a.md`).

## Filtres, dossiers, document Word et chronologie

- **Filtres** de la liste des journées : texte (contenu d'une ligne, navire cité ou auteur), statut, canal, période ; ils s'appliquent à la saisie.
- **Vue en dossiers** (`?view=folders`) : arbre année, mois, jour ; le jour choisi se déplie en rubriques (entrées par canal, navires impliqués, acteurs impliqués, opérations liées, ordres et décisions) construites par `Support/LogbookDayFolders`. Le volet de droite montre la synthèse du jour (compteurs, navires, acteurs, opérations, chronologie) ou les entrées de la rubrique choisie.
- **Document Word** : `logbook.days.word` génère un `.docx` (PhpWord) avec le même contenu que le PDF de clôture, plus les navires et acteurs impliqués (`Support/LogbookDayWord`). Il est disponible pour une journée ouverte comme pour une journée close ; seul le PDF de clôture est non modifiable.
- **Chronologie** : sur la page d'une journée et dans la vue en dossiers, avec une légende (reçu, émis, ordre ou décision, ligne rectificative).

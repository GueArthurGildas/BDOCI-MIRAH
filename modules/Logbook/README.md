# Logbook

Main courante : journées QUAND / COMMENT / QUOI, clôture avec PDF figé et lignes rectificatives, plus les pages historiques d'observations (`/logbook`).

- Tables : `logbook_days`, `logbook_closings`, `logbook_entries`, `logbook_entry_vessel` (modèles dans `app/Models`) et l'ancienne `observations`.
- Actions : `AddLogbookEntry`, `SetLogbookEntryHandling`, `RectifyLogbookEntry`, `CloseLogbookDay`, `OpenObservation`, `UpdateObservation` (ces deux dernières reflètent l'observation dans `surveillance_observations`).
- Permissions : `logbook.view`, `logbook.create`, `logbook.manage`, `logbook.close` (clôturer la journée : Chef d'équipe, Directeur).
- Le déclencheur `logbook_entries_locked` (migration `2026_11_03_100003`) interdit la modification d'une ligne d'une journée verrouillée.
- Hypothèses : H-3a-1, H-3a-4, H-3a-6, H-3a-7 (`docs/assumptions/phase-3a.md`).

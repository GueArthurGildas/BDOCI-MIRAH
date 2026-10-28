# Hypothèses de la phase 9 (M10 Pilotage)

Statut de toutes les entrées : provisoire, à confirmer par Gildas.

## H-9-1

| Champ | Contenu |
|---|---|
| Identifiant | H-9-1 |
| Question | archivage à 5 ans : à partir de quelle date, et quelles opérations |
| Option retenue | une opération clôturée (`status = closed`) dont `ended_at` a plus de 5 ans passe en `archived`, avec une ligne dans `operation_archives` ; un trigger interdit ensuite toute modification de l'opération et de ses tables filles |
| Alternatives | compter depuis la date d'ouverture ; archiver aussi les lignes de main courante et de veille liées |
| Où | réglage `archive.after_years` (`config/surveillance.php`), `Support/ArchivableOperations`, migration `2026_11_11_100002` |
| Pour changer | modifier le réglage ou `ArchivableOperations::query()` et `isEligible()` |
| Tests | `ReportsTest::test_only_operations_closed_for_five_years_are_archived_and_become_read_only`, `test_the_database_blocks_changes_to_the_children_of_an_archived_operation`, `test_the_archive_threshold_is_a_setting` |

## H-9-2

| Champ | Contenu |
|---|---|
| Identifiant | H-9-2 |
| Question | contenu et format des rapports programmés (hebdomadaire, mensuel, annuel ; PDF ou Excel) |
| Option retenue | catalogue de deux rapports (activité des opérations, activité de la main courante) sur la dernière période civile terminée ; « Excel » est un CSV séparé par des points-virgules avec BOM UTF-8 (aucune bibliothèque ajoutée) ; document figé, un par rapport et par période, en français |
| Alternatives | rapports métier détaillés fournis par Gildas ; vrai format xlsx |
| Où | `Support/ReportCatalog`, `Support/ReportPeriod`, `Support/ExportRenderer`, `config/reports.php` |
| Pour changer | ajouter un code dans `ReportCatalog`, ou remplacer le rendu « excel » dans `ExportRenderer` |
| Tests | `ReportsTest::test_the_scheduled_command_freezes_one_document_per_period_and_notifies_the_recipient`, `test_an_excel_report_is_a_semicolon_separated_file` |

## H-9-3

| Champ | Contenu |
|---|---|
| Identifiant | H-9-3 |
| Question | contenu du bandeau « santé du système » |
| Option retenue | affiché aux détenteurs de `reports.view_health` seulement quand un connecteur n'est pas opérationnel ou a des erreurs ouvertes ou des enregistrements rejetés à examiner ; rouge si un connecteur est arrêté |
| Alternatives | ajouter péremption des synchronisations, file de tâches, sauvegardes |
| Où | `App\Support\Health\SystemHealth` |
| Pour changer | modifier `affectedSources()` |
| Tests | `ReportsTest::test_the_health_banner_shows_for_supervisors_when_a_source_has_a_problem` |

## H-9-4

| Champ | Contenu |
|---|---|
| Identifiant | H-9-4 |
| Question | quels exports existent et sous quel droit (droits d'export par rôle) |
| Option retenue | trois exports (opérations, navires, main courante) en CSV ou PDF, chacun sous sa permission, accordée par défaut au Directeur et au Chef d'équipe ; chaque export écrit une ligne d'`export_log` |
| Alternatives | d'autres jeux de données ; exports en xlsx |
| Où | `Support/ExportType`, `Support/ExportDatasets`, `Role::permissions()` |
| Pour changer | ajouter un cas à `ExportType` et une action fille d'`ExportAction` |
| Tests | `ReportsTest::test_an_export_is_logged_with_user_permission_filters_and_row_count`, `test_exports_need_the_permission_of_their_type_and_write_nothing_when_refused` |

# Hypothèses du lot 6 (retours de recette du 31/10 au 01/11/2026)

Statut de toutes les entrées : provisoire, à confirmer par Gildas.

## H-6-1

| Champ | Contenu |
|---|---|
| Identifiant | H-6-1 |
| Question | « Catégoriser les alertes dans l'interface utilisateur : urgences, alertes les plus utiles à ses tâches, autres » (cahier, §4) : qu'est-ce qu'une urgence, qu'est-ce qui est utile à une personne ? |
| Option retenue | **Urgences** : alerte ouverte (pas encore prise en compte) de zone interdite, ou sur un navire dont le statut à feux est « suspect » ou « en infraction ». **Utiles à mes tâches** : alerte en cours (ouverte ou prise en compte), pas urgente, sur un navire que l'utilisateur suit : navire sous veille ouverte, navire d'une opération ouverte ou en cours créée par lui, ou alerte qu'il a lui-même prise en compte. **Autres** : toutes les autres, y compris les alertes closes |
| Alternatives | urgence selon le type d'alerte seulement ; utilité selon l'équipe, la zone ou une mission de l'utilisateur ; niveau de gravité porté par la règle d'alerte |
| Où | `App\Support\Alerts\AlertTriage` (conditions), `App\Enums\AlertCategory` (catégories) ; utilisé par la page des alertes, l'aperçu au survol du menu et le profil |
| Pour changer | modifier `AlertTriage::urgent()` ou `AlertTriage::followed()` |
| Tests | `Modules\Tracking\Tests\Feature\AlertCategoriesTest` |

## H-6-2

| Champ | Contenu |
|---|---|
| Identifiant | H-6-2 |
| Question | « Dans son interface personnelle, il voit : les messages qui lui sont adressés, les alertes qui le concernent, les urgences qui le concernent, les activités en attente » (cahier, §4) : que veut dire « le concerne » ? |
| Option retenue | **Messages** : notes adressées à l'utilisateur ou à son équipe. **Alertes** : voir H-6-1. **Urgences** : alertes urgentes (H-6-1) et messages d'urgence élevée non lus qui lui sont adressés. **Activités en attente** : observations non closes écrites par lui ou par son équipe, messages non lus avec une échéance, opérations ouvertes ou en cours qu'il a créées. Les missions ne sont pas incluses : leur lecture passe par un point unique (`VesselMissions`) |
| Alternatives | inclure les missions où l'utilisateur est participant ; inclure les dossiers d'infraction qu'il instruit |
| Où | `Modules\Profile\Support\PersonalSpace` |
| Pour changer | modifier la requête correspondante dans `PersonalSpace::for` |
| Tests | `Modules\Profile\Tests\Feature\ProfileTest` |

## H-6-3

| Champ | Contenu |
|---|---|
| Identifiant | H-6-3 |
| Question | « Captures (quantités par espèce) : être plus précis par navire et par période. Si possible, ouvrir dans e-inspection une recherche pointant sur le navire et ses captures » (cahier, §8) : que veut dire « ouvrir dans e-inspection une recherche » ? |
| Option retenue | la rubrique e-inspection de l'application (menu Pilotage) : la page Captures se filtre par navire et par période (30 jours, 3 mois, 12 mois, ou dates libres), et la fiche du navire a un bouton « Rechercher dans e-inspection » qui ouvre cette page déjà filtrée sur le navire. Aucun lien vers le site e-inspection lui-même : le lien externe configuré (`external_links`) vise une inspection précise, pas une recherche de navire |
| Alternatives | lien externe vers la recherche d'e-inspection (il faudrait connaître le format de son adresse) |
| Où | `Modules\Catches\Http\Controllers\CatchDeclarationController`, `modules/Vessels/resources/views/show/catches.blade.php` |
| Tests | `Modules\Catches\Tests\Feature\CatchBreakdownTest` |

## H-6-4

| Champ | Contenu |
|---|---|
| Identifiant | H-6-4 |
| Question | « Navires souvent vus mais jamais contrôlés » (cahier, §8) : à partir de combien d'observations un navire est-il « souvent » vu ? |
| Option retenue | un navire est listé s'il a été vu ou approché au moins 2 fois pendant des missions et qu'aucune de ses observations n'a abouti à un contrôle à bord ; le seuil se règle sur la page (1, 2, 3, 5 ou 10) |
| Alternatives | seuil fixe plus élevé ; seuil sur une période glissante |
| Où | `App\Queries\NeverInspectedVessels` (vue SQL `v_seen_never_inspected`), `Modules\Inspections\Http\Controllers\NeverInspectedController::DEFAULT_THRESHOLD` |
| Tests | `Modules\Inspections\Tests\Feature\NeverInspectedTest` |

## H-6-5

| Champ | Contenu |
|---|---|
| Identifiant | H-6-5 |
| Question | « Déclarations : afficher toutes les informations présentées (voir image). Permettre l'extraction de la liste sur une période, ou de tous les éléments d'un navire » (cahier, §10) : de quelles déclarations s'agit-il, et que montre l'image ? |
| Option retenue | les déclarations ZEE et ERS (les deux seules déclarations de l'application). L'image n'étant pas dans `docs/new/`, « toutes les informations » est pris au sens de tous les champs d'une déclaration : la liste ZEE affiche en plus le franchissement, la destination et le canal de réception, la fiche ERS a une fiche d'en-tête (navire, date, source, nombre de lignes, espèces, poids vif total). L'extraction (CSV ou PDF) se fait par navire, par période, ou les deux, depuis les listes, la fiche du navire et la page Exports ; chaque export est journalisé |
| Alternatives | afficher d'autres déclarations (déclarations de panne, de coupure d'émission) ; reproduire une maquette précise |
| Où | `Modules\Reports\Support\ExportType::EezDeclarations` et `ErsDeclarations`, `ExportDatasets`, permissions `reports.export_eez_declarations` et `reports.export_ers_declarations` |
| Pour changer | modifier les colonnes dans `ExportDatasets` ; accorder les permissions aux rôles voulus (page Rôles) |
| Tests | `Modules\Reports\Tests\Feature\DeclarationExportsTest`, `Modules\Movements\Tests\Feature\DeclarationListsTest` |

## H-6-6

| Champ | Contenu |
|---|---|
| Identifiant | H-6-6 |
| Question | « Annuaire de contacts AMREP pour les notifications dans le cadre de l'AMREP. Prévoir des messages prédéfinis, personnalisables, avec ajout de documents générés par la plateforme (mains courantes, observations, navires, etc.) » (cahier, §10) : qui sont les contacts, quels messages, quels documents ? |
| Option retenue | AMREP est lu comme l'Accord relatif aux mesures du ressort de l'État du port (MREP dans le reste de l'application). L'annuaire réutilise les contacts des référentiels (organisation, catégorie, pays, e-mail, téléphone). Les messages prédéfinis sont trois modèles, en français et en anglais (notification d'entrée au port, inspection, refus d'accès), créés par `AmrepMessageTemplatesSeeder` et gérés comme tous les modèles ; le texte est librement modifiable avant l'envoi. Les documents joignables sont la fiche technique du navire (PDF), la main courante d'une journée (Word) et une observation (PDF), générés à l'envoi. Les e-mails les portent en pièces jointes, pas les messages WhatsApp. Chaque contact reçoit son propre message, tracé dans la liste des messages |
| Alternatives | catégories de contacts imposées par l'accord (État du pavillon, État côtier, ORGP) plutôt que texte libre ; autres documents (infraction, inspection) ; validation avant envoi plutôt qu'envoi immédiat |
| Où | `Modules\Communications\Http\Controllers\DirectoryController`, `Actions\NotifyContacts`, `App\Support\Documents\GeneratedDocuments`, `database/seeders/AmrepMessageTemplatesSeeder.php` |
| Pour changer | modifier les modèles dans la page Modèles ; ajouter un document dans `GeneratedDocuments` |
| Tests | `Modules\Communications\Tests\Feature\ContactDirectoryTest` |

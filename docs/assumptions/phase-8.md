# Hypothèses de la phase 8 (Vue 360° et indicateurs)

Statut de toutes les entrées : provisoire, à confirmer par Gildas.

## H-8-1

| Champ | Contenu |
|---|---|
| Identifiant | H-8-1 |
| Question | A1 : règles du statut à feux d'un navire (conforme, à surveiller, suspect, en infraction) |
| Option retenue | le statut le plus grave parmi les faits observés. En infraction : au moins un dossier d'infraction non clos ni classé. Suspect : licence expirée, suspendue ou révoquée ; balise silencieuse hors avarie déclarée ; écart de déclaration ZEE non expliqué (contrôle en échec) sur 30 jours ; 3 vérifications ou plus sans suite. À surveiller : avarie de balise en cours ; suivi renforcé ouvert ; licence active expirant dans 30 jours ou moins. Sinon conforme. Une licence non renseignée n'influence pas le statut (RG08 : l'enregistrement est facultatif) |
| Alternatives | licence non renseignée = à surveiller ; dossier d'infraction « sanctionné » considéré comme clos ; récidive comptée dans « suspect » ; pondération plutôt que le niveau le plus grave |
| Où | `App\Support\Vessels\StatusLight` (règles), `StatusLightFacts` (faits), `config/vigilance.php` (`status_light.*` : 30 jours d'échéance, 30 jours d'écart ZEE, 3 vérifications), `App\Queries\OpenInfractionCases` (définition d'un dossier ouvert) |
| Pour changer | modifier une valeur de `config/vigilance.php`, ou une règle dans `StatusLight::assess` ; relancer `php artisan vessels:rebuild-state` |
| Tests | `Tests\Feature\Vigilance\StatusLightTest`, `Modules\Vessels\Tests\Feature\Vessel360Test::test_the_header_shows_the_status_light_and_its_reasons` |

## H-8-2

| Champ | Contenu |
|---|---|
| Identifiant | H-8-2 |
| Question | formule du score de vigilance « par règles lisibles et paramétrables » (spécification section 12) |
| Option retenue | somme de neuf composantes, chacune = nombre d'occurrences × points, plafonnée par composante, total plafonné à 100 : silence en cours (20), silences sur 12 mois (5 chacun, 15 max), licence expirée/suspendue/révoquée (20), licence proche de l'échéance (10), infractions ouvertes (15 chacune, 30 max), infractions sur 12 mois (5 chacune, 15 max), vérifications répétées (10), écarts ZEE non expliqués (10 chacun, 20 max), messages sans réponse (3 chacun, 9 max), navire vu mais non inspecté sur 12 mois (10). Niveaux : faible sous 25, modéré de 25 à 49, élevé à partir de 50. Chaque composante est affichée avec ses occurrences et ses points |
| Alternatives | pondération par récence ; score relatif à la flotte ; autres poids |
| Où | `App\Support\Vessels\VigilanceScore`, `VesselIndicators`, poids et seuils dans `config/vigilance.php` (`score.*`) |
| Pour changer | modifier les poids, plafonds et niveaux de `config/vigilance.php` ; une nouvelle composante s'ajoute dans `VigilanceScore::occurrences` et dans les traductions `vigilance.score.components` |
| Tests | `Tests\Feature\Vigilance\VigilanceScoreTest` |

## H-8-3

| Champ | Contenu |
|---|---|
| Identifiant | H-8-3 |
| Question | quels indicateurs la vue flotte du Directeur affiche, et ce que « actif en ZEE » signifie |
| Option retenue | neuf indicateurs : navires actifs dont la dernière position est en zone économique exclusive, balises silencieuses, licences qui expirent (délai d'alerte), infractions en cours, opérations ouvertes ou en cours, missions en cours, et les navires par statut à feux. Chaque nombre est la longueur de la liste qu'il ouvre ; les filtres (période, zone, pavillon, segment, statut à feux, recherche) s'appliquent aux nombres comme aux listes. La période filtre la date propre à l'indicateur (dernière position, échéance, constat, début) |
| Alternatives | navires « en ZEE » d'après les déclarations d'entrée ; indicateurs par équipe ou par agent |
| Où | `Modules\Indicators\Support\IndicatorCatalog` |
| Pour changer | modifier la méthode de l'indicateur dans `IndicatorCatalog` |
| Tests | `Modules\Indicators\Tests\Feature\FleetViewTest`, `IndicatorDetailTest` |

## H-8-4

| Champ | Contenu |
|---|---|
| Identifiant | H-8-4 |
| Question | atterrissage du Directeur et droits de la vue flotte |
| Option retenue | le rôle Directeur arrive sur la vue flotte (`/fleet`) à la connexion et sur `/` ; les autres rôles gardent le tableau de bord. Droit `indicators.view` (Chef d'équipe et Directeur) pour la vue flotte, le détail des indicateurs et l'onglet Indicateurs de la fiche navire ; les autres onglets suivent les droits de leur module (l'onglet Infractions exige `infractions.view`) |
| Alternatives | vue flotte accessible à l'Agent ; atterrissage choisi par chaque utilisateur |
| Où | `App\Support\Navigation\Landing`, `Permission::IndicatorsView`, `VesselController::tabs` |
| Pour changer | modifier `Landing::routeName` ou accorder le droit à un rôle ou groupe |
| Tests | `FleetViewTest::test_directors_land_on_the_fleet_view_and_others_on_the_dashboard`, `Vessel360Test::test_tabs_follow_the_permissions_of_the_reader` |

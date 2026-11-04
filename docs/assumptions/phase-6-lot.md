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

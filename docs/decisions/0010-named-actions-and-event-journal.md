# 0010 — Actions nommées, journal d'événements et état courant

**Statut** : accepté le 2026-10-27 (phase 1, tranche verticale « enregistrer une licence »). À valider par Gildas avec la phase 1.

## Contexte

- La spécification (§2 et §16) demande que chaque module publie un événement dans un journal commun par navire, mette à jour un état courant, et que toute écriture métier soit une « action nommée » (permission, écriture, événement, état courant, audit). La vue 360° ne lira que le journal et l'état courant.
- Avant cette décision, les contrôleurs écrivaient directement et la chronologie d'un navire était reconstituée à l'affichage (`modules/Vessels/Support/VesselTimeline.php`).

## Décision

Le noyau (`app/`) fournit :

- **`App\Support\Actions\Action`** : classe abstraite avec `code()`, `permission()` et `handle(ActionContext, array)`.
- **`ActionRunner`** : exécute une action dans **une seule transaction** : contrôle de la permission (`AuthorizationException` sinon), écriture, enregistrement des événements publiés, rafraîchissement de l'état courant des navires touchés, entrée d'audit (action, entrée, adresse IP, demandeur). Si une étape échoue, rien n'est écrit.
- **`ActionContext::publish(...)`** : une action déclare les faits à inscrire au journal du navire.
- **`vessel_events`** (journal) et **`vessel_current_states`** (état courant), reconstructibles par `php artisan vessels:rebuild-state` (planifiée toutes les heures, parce que l'expiration d'une licence et le silence d'une balise évoluent avec le temps).
- **`module_registry`** : chaque `ModuleServiceProvider` déclare son module ; `php artisan modules:sync` l'enregistre. Un test vérifie qu'un module n'utilise jamais le code d'un autre module.
- **Audit inaltérable** : des triggers PostgreSQL refusent toute modification, suppression ou vidage de `activity_log`, y compris pour l'Admin.

Première action : `modules/Licenses/Actions/RecordLicense.php`, avec la page « Enregistrer une licence » (permission `licenses.manage`).

### Écarts et choix

1. **Résumé traduit à l'affichage** : `vessel_events` stocke `summary_key` et `summary_params` au lieu d'un texte (`summary` dans le schéma v1), pour que chaque lecteur voie le résumé dans sa langue. Le schéma v2 est mis à jour.
2. **État courant limité aux faits définis** : `license_status`, `license_expires_on`, `last_position_at`, `vms_silence_hours`. La licence retenue est celle dont la date de fin est la plus tardive (règle déjà utilisée par la fiche navire). Les colonnes `status_light`, `is_enhanced_monitoring`, `checks_count`, `vms_failure_ongoing` et `open_infractions_count` existent mais gardent leur valeur par défaut : la spécification ne définit pas leurs règles de calcul (statut à feux, « infraction ouverte » selon les statuts actuels), et il ne faut pas les inventer (voir questions ouvertes du plan).
3. **Audit sur `activity_log`** en attendant la table `audit_log` du schéma.
4. **Conversion progressive** : seules les écritures migrées vers une action alimentent le journal. Les autres contrôleurs seront convertis module par module, au fil des phases.

## Conséquences

- Positives : un seul chemin pour écrire, donc des garanties communes (droits, trace, journal) ; atomicité ; chronologie et état courant fiables pour la vue 360° ; un nouvel arrivant a un patron unique (recette 4.9 du guide).
- Négatives : le journal est incomplet tant que tous les modules ne sont pas convertis ; l'état courant est un instantané, d'où la reconstruction horaire ; les triggers d'audit empêchent les purges (`activitylog:clean`), la rétention de 5 ans sera traitée en phase 9.
- Travail induit : convertir les écritures existantes (observations, mouvements, infractions) en actions ; définir avec Gildas le statut à feux et les infractions ouvertes avant la phase 8.

## Alternatives écartées

- **Événements Eloquent ou observers** pour alimenter le journal : implicites, difficiles à tester et à rattacher à un acteur et une permission.
- **Journal écrit par chaque module à sa façon** : chronologies incohérentes, droits et audit à recopier.
- **Calculer le statut à feux maintenant** : règle métier non définie par la spécification.

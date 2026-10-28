# 0011 — Résolveur de permissions, groupes et héritage de rôles

**Statut** : accepté le 2026-10-27 (phase 1, droits flexibles). Réalise l'ADR 0004. À valider par Gildas avec la phase 1.

## Contexte

- La spécification (§3.2 et §3.3) demande des rôles hérités (Directeur ⊃ Chef d'équipe ⊃ Agent), des groupes de permissions attachables à tout utilisateur, un catalogue d'actions par module, et des permissions limitables à une zone ou une période, modifiables sans changer le code. Le schéma v1 prévoit `role`, `groupe`, `action_catalogue`, `permission`, `utilisateur_role`, `utilisateur_groupe`.
- Spatie Permission ne connaît ni groupes, ni héritage, ni portée. L'ADR 0004 a décidé d'introduire le modèle de la spécification derrière les `Gate`.

## Décision

- **Tables** (schéma v2) : `action_catalog`, `permission_groups`, `permission_group_user`, `permission_grants` (un détenteur, rôle **ou** groupe, une action, zone et période facultatives), et `roles.parent_id`. Contraintes de la base : un seul détenteur par droit, pas de doublon de droit permanent.
- **Source unique des droits** : `permission_grants`. Spatie Permission est retiré (2026-10-28) : les rôles vivent dans `roles` et `role_user`, gérés par `RoleRecord` et le trait `HasRoles`.
- **`PermissionResolver`**, branché par `Gate::before` : un utilisateur détient une action s'il l'a, valide aujourd'hui et sans zone, par l'un de ses rôles, de leurs ancêtres ou de ses groupes ; une action limitée à une zone ne s'applique que si l'appelant fournit cette zone (`Gate::allows($action, $zone)`). Les appels `can()`, `->can()` et `@can` du code existant ne changent pas.
- **Mémoire par requête** : les droits d'un utilisateur sont calculés une fois par requête, puis recalculés après tout changement fait par `GrantService`, par le pivot des groupes (`GroupMembership`) ou par un changement de rôle de l'utilisateur (`RoleMembership`).
- **Catalogue** : l'énumération `Permission` reste la déclaration des actions (module et verbe dérivés) ; `permissions:sync` les enregistre en base. Un module qui livre une action l'ajoute à l'énumération.
- **Hiérarchie des rôles système** : `director` hérite de `team_lead`, qui hérite de `agent`, fixée par le seeder. Un rôle personnalisé choisit son parent (sans cycle).
- **Groupes** : les cinq groupes de la spécification sont créés (`fnp_license_management`, `infraction_investigation`, `surveillance_opinion`, `automatic_dispatch`, `trigger_ai_agent`) ; les deux premiers reçoivent les permissions de licences et d'infractions qui existent, les autres attendent leurs actions. L'administrateur crée d'autres groupes.
- **Destinataires des alertes** : `Notifier` s'appuie sur `PermissionResolver::holders()` (rôles, descendants et groupes).

## Conséquences

- Positives : droits modifiables par l'administrateur (rôles, groupes, appartenances) sans toucher au code ; un utilisateur peut recevoir un droit ponctuel par un groupe sans changer de rôle ; héritage cohérent avec la spécification ; portée et période déjà gérées par le résolveur.
- Négatives : trois requêtes par utilisateur et par requête (rôles, groupes, droits) ; le calcul est une approximation volontaire pour les zones (égalité exacte, pas de hiérarchie de zones) .
- Travail induit : hiérarchie de zones avec le module référentiels (phase 2) ; permissions des groupes « avis surveillance », « envois automatiques » et « agent IA » avec les phases 5 et 9.

## Alternatives écartées

- **Droits propres à chaque utilisateur** : absents de la spécification, difficiles à auditer.
- **Garder Spatie pour les rôles et ajouter une seconde table pour les groupes** : deux sources de droits à additionner partout.
- **Cache durable des droits** : invalidation fragile, le calcul par requête reste léger.

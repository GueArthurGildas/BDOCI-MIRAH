# 0005 — Quatre rôles cibles

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (D5). Livré.

## Contexte

- La spécification (§3.1) définit quatre rôles : Directeur, Chef d'équipe, Agent (opérateur CSP et inspecteur) et Admin. L'Admin n'a aucune décision métier (exigence S-004) ; l'inspecteur est un agent titulaire d'une habilitation (table `habilitation`).
- Le code d'origine avait `admin`, `supervisor`, `surveillance_agent` et `inspector`, sans Directeur, et l'`admin` détenait toutes les permissions.

## Décision

Les rôles système sont `admin`, `director`, `team_lead` et `agent`, affichés Administrateur, Directeur, Chef d'équipe et Agent (`App\Support\Authorization\Role`) :

- `supervisor` devient `team_lead`. L'équivalence est expliquée dans la description du rôle : « Équivalent de l'ancien rôle Responsable CSP (superviseur) » ;
- `inspector` est fusionné dans `agent` (un inspecteur est un agent titulaire d'une habilitation, à modéliser avec M1) ; `surveillance_agent` devient `agent` ;
- `director` est créé ;
- l'Admin n'a plus de droit métier : comptes, rôles, paramètres, connecteurs (consultation et gestion), audit, et uniquement la consultation du tableau de bord, qui porte la santé des sources ;
- Directeur et Chef d'équipe ont aujourd'hui les mêmes permissions (tout le métier plus la consultation des connecteurs) ; leur différence apparaîtra avec les actions de décision (suspension, refus de port) en phases 4 et 6.

Les noms de rôles sont des identifiants techniques, donc en anglais (voir 0001).

## Conséquences

- Positives : conforme à S-004 ; les alertes métier ne sont plus envoyées à l'Admin ; seuls les administrateurs reçoivent les notifications de commentaires de connecteurs.
- Négatives : une base déjà peuplée garderait les anciens noms de rôles (sans objet, voir 0006) ; les tests qui utilisaient l'inspecteur comme utilisateur limité utilisent maintenant l'Admin, ou un utilisateur muni d'une seule permission ; `docs/CAS_UTILISATION.md` et `docs/MODELE_DONNEES.md` décrivent encore les anciens rôles (avertissement ajouté, réécriture prévue).
- Travail induit : habilitation d'inspecteur en phase 2 ; permissions de décision du Directeur en phases 4 et 6 ; restreindre le tableau de bord de l'Admin à la santé des sources en phase 9.

## Alternatives écartées

- **Conserver les quatre rôles d'origine** : pas de Directeur, Admin avec droits métier, contraire à S-004.
- **Garder `inspector` comme rôle** : contredit la spécification, où l'inspecteur est un agent habilité.

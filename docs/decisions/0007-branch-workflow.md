# 0007 — Flux de branches

**Statut** : accepté le 2026-10-27, décision de l'utilisateur (D7).

## Contexte

- La spécification demande de travailler sur la branche `g` ; le code courant est sur `y-branch`.
- `g-branch` est la branche de design du collègue ; elle a été fusionnée dans `y-branch` (commit de fusion `df038c6`) et n'a plus de commit propre.
- `main` est la branche de déploiement (aucun déploiement n'a encore eu lieu).
- Le collègue ne travaille que sur le design, puis laisse la main sur la suite.

## Décision

1. Le développement se fait sur `y-branch`.
2. Une fois `y-branch` poussée, le collègue la récupère et la teste.
3. Quand elle est validée, elle est fusionnée dans `main`, la branche de déploiement.
4. `g-branch` est conservée, sans évolution attendue.

Rien n'est poussé ni fusionné dans `main` sans demande explicite de l'utilisateur.

## Conséquences

- Positives : flux simple, une seule branche de travail, pas de conflits entre branches parallèles.
- Négatives : `main` est la branche de déploiement, donc chaque fusion doit être déployable. Avant la première fusion il faudra vérifier : l'image `postgis/postgis:16-3.5` ; les variables d'environnement du serveur (`SESSION_DRIVER` et `CACHE_STORE` à `database`, `QUEUE_CONNECTION` à `sync`, `MAIL_MAILER` à `log`) ; le conteneur `scheduler` actif pour les alertes planifiées ; la reconstruction de la base (voir 0006).
- Si le collègue modifie de nouveau l'ancien fichier `_gesica.scss` sur `g-branch`, la fusion produira des conflits : le thème est maintenant découpé dans `resources/scss/theme/`. L'auteur de la spécification doit être informé que `g-branch` est conservée.

## Alternatives écartées

- **Retirer `g-branch`** : inutile, elle est déjà fusionnée et ne coûte rien.
- **Fusions croisées régulières entre deux branches personnelles** : sans objet, le collègue ne développe plus que sur le design puis repasse la main.

# 0012 — Exécution de phases en parallèle

**Statut** : appliqué le 2026-10-28 sur décision du responsable technique pour livrer une application complète sans attendre les réponses ouvertes ; **à confirmer par Gildas** (il modifie la règle de l'ADR 0008, qui reprend la méthode imposée par la spécification). Les points métier non tranchés suivent le registre `docs/assumptions/`.

## Contexte

- L'ADR 0008 interdit de démarrer une phase sans validation de la précédente. La trajectoire compte dix phases après la phase 1, dont quatre de taille XL ; en séquence, le chemin est long.
- L'analyse des clés étrangères de `docs/schema/schema_v2.sql` (voir le plan, « Exécution en parallèle ») montre que tout dépend de la phase 2 (`vessels`), mais que, la phase 2 faite, les phases 3a, 3b, 4a, 4b, 5 et la partie miroir de la 7 ne se lisent entre elles que par quelques clés étrangères nullables.

## Décision proposée

- Une phase peut démarrer dès que **les phases dont elle dépend** sont validées (graphe du plan), et non plus toutes les précédentes.
- **Chaque phase est validée séparément par Gildas**, avec les mêmes critères de « terminé » (plan, §4). Aucun critère n'est assoupli ; aucune règle métier n'est inventée pour avancer.
- Les clés étrangères vers une table d'une phase non encore livrée sont ajoutées par la phase qui livre la table cible (migration de jonction), la colonne existant déjà sans contrainte.
- Le nombre de flux simultanés est limité à la capacité de relecture et de validation (par défaut : trois).
- Chaque flux travaille dans son propre arbre de travail Git, rebasé sur `y-branch` ; l'intégration dans `y-branch` se fait phase par phase, après validation.

## Conséquences

- Positives : délai global réduit ; les points ouverts bloquants d'une phase n'arrêtent pas les autres.
- Négatives : conflits possibles sur les fichiers partagés (énumération des permissions, traductions, glossaires, référence générée) ; charge de validation concentrée.
- Travail induit : numérotation des migrations par flux, règles d'intégration (plan), génération de la référence après chaque intégration.

## Alternatives écartées

- **Rester strictement séquentiel** : sûr mais long, et bloque des phases sur des questions qui ne les concernent pas.
- **Tout démarrer sans validation** : contraire à la méthode imposée.

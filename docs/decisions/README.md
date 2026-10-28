# Décisions d'architecture (ADR)

Chaque décision structurante du projet est consignée dans un fichier numéroté. Une ADR explique pourquoi un choix a été fait, ce qu'il implique et ce qui a été écarté, pour qu'un nouvel arrivant n'ait pas à le redécouvrir.

Elles reprennent les décisions D1 à D9 du plan de refonte ([PLAN_REFONTE.md](../PLAN_REFONTE.md), section 2).

## Index

| N° | Décision | Statut | Phase concernée |
|---|---|---|---|
| [0001](0001-identifier-language.md) | Langue des identifiants : anglais + glossaire | accepté | 0 puis toutes |
| [0002](0002-database-engine.md) | SGBD : PostgreSQL 16 | accepté | 0 (fait), 3b |
| [0003](0003-geometry-postgis.md) | Géométrie : PostGIS | accepté | 0 (fait), 3b, 6 |
| [0004](0004-flexible-permissions.md) | Droits flexibles derrière les `Gate` | accepté, en cours | 1 |
| [0005](0005-target-roles.md) | Quatre rôles cibles | accepté, livré | 1 |
| [0006](0006-existing-data.md) | Données existantes : repartir des seeders | accepté | toutes |
| [0007](0007-branch-workflow.md) | Flux de branches | accepté | toutes |
| [0008](0008-phase-validation.md) | Validation des phases | recommandation acceptée | toutes |
| [0009](0009-default-thresholds.md) | Seuils par défaut | recommandation acceptée | 3b, 4a |
| [0010](0010-named-actions-and-event-journal.md) | Actions nommées, journal d'événements et état courant | accepté, en cours | 1 |
| [0011](0011-permission-resolver-and-grants.md) | Résolveur de permissions, groupes et héritage de rôles | accepté | 1 |
| [0012](0012-parallel-phases.md) | Exécution de phases en parallèle | appliqué, à confirmer par Gildas | 2 à 9 |

## Format

Un fichier par décision, nommé `NNNN-titre-en-anglais.md`, rédigé en français :

1. **Titre**
2. **Statut** : accepté, remplacé ou abandonné, avec la date et l'auteur de la décision
3. **Contexte** : les faits (code actuel, spécification) qui imposent de décider
4. **Décision** : ce qui est retenu
5. **Conséquences** : effets positifs et négatifs, travail induit, phase concernée
6. **Alternatives écartées**

## Ajouter ou modifier une décision

- Une nouvelle décision prend le numéro suivant ; on ne renumérote jamais.
- Une décision n'est pas réécrite après coup : si elle change, on crée une nouvelle ADR et on passe l'ancienne au statut « remplacé par NNNN ».
- On ajoute la ligne correspondante à l'index ci-dessus.

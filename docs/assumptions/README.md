# Hypothèses provisoires

Quand une question métier reste ouverte (`docs/QUESTIONS_GILDAS.md`, section 6 du plan), la phase retient **l'option la plus raisonnable**, la consigne ici et l'isole pour qu'une réponse de Gildas se traduise par une modification locale. **Aucune hypothèse n'est une règle validée.**

## Une hypothèse, un identifiant

`H-<phase>-<n>` (exemple : `H-3a-2`). Chaque phase tient son fichier `docs/assumptions/<phase>.md` (par exemple `phase-3a.md`) ; ce fichier-ci décrit seulement le format et l'index.

Format d'une entrée :

| Champ | Contenu |
|---|---|
| Identifiant | `H-3a-2` |
| Question | référence de la spécification (E1, V18, nomenclature…) ou point de la phase |
| Option retenue | ce que fait le code aujourd'hui |
| Alternatives | les autres choix plausibles |
| Où | fichiers, clés de configuration ou réglages concernés |
| Pour changer | les étapes exactes quand Gildas répond |
| Tests | classes ou méthodes portant `#[Assumes('H-3a-2')]` |

## Comment isoler une hypothèse

1. **Un paramètre** (seuil, délai, jour, heure, liste de valeurs) : une clé de configuration, modifiable par un réglage de l'Admin quand c'est un paramètre non sensible (compétence `add-setting`). Changer la réponse = changer la valeur.
2. **Une règle** (calcul, condition, choix d'une ligne parmi plusieurs) : une seule classe ou méthode nommée d'après l'hypothèse, appelée partout ailleurs. Changer la réponse = changer ce point.
3. **Une liste fermée** (nomenclature, types, motifs) : une liste de référence (`reference_lists`) alimentée par un seeder, modifiable par l'Admin sans code.
4. **Un test qui fige l'hypothèse** porte `#[Assumes('H-…')]` (`Tests\Attributes\Assumes`). Pour retrouver ce qu'une réponse de Gildas touche : `grep -rn "Assumes('H-3a-2'" tests modules`.
5. Jamais d'hypothèse silencieuse : tout choix non tiré de la spécification a une entrée.

## Quand Gildas répond

1. Trouver l'entrée (index ci-dessous ou `grep`).
2. Appliquer « Pour changer ».
3. Modifier les tests marqués `Assumes`, retirer l'attribut, passer l'entrée à `Statut : confirmée` ou `corrigée` avec la date.
4. Mettre à jour `docs/QUESTIONS_GILDAS.md` (colonne Réponse) et, si la règle devient définitive, l'ADR ou la matrice d'acceptation.

## Index

Alimenté en fin de nuit par l'intégration des phases (un fichier par phase).

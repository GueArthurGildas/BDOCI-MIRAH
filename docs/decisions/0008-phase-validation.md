# 0008 — Validation des phases

**Statut** : recommandation acceptée le 2026-10-27 (D8).

## Contexte

- La spécification impose d'avancer étape par étape, de ne passer à la suivante qu'après validation explicite, et de ne jamais inventer une règle métier : en cas de doute, on consulte la section 18 (points ouverts) et on pose la question.
- La modélisation métier a été validée avec Gildas, auteur de la spécification ; les choix techniques relèvent du développeur du projet.
- Le plan de refonte (§4) définit une validation par Gildas comme dernier critère de « terminé » de chaque phase.

## Décision

- **Gildas** valide le métier : règles de gestion, cas d'usage, vocabulaire, points ouverts de la section 18.
- **L'utilisateur** valide la technique : schéma physique, architecture, qualité du code et des tests.
- Aucune phase ne démarre sans validation de la précédente. La validation est consignée dans le commit ou le message de fin de phase et dans la matrice d'acceptation.
- Tout point de métier absent de la spécification est posé à Gildas avant d'être implémenté.

## Conséquences

- Positives : respect de la méthode imposée ; responsabilités claires ; pas de règle métier inventée.
- Négatives : les phases dépendent de la disponibilité du validateur ; les points ouverts bloquants (E1, V9 à V12, V17, V18, V20, V21, nomenclature des infractions) peuvent retarder certaines phases (voir plan, §6).
- Travail induit : préparer à chaque fin de phase une démonstration et une liste de questions ouvertes.

## Alternatives écartées

- **Enchaîner les phases sans validation** : contraire à la méthode imposée.
- **Un seul validateur pour tout** : mélange métier et technique, retarde les deux.

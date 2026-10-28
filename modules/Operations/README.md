# Operations

Opérations CSP `OPS-num-cat-AAAA-NOM` (module M5 de la spécification) : ouverture, chronologie, instructions, clôture avec résultat.

- Tables : `operation_categories`, `operation_counters`, `operations`, `operation_contexts`, `operation_vessel`, `operation_user`, `operation_events`, `operation_instructions` (modèles dans `app/Models`).
- Actions : `OpenOperation`, `AddOperationEvent`, `GiveOperationInstruction`, `UpdateOperation`, `CloseOperation` (la clôture ferme les lignes de veille de l'opération).
- Permissions : `operations.view`, `operations.create` (Agent compris), `operations.manage` (suivre et clôturer : Chef d'équipe, Directeur).
- Numérotation : `Support/OperationNumbering` (compteur par catégorie et par année, remis à zéro le 1er janvier).
- Hypothèses : H-3a-3, H-3a-7, H-3a-8 (`docs/assumptions/phase-3a.md`).

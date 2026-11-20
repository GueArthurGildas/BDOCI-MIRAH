# Operations

Opérations CSP `OPS-num-cat-AAAA-NOM` (module M5 de la spécification, procédure PROCOP_CSP/01 du manuel) : ouverture, main courante de l'opération, instructions, pièces jointes, clôture avec résultat et PDF figé.

- Tables : `operation_categories`, `operation_counters`, `operations`, `operation_contexts`, `operation_vessel`, `operation_user`, `operation_events`, `operation_instructions` (modèles dans `app/Models`).
- Actions : `OpenOperation` (inscrit l'ouverture en rouge dans la main courante journalière), `AddOperationEvent` (ligne QUAND / COMMENT / QUOI de la main courante de l'opération), `GiveOperationInstruction`, `UpdateOperation`, `AttachOperationDocument`, `CloseOperation` (inscrit la clôture en vert, ferme les lignes de veille, range le PDF figé de la main courante de l'opération dans son dossier).
- Permissions : `operations.view`, `operations.create` (ouvrir, ajouter des lignes et des pièces jointes : Agent compris), `operations.close` (clôturer : Agent compris), `operations.manage` (statut et instructions : Chef d'équipe, Directeur).
- Numérotation : `Support/OperationNumbering` (compteur par catégorie et par année, remis à zéro le 1er janvier).
- Documents imprimés : `Support/OperationDocument` (modèle `Main_courante_OPS_Num_Cat_AAAA_Nom` : cartouche de contexte, main courante de l'opération, cartouche de clôture, liste des pièces jointes), rendu en Word (`operations.word`) et en PDF (`operations.pdf`) par `App\Support\Printing`.
- Hypothèses : H-3a-3, H-3a-7, H-3a-8, H-3a-9 (`docs/assumptions/phase-3a.md`).

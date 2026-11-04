# Profile (profil et espace personnel)

Page `/profile` de l'utilisateur connecté : identité (prénom, nom, fonction, rôle, équipe, contacts), photo de profil, et son espace personnel.

- **Photo** : un document (`documents`) référencé par `users.avatar_document_id`, servi par `profile.avatar` ; l'ancien document est conservé. Sans photo, l'image par défaut de l'application est utilisée.
- **Espace personnel** (`Support/PersonalSpace`) : messages adressés à l'utilisateur ou à son équipe (`InternalNote::addressedTo`), urgences (alertes urgentes et messages d'urgence élevée non lus), alertes classées par `App\Support\Alerts\AlertTriage`, activités en attente (observations de l'utilisateur ou de son équipe, messages à échéance non lus, opérations ouvertes par lui).
- **Prénom** : `users.first_name` (facultatif) ; `name` reste le nom affiché, et `User::fullName()` assemble prénom et nom.
- **Hypothèses** : H-6-1 (classement des alertes) et H-6-2 (ce qui « concerne » un utilisateur), voir `docs/assumptions/phase-6-lot.md`.
- Aucune permission : toute personne connectée voit son propre profil.

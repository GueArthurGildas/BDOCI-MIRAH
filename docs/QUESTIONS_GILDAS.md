# Questions et points à valider avec Gildas

Document à transmettre à Gildas (auteur de la spécification) à la fin de la phase 1. **Aucune règle métier n'a été inventée** : tant qu'une question reste sans réponse, le code applique le comportement indiqué dans la colonne « Aujourd'hui » et le signale dans la documentation. Les propositions sont de l'équipe technique et ne valent que si Gildas les confirme.

Mise à jour : 2026-10-28. Références : `docs/PLAN_REFONTE.md`, `docs/decisions/`.

## A. Validation de la phase 1 (noyau)

À valider : ADR [0010](decisions/0010-named-actions-and-event-journal.md) (actions nommées, journal d'événements, état courant) et [0011](decisions/0011-permission-resolver-and-grants.md) (droits, groupes, héritage, zone et période), ainsi que les écarts au schéma listés au §11 de `docs/schema/CONVENTIONS.md`.

Questions de métier ouvertes par cette phase :

| # | Question | Aujourd'hui | Proposition technique | Réponse |
|---|---|---|---|---|
| A1 | Quelles sont les **règles du statut à feux** d'un navire (conforme, à surveiller, suspect, en infraction) ? | Non calculé : `vessel_current_states` ne porte que les faits définis (statut et échéance de licence, dernière position, silence VMS) | Aucune : la règle doit venir de la spécification | Reçue (collègue, 2026-10-29), partielle : un navire passe à « à vérifier » si un élément est jugé suspect par les équipes ou si sa balise n'émet plus ; le nombre de vérifications avant « suspect » est un paramètre, 3 pour l'instant. Les règles des autres feux restent à préciser | |
| A2 | Quelles infractions comptent comme **« ouvertes »** (pour le statut et la surveillance renforcée) ? | Non défini, rien n'est calculé | Aucune : la règle doit venir de la spécification | Reçue (collègue, 2026-10-29) : toutes les infractions comptent, quel que soit le type ; les types sont une table modifiable en administration ; une infraction est ouverte tant qu'elle n'est pas clôturée. Il demande confirmation de ce sens : voir la question A2b | |
| A3 | Quelle **licence retenir** quand un navire en a plusieurs (en parallèle, ou renouvelée avant échéance) ? | La licence à la date de fin la plus tardive | Garder ce choix | Reçue (collègue, 2026-10-29) : « licence de référence » choisie par un agent ou le Directeur ayant le droit de gérer les licences ; à défaut, la licence valide la plus récente ; chaque changement est tracé dans le journal d'audit | |
| A4 | Les **étiquettes** génériques (§4 de la spécification) n'ont pas de table dans le schéma SQL. Faut-il les créer ? À quels objets s'appliquent-elles ? Libres ou issues d'une liste ? | Non implémentées | Une table `labels` et une table de liaison polymorphe, si l'usage est confirmé | Reçue (collègue, 2026-10-29) : table dédiée, plusieurs étiquettes par navire et plusieurs navires par étiquette ; droits de création à fixer avec l'équipe technique | |
| A5 | Un **champ extensible de type « liste »** doit pointer vers une liste de référence, mais le schéma n'a aucune colonne pour porter ce lien. Ajouter `reference_list_id` à `custom_field_definitions` ? | Les champs de type liste existent sans lien | Ajouter la colonne | |
| A2b | « Clôturée » désigne-t-elle toute décision finale (sanctionnée, classée sans suite, soldée), ou seulement l'état « clôturé » du dossier ? | Un dossier est ouvert tant qu'il n'est ni clos ni classé (H-8-1) | Ouvert = ni clos ni classé, sauf indication contraire | |
| A3b | Qui est « un agent qui a le droit de gérer les licences » : quelle permission, quels rôles ? | Aucune permission dédiée | Permission `licenses.reference` à accorder par l'Admin | |
| A4b | Qui peut créer une étiquette, et qui peut la poser sur un navire ? | Étiquettes non implémentées | Création réservée au Chef d'équipe et au Directeur, pose ouverte à l'Agent | |

## B. Points de la spécification à porter à sa connaissance

Ce sont des informations, pas des questions : elles expliquent des écarts entre la spécification et le code.

| # | Point | Décision prise |
|---|---|---|
| B1 | Le schéma v1 est écrit pour **MySQL 8 et en français** ; l'auteur demandait d'être prévenu si le projet utilisait PostgreSQL. | Le projet est sur PostgreSQL 16 + PostGIS, identifiants en anglais (D1, D2, D3). Le schéma v2 (`docs/schema/schema_v2.sql`) est la traduction complète, les noms français sont conservés dans les commentaires de la base et dans les glossaires. |
| B2 | La spécification demande de travailler sur la **branche `g`**. | Le développement se fait sur `y-branch`, qui contient `g-branch` (design, figée) ; `main` est la branche de déploiement (D7). |
| B3 | Le **statut de licence** est stocké (`licence.statut`, avec `expiree`) alors que l'expiration se déduit de la date. | Le statut d'expiration est calculé à partir de la date de fin ; l'état courant du navire n'en garde qu'une copie reconstructible. |
| B4 | `etat_courant_navire` est dénormalisé. | Maintenu par les actions, avec commande de reconstruction horaire et test de dérive. |
| B5 | Le journal d'audit est `activity_log` (durci par triggers) en attendant la table `audit_log`. | Voir `docs/schema/CONVENTIONS.md` §11. |
| B6 | Seuils par défaut : silence VMS **4 h**, alertes de licence **30/60/90 jours** (la spécification prévaut sur 2 h et 30 jours). | À appliquer aux phases 3b et 4a, modifiables dans Paramètres (D9). |

## C. Questions qui bloquent des phases

Classées par ordre de besoin. Une phase ne peut pas se terminer sans la réponse indiquée ; elle peut généralement démarrer.

| # | Réf. spec | Question | Bloque | Aujourd'hui | Réponse |
|---|---|---|---|---|---|
| C1 | E1 | Nombre d'équipes, jours travaillés, heure de clôture de la main courante | 3a | Phase non commencée | |
| C2 | V18 | Les positions sont-elles disponibles par API pour construire la « photo » d'un créneau de contrôle ? | 3b | Phase non commencée | |
| C3 | V20 | Quelles localités compter hors Abidjan, San Pedro et Sassandra (comptage par zone) ? | 3b | Phase non commencée | |
| C4 | V10 | Fournisseur d'e-mail (SMTP, service tiers) | 5 | Phase non commencée | |
| C5 | V11 | Consentement et modèles de messages WhatsApp | 5 | Phase non commencée | |
| C6 | — | Nomenclature des infractions et des suites donnés | 6 | Phase non commencée | |
| C7 | V21 | Qui autorise les missions, par type de mission | 6 | Phase non commencée | |
| C8 | V9 | Champs exposés par l'API NENO | 7 | Phase non commencée | |
| C9 | V12 | Accord écrit pour le scraping de THEMIS | 7 | Phase non commencée | |
| C10 | V17 | URL directe par navire dans THEMIS | 7 | Phase non commencée | |
| C11 | — | La DPEP expose-t-elle tous les champs par API ? Sinon import contrôlé (F-018) | 7 | Phase non commencée | |
| C12 | E3 | Liste complète des besoins fonctionnels (dont BF06) | périmètre | Phase non commencée | |

## D. Méthode : exécution de plusieurs phases en parallèle

La méthode actuelle (ADR [0008](decisions/0008-phase-validation.md)) interdit de démarrer une phase avant la validation de la précédente. Une fois la phase 2 validée, cinq phases n'ont plus de dépendance de données entre elles (section « Exécution en parallèle » du plan, calculée depuis les clés étrangères du schéma). L'ADR [0012](decisions/0012-parallel-phases.md), proposé, prévoit de démarrer une phase dès que **les phases dont elle dépend** sont validées, **chaque phase restant validée par Gildas individuellement et dans le même format**.

| # | Question | Réponse |
|---|---|---|
| D1 | Acceptez-vous que plusieurs phases indépendantes soient en cours en même temps, chacune validée séparément ? | |
| D2 | Pouvez-vous valider plusieurs phases dans une même période (disponibilité) ? Sinon, le nombre de flux simultanés est limité à ce que vous pouvez relire. | |

## E. Hypothèses retenues en attendant vos réponses

Pour livrer une application complète, 74 hypothèses provisoires ont été retenues (`docs/assumptions/`, index dans le README). Chacune a une fiche (question, option retenue, alternatives, où la changer, tests concernés) : une réponse de votre part se traduit par une modification à un seul endroit. Les plus structurantes à confirmer en premier :

| # | Hypothèse | Sujet |
|---|---|---|
| E1 | H-8-1, H-8-2 | règles du statut à feux et formule du score de vigilance (questions A1 et A2) |
| E2 | H-4a-1 | licence retenue quand un navire en a plusieurs (question A3) |
| E3 | H-3a-1 | équipes, jours travaillés, heure de clôture (E1 de la spécification) |
| E4 | H-6-3, H-6-2 | nomenclature des infractions et autorisation des missions (V21) |
| E5 | H-7-1 | accès aux sources externes : seul le jeu de données enregistré est branché |
| E6 | H-5-1, H-5-2 | fournisseur d'e-mail et consentement WhatsApp (V10, V11) |
| E7 | H-3b-1, H-3b-2 | positions pour la « photo » et localités à compter (V18, V20) |

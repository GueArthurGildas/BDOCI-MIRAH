# Glossaire

Vocabulaire du projet : termes métier, rôles, noms des tables de la spécification (`database/docs/`) et leur nom technique en anglais (décision D1, voir `docs/decisions/0001-identifier-language.md`). Les noms de colonnes sont dans `docs/schema/GLOSSAIRE_COLONNES.md`, les règles de nommage dans `docs/schema/CONVENTIONS.md`.

Un test (`tests/Feature/GlossaryTest.php`) vérifie que chaque table de l'application figure dans ce glossaire.

## 1. Vocabulaire métier

| Terme | Définition |
|---|---|
| CSP | Centre de Surveillance des Pêches (MIRAH, Côte d'Ivoire) : l'utilisateur de l'application. |
| MIRAH | Ministère de tutelle du CSP. |
| VMS | Suivi des navires par balise satellite ; les positions et alertes viennent de THEMIS. |
| AIS, Sat-AIS | Identification automatique des navires, par radio ou par satellite. |
| ERS | Déclarations électroniques de captures (navires européens) ; l'écart avec le constat d'inspection est comparé à une tolérance (20 % ORGP, 10 % UE). |
| ZEE (EEZ) | Zone économique exclusive. Un navire étranger déclare son entrée et sa sortie. |
| FNP | Registre dans lequel un navire est inscrit ou radié (`fnp_statuses`). |
| THEMIS | Plateforme VMS (CLS) : positions, alertes, liste des navires à surveiller du Directeur. Lue par API, import ou scraping. |
| NENO | Source des vedettes (API). |
| e-inspection | Application d'inspection lue en sens unique : inspections, captures déclarées et constatées, débarquements, transbordements. |
| MREP, DPEP | Autre application, lue en lecture seule : DPEP (déclaration préalable, délai de 72 h), décision d'entrée au port, escales, notifications, refus GIES, ciblage. |
| GIES | Fichier mondial de la FAO ; le refus GIES reste saisi à la main sur le site FAO. |
| ORGP (RFMO), ORP (RFB) | Organisations régionales de gestion des pêches et organisations régionales des pêches (`institutions`). |
| INN (IUU) | Pêche illicite, non déclarée et non réglementée ; listes INN rattachées aux navires. |
| Vedette | Navire géré dans NENO, catégorie `patrol_vessel` des navires. |
| Fiche unique | Un navire = une seule fiche, quelle que soit la voie d'entrée (saisie, THEMIS, NENO, e-inspection, DPEP). |
| Rapprochement | Comparaison d'une arrivée avec les fiches existantes : IMO, MMSI, IRCS, immatriculation, puis nom + pavillon. Un doute envoie la fiche dans la file « doublons possibles ». |
| Événement du navire (`vessel_events`) | Fait unique publié par chaque module dans le journal commun ; la vue 360° ne lit que lui et l'état courant. |
| État courant (`vessel_current_states`) | Résumé calculé de la situation d'un navire : licence, avarie, suivi renforcé, infractions ouvertes, silence VMS, statut à feux. |
| Action nommée | Toute écriture métier : vérifie la permission, écrit, publie l'événement, met à jour l'état courant, journalise l'audit. |
| Droits flexibles | Rôles, groupes et catalogue d'actions par module, au besoin limités à une zone ou une période. |
| Main courante (`logbook_*`) | Journal de quart : chaque fait reçu devient une ligne QUAND / COMMENT / QUOI. Clôturée en fin de quart, copie PDF figée, correction par ligne rectificative. |
| Quart (shift) | Journée de travail de 08h à 18h (paramétrable), assurée par une équipe en rotation. |
| Relais (`relays`) | Transmission des observations à l'équipe suivante et au Chef d'équipe, avec accusé de lecture. |
| Opération | Dossier ouvert pour un fait complexe. Code `OPS-num-cat-AAAA-NOM`, catégories VMS, ERS, MEP, INN, DIV ; compteurs remis à zéro le 1er janvier. |
| Veille (`watch_entries`) et tableau du matin | Navires à surveiller affichés à l'ouverture : liste du Directeur, navires cités en main courante, entrées/sorties de ZEE, suivi renforcé ou plus de 3 vérifications. |
| Suivi renforcé | Fusion de « navire d'intérêt CSP » et « navires suspects ». |
| Créneau de contrôle et photo (`control_slots`, `slot_positions`) | Chargement des positions THEMIS à chaque créneau, puis trois tests assistés (émission, vitesse/activité, trajectoire). |
| Unité rencontrée (`observed_units`) | Un enregistrement, trois niveaux : vue, approchée, contrôlée (ce dernier exige le lien vers e-inspection). |
| Voyant (indicator) | État d'un connecteur : vert, orange, rouge ; plus l'indicateur de données périmées. |
| Seuil (`threshold_parameters`) | Paramètre réglable : écart de captures, silence VMS (4 h), vérifications avant « suspect » (3), plafond d'envois par jour, horaires de quart. |
| RG01 à RG10, UC01 à UC29 | Règles de gestion et cas d'usage de la spécification ; matrice de traçabilité dans `docs/ACCEPTATION.md`. |

## 2. Rôles

| Nom technique | Libellé | Remarque |
|---|---|---|
| `admin` | Administrateur | comptes, rôles, paramètres, connecteurs, audit ; aucune décision métier |
| `director` | Directeur | voit tout, décisions finales |
| `team_lead` | Chef d'équipe | ancien `supervisor` (« Responsable CSP ») |
| `agent` | Agent | opérateur CSP ou inspecteur ; ancien `surveillance_agent` et `inspector` |

## 3. Tables de la spécification

Nom de la spécification (v1, français) → nom technique (v2, anglais) → situation dans l'application actuelle. Les 175 tables sont regroupées par module.

### Noyau

| Spécification | Table | Situation actuelle |
|---|---|---|
| `module_registre` | `module_registry` | créé (phase 1) |
| `pays` | `countries` | créé (phase 2), avec `iso2_code` |
| `zone` | `zones` | existe (géométrie JSON vers PostGIS) |
| `equipe` | `teams` | existe |
| `role` | `roles` | créée (phase 1), avec `parent_id` et `description` |
| `groupe` | `permission_groups` | créé (phase 1) |
| `utilisateur` | `users` | existe, à étendre (type, fonction, téléphone) |
| `utilisateur_role` | `role_user` | créée (phase 1) |
| `utilisateur_groupe` | `permission_group_user` | créé (phase 1) |
| `utilisateur_equipe` | `team_user` | nouveau |
| `action_catalogue` | `action_catalog` | créé (phase 1) |
| `permission` | `permission_grants` | créé (phase 1) |
| `journal_audit` | `audit_log` | remplace `activity_log` (durci) |
| `document` | `documents` | nouveau |
| `document_lien` | `document_links` | nouveau |
| `commentaire` | `comments` | a absorbé `data_source_comments` |
| `liste_reference` | `reference_lists` | nouveau |
| `liste_valeur` | `reference_list_values` | nouveau |
| `champ_definition` | `custom_field_definitions` | nouveau |
| `champ_valeur` | `custom_field_values` | nouveau |
| `parametre_seuil` | `threshold_parameters` | absorbe `settings` |

### M1 Référentiels et navires

| Spécification | Table | Situation actuelle |
|---|---|---|
| `organisme` | `institutions` | créé (phase 2) |
| `port` | `ports` | étendu (phase 2) : `code` et `is_designated` |
| `personne_morale` | `legal_entities` | créé (phase 2) |
| `personne` | `people` | créé (phase 2) |
| `navire` | `vessels` | étendu (phase 2) : `flag` et `owner` deviennent des relations, colonnes de la fiche complète ajoutées |
| `navire_identifiant` | `vessel_identifiers` | créé (phase 2) |
| `navire_historique` | `vessel_history` | créé (phase 2) |
| `navire_source` | `vessel_sources` | créé (phase 2) |
| `navire_champ_provenance` | `vessel_field_provenance` | créé (phase 2) |
| `source_priorite` | `source_priorities` | créé (phase 2) |
| `navire_acteur` | `vessel_actors` | créé (phase 2) |
| `regle_de_zone` | `zone_rules` | créé (phase 2) |
| `espece` | `species` | existe, code FAO élargi à 10 caractères (phase 2) |
| `engin` | `gears` | créé (phase 2) |
| `presentation` | `presentations` | créé (phase 2) |
| `type_navire` | `vessel_types` | créé (phase 2) |
| `liste_externe` | `external_lists` | créé (phase 2) |
| `navire_liste_externe` | `external_list_vessel` | créé (phase 2) |
| `contact` | `contacts` | créé (phase 2) |
| `agent` | `agents` | créé (phase 2) |
| `habilitation` | `accreditations` | créé (phase 2) |
| `evenement_navire` | `vessel_events` | créé (phase 1) |
| `etat_courant_navire` | `vessel_current_states` | créé (phase 1) |
| `etat_connecteur` | `data_sources` | livré (phase 7) : `code`, `mode`, `indicator_status`, `last_error`, `is_stale` (voir CONVENTIONS, point 9 et section 11) |
| `journal_synchro` | `data_source_syncs` | existe |
| `rejet_import` | `data_source_rejections` | existe |
| `lien_externe` | `external_links` | livré (phase 7) |

### M2 Suivi des licences

| Spécification | Table | Situation actuelle |
|---|---|---|
| `licence` | `licenses` | étendue (phase 4a) |
| `licence_zone` | `license_zone` | livrée (phase 4a) |
| `licence_engin` | `gear_license` | livrée (phase 4a) |
| `licence_espece` | `license_species` | livrée (phase 4a) |
| `licence_condition` | `license_conditions` | livrée (phase 4a) |
| `licence_document` | `license_documents` | livrée (phase 4a) |
| `statut_fnp` | `fnp_statuses` | livrée (phase 4a) |
| `suspension_licence` | `license_suspensions` | livrée (phase 4a) |
| `revocation` | `license_revocations` | livrée (phase 4a) |
| `certificat_compatibilite_vms` | `vms_compatibility_certificates` | livrée (phase 4a) |
| `autorisation_externe` | `external_authorizations` | livrée (phase 4a) |
| `import_licences` | `license_imports` | livrée (phase 4a) |
| `import_licences_ligne` | `license_import_lines` | livrée (phase 4a) |
| `registre_licences` | `license_registers` | livrée (phase 4a) |
| `publication_liste_autorises` | `authorized_list_publications` | livrée (phase 4a) |

### M3 VMS, AIS, ERS

| Spécification | Table | Situation actuelle |
|---|---|---|
| `balise_vms` | `beacons` | remplace `tracking_devices` |
| `balise_affectation` | `beacon_assignments` | nouveau |
| `installateur_agree` | `approved_installers` | nouveau |
| `demande_evaluation_vms` | `vms_assessment_requests` | nouveau |
| `demande_nouvelle_balise` | `new_beacon_requests` | nouveau |
| `evaluation_compatibilite` | `compatibility_assessments` | nouveau |
| `installation` | `beacon_installations` | nouveau |
| `obligation_declarative_navire` | `vessel_reporting_obligations` | nouveau |
| `position` | `positions` | existe, à partitionner |
| `position_jour` | `position_daily_summaries` | nouveau |
| `creneau_controle` | `control_slots` | nouveau |
| `position_creneau` | `slot_positions` | nouveau |
| `trace` | `tracks` | nouveau |
| `capture_ecran` | `screen_captures` | nouveau |
| `anomalie_identite` | `identity_anomalies` | nouveau |
| `coupure_emission` | `emission_cutoffs` | nouveau |
| `regle_alerte` | `alert_rules` | nouveau |
| `alerte` | `alerts` | nouveau |
| `traitement_alerte` | `alert_handlings` | nouveau |
| `avarie_vms` | `vms_failures` | nouveau |
| `autorisation_coupure_vms` | `vms_cutoff_authorizations` | nouveau |
| `rapport_position_manuel` | `manual_position_reports` | nouveau |
| `attestation_bon_fonctionnement` | `working_order_attestations` | nouveau |
| `ordre_cessation` | `cessation_orders` | nouveau |
| `balayage` | `sweeps` | nouveau |
| `anomalie_balayage` | `sweep_anomalies` | nouveau |
| `agregation_mensuelle` | `monthly_aggregates` | nouveau |
| `declaration_ers` | `ers_declarations` | nouveau |
| `ers_ligne_capture` | `ers_catch_lines` | nouveau |

### M4 Déclarations d'entrée et de sortie de la ZEE

| Spécification | Table | Situation actuelle |
|---|---|---|
| `declaration_zee` | `eez_declarations` | remplace `movements` (changement de sens) ; phase 4b livrée, `movements` reste pour les mouvements portuaires en attendant `mrep_port_calls` (phase 7) |
| `declaration_zee_capture` | `eez_declaration_catches` | livrée (phase 4b) |
| `lien_entree_sortie` | `entry_exit_links` | livrée (phase 4b) |
| `verification_zee` | `eez_declaration_checks` | livrée (phase 4b) |
| `non_declaration` | `missed_declarations` | livrée (phase 4b) |
| `alerte_entree_sortie` | `entry_exit_alerts` | livrée (phase 4b), lue par la veille (phase 3a) |

### M5 Opérations, main courante, surveillance de quart

| Spécification | Table | Situation actuelle |
|---|---|---|
| `categorie_operation` | `operation_categories` | créé (phase 3a) |
| `compteur_operation` | `operation_counters` | créé (phase 3a) |
| `operation` | `operations` | créé (phase 3a) |
| `operation_contexte` | `operation_contexts` | créé (phase 3a) |
| `operation_navire` | `operation_vessel` | créé (phase 3a) |
| `operation_personnel` | `operation_user` | créé (phase 3a) |
| `operation_evenement` | `operation_events` | créé (phase 3a) |
| `operation_instruction` | `operation_instructions` | créé (phase 3a) |
| `main_courante_jour` | `logbook_days` | créé (phase 3a) |
| `cloture_mc` | `logbook_closings` | créé (phase 3a) |
| `mc_evenement` | `logbook_entries` | créé (phase 3a) |
| `mc_evenement_navire` | `logbook_entry_vessel` | créé (phase 3a) |
| `rotation` | `rotations` | créé (phase 3a) |
| `releve_quart` | `shift_handovers` | créé (phase 3a) |
| `observation_surveillance` | `surveillance_observations` | créée (phase 3a) ; reflète l'ancienne `observations`, conservée |
| `relais` | `relays` | créé (phase 3a) |
| `consigne_permanente` | `standing_instructions` | créé (phase 3a) |
| `comptage_zone` | `zone_counts` | créé (phase 3a) |
| `regle_coherence` | `consistency_rules` | créé (phase 3a) |
| `compteur_verification` | `check_counters` | créé (phase 3a) |
| `veille_navire` | `watch_entries` | créé (phase 3a) |

### M6 MREP (miroir en lecture seule)

| Spécification | Table | Situation actuelle |
|---|---|---|
| `mrep_dpep` | `mrep_dpeps` | livré (phase 7) |
| `mrep_decision_entree` | `mrep_entry_decisions` | livré (phase 7) |
| `mrep_escale` | `mrep_port_calls` | livré (phase 7) |
| `mrep_notification` | `mrep_notifications` | livré (phase 7) |
| `mrep_refus_gies` | `mrep_gies_refusals` | livré (phase 7) |
| `mrep_ciblage` | `mrep_targetings` | livré (phase 7) |

### M11 e-inspection (miroir en lecture seule)

| Spécification | Table | Situation actuelle |
|---|---|---|
| `inspection_externe` | `external_inspections` | livré (phase 7) ; remplace `inspections` (supprimée en phase 6, `infraction_cases.inspection_id` pointe ici) |
| `inspection_document` | `inspection_documents` | livré (phase 7) |
| `inspection_engin` | `inspection_gears` | livré (phase 7) |
| `capture_declaree_constatee` | `declared_observed_catches` | livré (phase 7) ; `catch_declarations` reste en place pour la page des captures (phase 3a/8) |
| `inspection_infraction` | `inspection_infractions` | livré (phase 7) |
| `debarquement` | `landings` | livré (phase 7) |
| `transbordement` | `transshipments` | livré (phase 7) |

### M7 Surveillance participative

| Spécification | Table | Situation actuelle |
|---|---|---|
| `temoin` | `witnesses` | livré (phase 6) |
| `signalement` | `witness_reports` | livré (phase 6) |
| `signalement_navire` | `reported_vessels` | livré (phase 6) |
| `exploitabilite` | `report_usabilities` | livré (phase 6) |
| `enquete` | `report_investigations` | livré (phase 6) |
| `decision_signalement` | `report_decisions` | livré (phase 6) |
| `diffusion_liste_autorises` | `authorized_list_distributions` | livré (phase 6, module Witnesses) |

### M8 Infractions et suites

| Spécification | Table | Situation actuelle |
|---|---|---|
| `referentiel_infraction` | `infraction_types` | étendu (phase 6 : gravité, sanction encourue, actif ; `name` devient `label`) |
| `dossier_infraction` | `infraction_cases` | livré (phase 6) ; remplace `infractions` (données reprises, ajout de `facts`) |
| `infraction_retenue` | `upheld_infractions` | livré (phase 6) |
| `preuve` | `evidence_items` | livré (phase 6) |
| `scelle` | `seals` | livré (phase 6) |
| `mesure_conservatoire` | `precautionary_measures` | livré (phase 6) |
| `saisie` | `seizures` | livré (phase 6) |
| `pv` | `violation_reports` | livré (phase 6) |
| `suite_contentieux` | `litigation_follow_ups` | livré (phase 6) |
| `decision` | `decisions` | livré (phase 6) |
| `paiement` | `payments` | livré (phase 6) |
| `recours` | `appeals` | livré (phase 6) |

### M9 Missions et moyens

| Spécification | Table | Situation actuelle |
|---|---|---|
| `mission` | `missions` | livré (phase 6) ; l'ancienne table est reprise puis supprimée |
| `mission_autorisation` | `mission_authorizations` | livré (phase 6) |
| `mission_participant` | `mission_participants` | livré (phase 6) ; remplace `mission_user` |
| `observateur` | `observers` | livré (phase 6) |
| `embarquement_observateur` | `observer_boardings` | livré (phase 6) |
| `moyen` | `assets` | livré (phase 6) |
| `mission_moyen` | `mission_assets` | livré (phase 6) |
| `mission_briefing` | `mission_briefings` | livré (phase 6) |
| `mission_preparation` | `mission_preparations` | livré (phase 6) |
| `trace_patrouille` | `patrol_tracks` | livré (phase 6) |
| `mission_unite_observee` | `observed_units` | livré (phase 6) ; remplace `mission_vessel` |
| `mission_evenement` | `mission_events` | livré (phase 6) |
| `mission_debriefing` | `mission_debriefings` | livré (phase 6) |
| `mission_bilan` | `mission_reviews` | livré (phase 6) |

### M12 Communications et alertes

| Spécification | Table | Situation actuelle |
|---|---|---|
| `canal_communication` | `communication_channels` | créé (phase 5) |
| `connecteur_email` | `email_connectors` | créé (phase 5) |
| `message_modele` | `message_templates` | créé (phase 5) |
| `regle_envoi` | `dispatch_rules` | créé (phase 5) |
| `message_envoye` | `sent_messages` | créé (phase 5) |
| `agent_ia_action` | `ai_agent_actions` | créé (phase 5) |
| `note_interne` | `internal_notes` | créé (phase 5) |

### M10 Pilotage, rapports, archivage

| Spécification | Table | Situation actuelle |
|---|---|---|
| `rapport_programme` | `scheduled_reports` | créé (phase 9) |
| `export_journal` | `export_log` | créé (phase 9) |
| `archive_operation` | `operation_archives` | créé (phase 9) |

## 4. Tables actuelles vers la cible

| Table actuelle | Devient | Remarque |
|---|---|---|
| `activity_log` | `audit_log` | durci : journal en ajout seul |
| `catch_declarations` | `declared_observed_catches, ers_catch_lines` | absorbée |
| `data_source_comments` | `comments` | repliée dans la table générique (objet rattaché = le connecteur, colonne `kind` conservée) |
| `data_source_errors` | `(propre à l'application)` | suivi humain des erreurs de connecteur, conservé |
| `data_source_rejections` | `data_source_rejections` | alignée |
| `data_source_syncs` | `data_source_syncs` | alignée |
| `data_sources` | `data_sources` | alignée (voir CONVENTIONS, point 9) |
| `infraction_types` | `infraction_types` | alignée |
| `infractions` | `infraction_cases, upheld_infractions, evidence_items, ...` | remplacée |
| `inspections` | `external_inspections, inspection_documents, ...` | remplacée par un miroir en lecture seule |
| `licenses` | `licenses` | étendue |
| `mission_user` | `mission_participants` | remplacée |
| `mission_vessel` | `observed_units` | remplacée |
| `missions` | `missions` | étendue |
| `movements` | `eez_declarations, mrep_port_calls` | change de sens |
| `notifications` | `(propre à l'application)` | canal in_app du module Communications, conservée |
| `observations` | `surveillance_observations, logbook_entries` | remplacée |
| `ports` | `ports` | étendue (code, port désigné) |
| `positions` | `positions` | à partitionner par mois |
| `roles` | `roles` | étendue |
| `settings` | `threshold_parameters` | absorbée |
| `species` | `species` | alignée (noms de colonnes conservés, voir CONVENTIONS §11) |
| `teams` | `teams` | alignée |
| `tracking_devices` | `beacons, beacon_assignments` | remplacée |
| `users` | `users` | étendue |
| `vessels` | `vessels` | étendue (phase 2) |
| `zones` | `zones` | géométrie vers PostGIS |

Tables du framework (hors périmètre de ce glossaire) : `migrations`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`.

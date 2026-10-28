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
| `pays` | `countries` | nouveau |
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
| `organisme` | `institutions` | nouveau |
| `port` | `ports` | existe |
| `personne_morale` | `legal_entities` | nouveau |
| `personne` | `people` | nouveau |
| `navire` | `vessels` | existe, à étendre (`owner` et `flag` deviennent des relations) |
| `navire_identifiant` | `vessel_identifiers` | nouveau |
| `navire_historique` | `vessel_history` | nouveau |
| `navire_source` | `vessel_sources` | nouveau |
| `navire_champ_provenance` | `vessel_field_provenance` | nouveau |
| `source_priorite` | `source_priorities` | nouveau |
| `navire_acteur` | `vessel_actors` | nouveau |
| `regle_de_zone` | `zone_rules` | nouveau |
| `espece` | `species` | existe |
| `engin` | `gears` | nouveau |
| `presentation` | `presentations` | nouveau |
| `type_navire` | `vessel_types` | nouveau |
| `liste_externe` | `external_lists` | nouveau |
| `navire_liste_externe` | `external_list_vessel` | nouveau |
| `contact` | `contacts` | nouveau |
| `agent` | `agents` | nouveau |
| `habilitation` | `accreditations` | nouveau |
| `evenement_navire` | `vessel_events` | créé (phase 1) |
| `etat_courant_navire` | `vessel_current_states` | créé (phase 1) |
| `etat_connecteur` | `data_sources` | existe (voir CONVENTIONS, point 9) |
| `journal_synchro` | `data_source_syncs` | existe |
| `rejet_import` | `data_source_rejections` | existe |
| `lien_externe` | `external_links` | nouveau |

### M2 Suivi des licences

| Spécification | Table | Situation actuelle |
|---|---|---|
| `licence` | `licenses` | existe, à étendre |
| `licence_zone` | `license_zone` | nouveau |
| `licence_engin` | `gear_license` | nouveau |
| `licence_espece` | `license_species` | nouveau |
| `licence_condition` | `license_conditions` | nouveau |
| `licence_document` | `license_documents` | nouveau |
| `statut_fnp` | `fnp_statuses` | nouveau |
| `suspension_licence` | `license_suspensions` | nouveau |
| `revocation` | `license_revocations` | nouveau |
| `certificat_compatibilite_vms` | `vms_compatibility_certificates` | nouveau |
| `autorisation_externe` | `external_authorizations` | nouveau |
| `import_licences` | `license_imports` | nouveau |
| `import_licences_ligne` | `license_import_lines` | nouveau |
| `registre_licences` | `license_registers` | nouveau |
| `publication_liste_autorises` | `authorized_list_publications` | nouveau |

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
| `categorie_operation` | `operation_categories` | nouveau |
| `compteur_operation` | `operation_counters` | nouveau |
| `operation` | `operations` | nouveau |
| `operation_contexte` | `operation_contexts` | nouveau |
| `operation_navire` | `operation_vessel` | nouveau |
| `operation_personnel` | `operation_user` | nouveau |
| `operation_evenement` | `operation_events` | nouveau |
| `operation_instruction` | `operation_instructions` | nouveau |
| `main_courante_jour` | `logbook_days` | nouveau |
| `cloture_mc` | `logbook_closings` | nouveau |
| `mc_evenement` | `logbook_entries` | nouveau |
| `mc_evenement_navire` | `logbook_entry_vessel` | nouveau |
| `rotation` | `rotations` | nouveau |
| `releve_quart` | `shift_handovers` | nouveau |
| `observation_surveillance` | `surveillance_observations` | remplace `observations` |
| `relais` | `relays` | nouveau |
| `consigne_permanente` | `standing_instructions` | nouveau |
| `comptage_zone` | `zone_counts` | nouveau |
| `regle_coherence` | `consistency_rules` | nouveau |
| `compteur_verification` | `check_counters` | nouveau |
| `veille_navire` | `watch_entries` | nouveau |

### M6 MREP (miroir en lecture seule)

| Spécification | Table | Situation actuelle |
|---|---|---|
| `mrep_dpep` | `mrep_dpeps` | nouveau |
| `mrep_decision_entree` | `mrep_entry_decisions` | nouveau |
| `mrep_escale` | `mrep_port_calls` | nouveau |
| `mrep_notification` | `mrep_notifications` | nouveau |
| `mrep_refus_gies` | `mrep_gies_refusals` | nouveau |
| `mrep_ciblage` | `mrep_targetings` | nouveau |

### M11 e-inspection (miroir en lecture seule)

| Spécification | Table | Situation actuelle |
|---|---|---|
| `inspection_externe` | `external_inspections` | remplace `inspections` |
| `inspection_document` | `inspection_documents` | nouveau |
| `inspection_engin` | `inspection_gears` | nouveau |
| `capture_declaree_constatee` | `declared_observed_catches` | absorbe `catch_declarations` |
| `inspection_infraction` | `inspection_infractions` | nouveau |
| `debarquement` | `landings` | nouveau |
| `transbordement` | `transshipments` | nouveau |

### M7 Surveillance participative

| Spécification | Table | Situation actuelle |
|---|---|---|
| `temoin` | `witnesses` | nouveau |
| `signalement` | `witness_reports` | nouveau |
| `signalement_navire` | `reported_vessels` | nouveau |
| `exploitabilite` | `report_usabilities` | nouveau |
| `enquete` | `report_investigations` | nouveau |
| `decision_signalement` | `report_decisions` | nouveau |
| `diffusion_liste_autorises` | `authorized_list_distributions` | nouveau |

### M8 Infractions et suites

| Spécification | Table | Situation actuelle |
|---|---|---|
| `referentiel_infraction` | `infraction_types` | existe |
| `dossier_infraction` | `infraction_cases` | remplace `infractions` |
| `infraction_retenue` | `upheld_infractions` | nouveau |
| `preuve` | `evidence_items` | nouveau |
| `scelle` | `seals` | nouveau |
| `mesure_conservatoire` | `precautionary_measures` | nouveau |
| `saisie` | `seizures` | nouveau |
| `pv` | `violation_reports` | nouveau |
| `suite_contentieux` | `litigation_follow_ups` | nouveau |
| `decision` | `decisions` | nouveau |
| `paiement` | `payments` | nouveau |
| `recours` | `appeals` | nouveau |

### M9 Missions et moyens

| Spécification | Table | Situation actuelle |
|---|---|---|
| `mission` | `missions` | existe, à étendre |
| `mission_autorisation` | `mission_authorizations` | nouveau |
| `mission_participant` | `mission_participants` | remplace `mission_user` |
| `observateur` | `observers` | nouveau |
| `embarquement_observateur` | `observer_boardings` | nouveau |
| `moyen` | `assets` | nouveau |
| `mission_moyen` | `mission_assets` | nouveau |
| `mission_briefing` | `mission_briefings` | nouveau |
| `mission_preparation` | `mission_preparations` | nouveau |
| `trace_patrouille` | `patrol_tracks` | nouveau |
| `mission_unite_observee` | `observed_units` | remplace `mission_vessel` |
| `mission_evenement` | `mission_events` | nouveau |
| `mission_debriefing` | `mission_debriefings` | nouveau |
| `mission_bilan` | `mission_reviews` | nouveau |

### M12 Communications et alertes

| Spécification | Table | Situation actuelle |
|---|---|---|
| `canal_communication` | `communication_channels` | nouveau |
| `connecteur_email` | `email_connectors` | nouveau |
| `message_modele` | `message_templates` | nouveau |
| `regle_envoi` | `dispatch_rules` | nouveau |
| `message_envoye` | `sent_messages` | nouveau |
| `agent_ia_action` | `ai_agent_actions` | nouveau |
| `note_interne` | `internal_notes` | nouveau |

### M10 Pilotage, rapports, archivage

| Spécification | Table | Situation actuelle |
|---|---|---|
| `rapport_programme` | `scheduled_reports` | nouveau |
| `export_journal` | `export_log` | nouveau |
| `archive_operation` | `operation_archives` | nouveau |

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
| `ports` | `ports` | alignée |
| `positions` | `positions` | à partitionner par mois |
| `roles` | `roles` | étendue |
| `settings` | `threshold_parameters` | absorbée |
| `species` | `species` | alignée |
| `teams` | `teams` | alignée |
| `tracking_devices` | `beacons, beacon_assignments` | remplacée |
| `users` | `users` | étendue |
| `vessels` | `vessels` | étendue |
| `zones` | `zones` | géométrie vers PostGIS |

Tables du framework (hors périmètre de ce glossaire) : `migrations`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`.

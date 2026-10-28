# Hypothèses de la phase 4a (M2 Licences)

Aucune de ces hypothèses n'est une règle validée. Pour retrouver les tests qui les figent : `grep -rn "Assumes('H-4a-" modules tests`. Statut de toutes les entrées : **provisoire**.

## H-4a-1 : Quelle licence retenir quand un navire en a plusieurs (point ouvert du plan)

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-1` |
| Question | Quelle licence retenir quand un navire en a plusieurs (point ouvert du plan) |
| Option retenue | La licence courante est celle dont la date d'expiration est la plus tardive, quel que soit son statut. La liste des navires autorisés applique la même règle parmi les licences autorisées |
| Alternatives | La licence active la plus récente ; la licence valide avant les autres ; la dernière émise |
| Où | `App\Support\Licenses\CurrentLicense` (constantes et méthode `select`), relation `Vessel::currentLicense`, lue par `VesselStateBuilder` et `AuthorizedList` |
| Pour changer | Modifier `CurrentLicense::ORDER_COLUMN`, `ORDER_AGGREGATE` et `select` |
| Tests | `AuthorizedListTest::test_a_vessel_with_several_licenses_appears_once_with_the_latest_end_date` |

## H-4a-2 : Suspension : fin prévue et levée (3 mois maximum hors force majeure)

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-2` |
| Question | Suspension : fin prévue et levée (3 mois maximum hors force majeure) |
| Option retenue | La fin prévue est obligatoire sauf pour la force majeure ; elle ne dépasse pas trois mois après le début sauf force majeure ; une suspension reste en vigueur jusqu'à sa levée explicite : dépasser la fin prévue la signale « échue » sans lever la licence automatiquement |
| Alternatives | Levée automatique à la fin prévue par une commande planifiée ; fin prévue facultative pour tous les motifs |
| Où | `Modules\Licenses\Support\SuspensionRules`, clé `surveillance.license_suspension_max_months`, action `LiftLicenseSuspension` |
| Pour changer | Modifier `SuspensionRules::violation` ou la clé de configuration ; pour une levée automatique, ajouter une commande qui appelle `LiftLicenseSuspension` |
| Tests | `SuspensionAndRevocationTest::test_a_planned_end_is_required_except_for_force_majeure`, `test_a_suspension_past_its_planned_end_stays_in_force_and_is_flagged_overdue` |

## H-4a-3 : Qui propose et qui décide une suspension ou une révocation

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-3` |
| Question | Qui propose et qui décide une suspension ou une révocation |
| Option retenue | Le schéma ne porte pas d'état de proposition : l'action enregistre la décision en une étape, `decided_by` est l'utilisateur qui détient la permission `licenses.suspend` ou `licenses.revoke` (Chef d'équipe et Directeur par défaut) ; `proposed_by` reste vide |
| Alternatives | Deux étapes : proposition du Chef d'équipe, décision du Directeur, avec un état de proposition |
| Où | Actions `SuspendLicense` et `RevokeLicense`, permissions `licenses.suspend` et `licenses.revoke` |
| Pour changer | Retirer la permission au Chef d'équipe depuis l'écran des rôles pour réserver la décision au Directeur ; une proposition exigerait une colonne d'état (nouvelle migration) |
| Tests | `SuspensionAndRevocationTest::test_a_suspension_updates_the_license_the_journal_the_state_and_the_audit_log` |

## H-4a-4 : Quels navires la liste autorisée (RG01) contient

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-4` |
| Question | Quels navires la liste autorisée (RG01) contient |
| Option retenue | Les navires ayant au moins une licence au statut actif dont la date d'expiration n'est pas passée ; ni suspendue ni révoquée ; la date d'émission n'est pas contrôlée |
| Alternatives | Exiger aussi une date d'émission passée ; inclure les licences en cours de renouvellement |
| Où | `App\Models\License::scopeAuthorized`, `App\Support\Licenses\AuthorizedList` |
| Pour changer | Modifier `License::scopeAuthorized` |
| Tests | `AuthorizedListTest::test_only_active_unexpired_licenses_authorize_a_vessel` |

## H-4a-5 : Format du fichier d'import et rapprochement du navire

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-5` |
| Question | Format du fichier d'import et rapprochement du navire |
| Option retenue | Fichier CSV avec ligne d'en-tête, séparateur `;` ou `,`, colonnes `registration_number;number;segment;issued_on;expires_on;zones;gears;species;conditions`, valeurs multiples séparées par `|` ; navire rapproché par son numéro d'immatriculation, zones par leur nom, engins et espèces par leur code FAO ; ligne rejetée avec un motif (navire inconnu, numéro manquant ou déjà utilisé, segment ou dates invalides, zone, engin ou espèce inconnus) et conservée |
| Alternatives | Excel, rapprochement par IMO ou nom, création automatique des zones inconnues |
| Où | `Modules\Licenses\Support\CsvReader`, `Modules\Licenses\Support\LineChecker` |
| Pour changer | Modifier `CsvReader::COLUMNS` et `row`, ou `LineChecker::rejection` |
| Tests | `BatchImportTest::test_a_csv_file_is_read_into_lines_with_rejected_lines_kept_and_a_reason` |

## H-4a-6 : Alertes d'échéance à 30, 60 et 90 jours (décision D9)

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-6` |
| Question | Alertes d'échéance à 30, 60 et 90 jours (décision D9) |
| Option retenue | Une alerte résumé par palier et par exécution quotidienne, portant sur les licences dont l'échéance tombe dans la tranche du palier (0 à 30 jours, 31 à 60, 61 à 90) ; délais modifiables par l'Admin ; l'état « bientôt échue » de la liste reste celui du premier palier |
| Alternatives | Alerte cumulative par palier ; une alerte par licence |
| Où | `config/surveillance.php` (`license_expiry_*_warning_days`), réglages du groupe licences, `Modules\Licenses\Support\ExpiryTiers`, `NotifyExpiringLicenses` |
| Pour changer | Modifier les valeurs dans les réglages, ou `ExpiryTiers::bands` pour un autre découpage |
| Tests | `ExpiryTiersAlertTest` |

## H-4a-7 : Récidive à la révocation

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-7` |
| Question | Récidive à la révocation |
| Option retenue | Le champ « récidive » de la révocation est saisi par l'utilisateur ; le calcul automatique par navire, capitaine et armateur relève de la phase 6 |
| Alternatives | Calcul à partir des dossiers d'infraction (phase 6) |
| Où | `Modules\Licenses\Actions\RevokeLicense` |
| Pour changer | Remplacer la valeur saisie par un appel au calculateur de la phase 6 |
| Tests | `SuspensionAndRevocationTest::test_a_license_is_revoked_once_and_can_no_longer_be_suspended` |

## H-4a-8 : Contenu du registre annuel

| Champ | Contenu |
|---|---|
| Identifiant | `H-4a-8` |
| Question | Contenu du registre annuel |
| Option retenue | Toutes les licences dont la validité recoupe l'année (expiration au plus tôt le 1er janvier, émission au plus tard le 31 décembre ou inconnue), en PDF, régénérable sur place pour une même année |
| Alternatives | Licences émises dans l'année seulement ; registre figé à la clôture |
| Où | `Modules\Licenses\Actions\GenerateLicenseRegister` |
| Pour changer | Modifier la requête de l'action |
| Tests | `AuthorizedListTest::test_the_annual_register_lists_the_licenses_of_the_year_and_is_regenerated_in_place` |

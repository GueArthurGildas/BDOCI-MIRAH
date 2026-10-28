# Hypothèses de la phase 5 (M12 Communications)

Statut de toutes les entrées : provisoire, à confirmer par Gildas.

## H-5-1

| Champ | Contenu |
|---|---|
| Identifiant | H-5-1 |
| Question | V10, fournisseur d'e-mail |
| Option retenue | l'e-mail part par le mailer Laravel ; `email_connectors` garde le fournisseur, le domaine d'expédition et la référence du mailer (`parameters.mailer`), jamais de secret ; pilote `log` par défaut |
| Alternatives | API d'un prestataire (accusés et rebonds remontés par webhook) |
| Où | `Channels/EmailDriver.php`, `App\Models\EmailConnector`, `config/mail.php` |
| Pour changer | ajouter un mailer dans `config/mail.php` et le choisir sur l'écran Canaux ; pour les accusés automatiques, écrire un contrôleur de webhook qui appelle `AcknowledgeMessage` |
| Tests | `SendMessageTest::test_the_email_goes_through_the_configured_mailer`, `CommunicationsAdministrationTest::test_only_the_administrator_configures_the_email_connector` |

## H-5-2

| Champ | Contenu |
|---|---|
| Identifiant | H-5-2 |
| Question | V11, consentement et modèles approuvés WhatsApp |
| Option retenue | WhatsApp passe par l'interface `WhatsAppGateway` ; seul le pilote `fake` (enregistre les envois) existe ; un message n'est envoyé que si le numéro a un consentement enregistré, sinon refus (défaut : refuser). Les consentements sont des valeurs de la liste de référence `whatsapp_consents` ; la révocation désactive la valeur. Les modèles approuvés par Meta ne sont pas gérés |
| Alternatives | API WhatsApp Business avec modèles approuvés ; consentement porté par une table dédiée avec date et preuve |
| Où | `Channels/WhatsAppGateway.php`, `Channels/WhatsAppDriver.php`, `Support/WhatsAppConsents.php`, `config/communications.php` (`whatsapp_driver`) |
| Pour changer | écrire un pilote réel, le déclarer dans `CommunicationsServiceProvider` et dans `whatsapp_driver` ; déplacer les consentements dans une table si la preuve est exigée |
| Tests | `SendMessageTest::test_whatsapp_is_refused_without_a_recorded_consent_and_sent_with_one`, `DispatchRulesTest::test_a_whatsapp_rule_does_nothing_without_consent`, `InternalNotesTest::test_the_whatsapp_relay_needs_the_consent_of_the_recipient_number`, `CommunicationsAdministrationTest::test_consents_are_recorded_and_revoked_by_number` |

## H-5-3

| Champ | Contenu |
|---|---|
| Identifiant | H-5-3 |
| Question | à qui part un message automatique (la fiche navire n'a pas de contact) |
| Option retenue | le destinataire est une adresse ou un numéro fixe porté par la règle (`conditions.recipient`) ; une règle sans destinataire ne fait rien |
| Alternatives | contact de l'armateur sur la fiche navire ; carnet d'adresses |
| Où | `Actions/DispatchRuleMessages.php`, `Actions/SaveDispatchRule.php` |
| Pour changer | résoudre le destinataire dans `DispatchRuleMessages::handle` à partir du navire |
| Tests | `DispatchRulesTest::test_inactive_rules_and_rules_without_recipient_are_ignored` |

## H-5-4

| Champ | Contenu |
|---|---|
| Identifiant | H-5-4 |
| Question | sens de la condition d'arrêt (« jusqu'au retour des positions ») |
| Option retenue | la règle est réévaluée à chaque passage : le retour des positions arrête de lui-même le déclencheur `beacon_silent` ; `stop_condition` ajoute `acknowledged` (un message de la règle pour ce navire a un accusé) ou `reply_received`. Un seul message à valider à la fois par règle et navire |
| Alternatives | condition d'arrêt libre en expression ; arrêt par nombre de rappels |
| Où | `Support/StopConditions.php`, `Actions/DispatchRuleMessages::isDue` |
| Pour changer | ajouter un code dans `StopConditions` |
| Tests | `DispatchRulesTest::test_an_acknowledged_message_stops_the_reminders_of_that_vessel_only`, `test_a_received_reply_stops_the_reminders_when_it_is_the_stop_condition` |

## H-5-5

| Champ | Contenu |
|---|---|
| Identifiant | H-5-5 |
| Question | plafond d'envois par jour (paramètre global de la section M10 non livré) |
| Option retenue | le plafond est celui de la règle (`daily_cap`), compté par règle et par jour calendaire, messages à valider compris ; sans valeur, `communications.default_daily_cap` (3). Pas de plafond global ni de réglage Admin |
| Alternatives | plafond global par navire ou par destinataire, réglable dans les paramètres (`add-setting`) |
| Où | `Actions/DispatchRuleMessages::remainingToday`, `config/communications.php` |
| Pour changer | déclarer le réglage dans `Settings::definitions()` et lire `config('communications.default_daily_cap')` |
| Tests | `DispatchRulesTest::test_the_daily_cap_limits_the_messages_of_a_rule_per_day`, `test_the_frequency_spaces_the_reminders_of_a_vessel` |

## H-5-6

| Champ | Contenu |
|---|---|
| Identifiant | H-5-6 |
| Question | mode automatique ou avec validation, et acteur de l'envoi automatique |
| Option retenue | une règle `automatic` n'envoie seule que si le canal a l'envoi automatique activé ; sinon, et pour le mode `with_validation`, le message est préparé et un utilisateur ayant `communications.send` le valide. La planification tourne toutes les 15 minutes sous un compte `system` membre du groupe `automatic_dispatch` |
| Alternatives | exécuter sous le compte de l'auteur de la règle ; fréquence de passage différente |
| Où | `Actions/DispatchRuleMessages.php`, `Support/SystemActor.php`, `CommunicationsServiceProvider::schedule` |
| Pour changer | modifier la fréquence dans `schedule()` ou l'acteur dans `SystemActor` |
| Tests | `DispatchRulesTest::test_a_rule_with_validation_prepares_the_message_until_a_user_sends_it`, `test_an_automatic_rule_only_sends_when_the_channel_allows_automatic_dispatch`, `test_the_run_is_audited_under_the_system_actor_in_the_dispatch_group` |

## H-5-7

| Champ | Contenu |
|---|---|
| Identifiant | H-5-7 |
| Question | V8, permission dédiée à la gestion des envois automatiques |
| Option retenue | permission `communications.manage_dispatch`, accordée au groupe système `automatic_dispatch` ; le Directeur et le Chef d'équipe l'ont aussi (toute permission métier leur revient). Si la règle désigne un groupe modificateur (`editable_by_permission_group_id`), seuls ses membres la modifient |
| Alternatives | réserver la permission au groupe seul (la classer hors des permissions métier héritées) |
| Où | `App\Support\Authorization\Permission`, `Role::permissions()`, `RolesAndPermissionsSeeder`, `Actions/SaveDispatchRule.php` |
| Pour changer | retirer la permission du Directeur et du Chef d'équipe dans `Role` |
| Tests | `DispatchRulesTest::test_only_holders_of_the_dispatch_permission_manage_rules`, `test_a_rule_restricted_to_a_group_can_only_be_edited_by_its_members` |

## H-5-8

| Champ | Contenu |
|---|---|
| Identifiant | H-5-8 |
| Question | agent IA, première action « créer une note » (V25) |
| Option retenue | l'action `communications.create_note` est exécutée sous le compte de type `ai_agent` (groupe `ai_agent_notes`, seule cette permission), à la demande d'un opérateur ayant `communications.trigger_ai_agent` ; elle n'a lieu que si `action_catalog.ai_agent_allowed` et `ai_agent_enabled` sont vrais, sinon la demande est enregistrée comme refusée. Aucun appel à un modèle : le texte de la note est l'instruction de l'opérateur |
| Alternatives | rédaction par un vrai modèle de langage ; écran d'administration de l'activation |
| Où | `Actions/RequestAiNote.php`, `Support/AiAgentAccount.php` |
| Pour changer | remplacer la construction des paramètres dans `RequestAiNote::handle` par l'appel au modèle ; activer l'action dans `action_catalog` |
| Tests | `AiAgentNoteTest` |

## H-5-9

| Champ | Contenu |
|---|---|
| Identifiant | H-5-9 |
| Question | destinataires et relais d'une note |
| Option retenue | un destinataire unique, agent ou équipe (tous les membres actifs sauf l'auteur) ; chaque destinataire reçoit une notification in_app ; le relais e-mail ou WhatsApp part pour chaque destinataire ayant une adresse (et un consentement pour WhatsApp) et `relayed_by` n'est renseigné que si un relais est parti ; « lue » est vrai dès qu'un destinataire la lit. Le canal in_app n'écrit dans `sent_messages` que pour un envoi manuel (UC20) |
| Alternatives | un état de lecture par destinataire ; journaliser aussi les notifications in_app |
| Où | `Actions/CreateNote.php`, `Actions/MarkNoteRead.php` |
| Pour changer | ajouter une table de lecture par destinataire |
| Tests | `InternalNotesTest` |

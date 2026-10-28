# Module Communications (M12)

Messages préconçus, règles d'envoi automatique, notes internes par boutons, canaux in_app, e-mail et WhatsApp. Couvre UC20 et UC21 ; première action de l'agent IA (créer une note, UC27 partiel).

## Tables

`communication_channels`, `email_connectors`, `message_templates`, `dispatch_rules`, `sent_messages`, `ai_agent_actions`, `internal_notes` (modèles dans `app/Models`, énumérations dans `app/Enums`, libellés dans `modules/Communications/lang/{en,fr}/enums.php`). Clés tardives, sans contrainte : `sent_messages.operation_id`, `sent_messages.logbook_entry_id`, `internal_notes.logbook_entry_id`.

## Actions (ADR 0010)

| Code | Permission |
|---|---|
| `communications.send_message`, `send_prepared_message`, `acknowledge_message`, `record_whatsapp_consent`, `revoke_whatsapp_consent` | `communications.send` |
| `communications.create_note` | `communications.create_note` |
| `communications.mark_note_read` | `communications.view` (le destinataire seulement) |
| `communications.request_ai_note` | `communications.trigger_ai_agent` |
| `communications.save_template` | `communications.manage_templates` |
| `communications.save_dispatch_rule`, `update_channel`, `dispatch_rule` | `communications.manage_dispatch` |
| `communications.save_email_connector` | `connectors.manage` |
| `communications.set_ai_agent_enabled` | `settings.manage` (page *Administration › Agent IA*) |
| `communications.record_delivery_report` | `communications.manage_dispatch` (compte `system`, via le webhook) |

## Envois automatiques

`php artisan communications:dispatch` (toutes les 15 minutes) évalue chaque règle active sous le compte `system`, membre du groupe `automatic_dispatch`. Déclencheurs : `beacon_silent`, `license_expiring` (`Support/TriggerEvents`). Plafond par jour, fréquence et condition d'arrêt sont des données de la règle. Une alerte prévient les détenteurs de `communications.send` des messages à valider (cooldown dans `config/communications.php`).

## Réglages et webhook

- Plafond quotidien par défaut et alerte « messages à valider » : réglages de l'Admin (groupe *Messages automatiques*, `docs/assumptions/phase-5.md`, H-5-5).
- `POST /communications/webhooks/delivery` : accusés et rejets envoyés par un fournisseur (`message_id`, `event` = `acknowledged`, `bounced` ou `failed`, `reply_received` facultatif). Authentifié par l'en-tête `X-Webhook-Secret` comparé à `COMMUNICATIONS_WEBHOOK_SECRET` (refus 403 tant que le secret n'est pas défini), sans session ni jeton CSRF. Seul un message au statut `sent` change d'état.

## Canaux

- `in_app` : notification via `Notifier` (destinataire = utilisateur actif).
- `email` : mailer Laravel choisi par le connecteur actif (`parameters.mailer`), pilote `log` par défaut.
- `whatsapp` : interface `WhatsAppGateway`, pilote `fake` qui enregistre les envois ; envoi seulement si un consentement est enregistré (liste de référence `whatsapp_consents`). **Pas de pilote réel** : il dépend du fournisseur (point ouvert V11).

## Hypothèses

H-5-1 à H-5-9 : voir `docs/assumptions/phase-5.md`.

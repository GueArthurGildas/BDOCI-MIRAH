# Movements (M4 Déclarations d'entrée et de sortie de la ZEE)

Phase 4b. Le module porte les déclarations d'entrée et de sortie de la ZEE saisies à la main (ou, plus tard, récupérées par scraping), leurs vérifications, l'appariement entrée/sortie, les non-déclarations et les alertes d'entrée/sortie que lit la veille (phase 3a). Les mouvements portuaires historiques (`movements`) restent disponibles sous « Mouvements portuaires » en attendant `mrep_port_calls` (phase 7).

## Tables

`eez_declarations`, `eez_declaration_catches`, `eez_declaration_checks`, `entry_exit_links`, `missed_declarations`, `entry_exit_alerts` (et `movements` pour les mouvements portuaires). Aucune clé étrangère tardive. `watch_entries.entry_exit_alert_id` (phase 3a) pointera sur `entry_exit_alerts`.

## Écrans

- `/eez-declarations` : liste filtrable, détail (vérifications, captures, appariement), saisie.
- `/eez-declarations/alerts` : alertes d'entrée et de sortie.
- `/eez-declarations/missed` : non-déclarations (relance, qualification).
- `/movements` : mouvements portuaires.

## Actions (ADR 0010)

`RecordEezDeclaration` (identification du navire, captures, vérifications, alerte, appariement des sorties), `AcknowledgeEezDeclaration`, `CloseEezDeclaration`, `ExplainEntryExitVariance`, `CloseEntryExitAlert`, `QualifyMissedDeclaration`, `RemindMissedDeclaration`, `RecordMovement`.

## Permissions

`movements.view`, `movements.create` (mouvements portuaires), `movements.declare` (toutes les écritures sur les déclarations ZEE).

## Alertes et réglages

Commande `movements:notify-eez-declarations` (toutes les heures) : détecte les non-déclarations, notifie les détenteurs de `movements.view`. Réglages de l'Admin : alerte active, fenêtre de position récente, délai entre deux alertes, écart de captures toléré.

## Hypothèses

`H-4b-1` à `H-4b-7` : `docs/assumptions/phase-4b.md`. Cas d'usage couvert : UC10.

## Hors périmètre de cette livraison

Scraping (origine `scraping` prévue par le schéma), accusés Form.02 à 06 par e-mail, décision de suivi renforcé (UC09), explication automatique des écarts par e-inspection (phase 7), sorties non déclarées, vérifications AIS, DPEP 72 h, accès et listes INN (sources absentes).

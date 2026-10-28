# Inspections

Miroir en lecture seule des inspections de l'application e-inspection (module M11) : inspection, documents et engins contrôlés, captures déclarées et constatées avec l'écart comparé au seuil du CSP (UC19), infractions relevées.

- Tables : `external_inspections`, `inspection_documents`, `inspection_gears`, `declared_observed_catches`, `inspection_infractions`. Débarquements et transbordements (`landings`, `transshipments`) sont affichés par le module Catches.
- Écrans : liste filtrable, fiche avec ouverture dans e-inspection ou copie de l'identifiant. Pas de création ni de modification.
- Synchronisation : connecteur `e-inspection` (`App\Support\Connectors\Sources\EInspectionConnector`) via l'action `SynchroniseConnector` du module Administration ; chaque nouvelle inspection d'un navire connu publie l'événement `inspection.received`.
- Table historique `inspections` conservée tant que la phase 6 n'a pas repris `infractions.inspection_id`.
- Hypothèses : H-7-4, H-7-5, H-7-8.

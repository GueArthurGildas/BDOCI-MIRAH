# Vessels (M1 navires)

Fiche navire, identifiants multiples, historique des noms et pavillons, acteurs datés, listes externes, sources et provenance par champ, rapprochement et file des doublons possibles (UC01, RG02).

- **Tables** : `vessels`, `vessel_identifiers`, `vessel_history`, `vessel_sources`, `vessel_field_provenance`, `source_priorities`, `vessel_actors`, `external_list_vessel`.
- **Actions** : `vessels.register`, `vessels.update`, `vessels.ingest_source` (rapprochement IMO, MMSI, indicatif, immatriculation, nom + pavillon), `vessels.settle_duplicate` (fusionner, garder séparées, rejeter), `vessels.add_identifier`, `vessels.add_actor`, `vessels.list_on_external_list`.
- **Permissions** : `vessels.view`, `vessels.manage`, `vessels.match` (file des doublons, Chef d'équipe et Directeur).
- **Écrans** : `/vessels` (liste, fiche à onglets Identifiants, Acteurs, Sources), `/vessels/ingest` (enregistrement depuis une source), `/vessels/duplicates` (file des doublons).
- **Colonnes de compatibilité** : `registration_number`, `imo_number` et `call_sign` restent sur `vessels` et sont synchronisées vers `vessel_identifiers` ; `flag` et `owner` sont des accesseurs sur `flag_country_id` et `vessel_actors`.
- **Hypothèses** : H-2-1, H-2-2, H-2-3, H-2-5, H-2-6, H-2-7, voir `docs/assumptions/phase-2.md`.

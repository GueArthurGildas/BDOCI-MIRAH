# Vessels (M1 navires)

Fiche navire, identifiants multiples, historique des noms et pavillons, acteurs datés, listes externes, sources et provenance par champ, rapprochement et file des doublons possibles (UC01, RG02).

- **Tables** : `vessels`, `vessel_identifiers`, `vessel_history`, `vessel_sources`, `vessel_field_provenance`, `source_priorities`, `vessel_actors`, `external_list_vessel`.
- **Actions** : `vessels.register`, `vessels.update`, `vessels.ingest_source` (rapprochement IMO, MMSI, indicatif, immatriculation, nom + pavillon), `vessels.settle_duplicate` (fusionner, garder séparées, rejeter), `vessels.add_identifier`, `vessels.add_actor`, `vessels.list_on_external_list`.
- **Permissions** : `vessels.view`, `vessels.manage`, `vessels.match` (file des doublons, Chef d'équipe et Directeur).
- **Écrans** : `/vessels` (liste, fiche à onglets Identifiants, Acteurs, Sources), `/vessels/ingest` (enregistrement depuis une source), `/vessels/duplicates` (file des doublons).
- **Colonnes de compatibilité** : `registration_number`, `imo_number` et `call_sign` restent sur `vessels` et sont synchronisées vers `vessel_identifiers` ; `flag` et `owner` sont des accesseurs sur `flag_country_id` et `vessel_actors`.
- **Hypothèses** : H-2-1, H-2-2, H-2-3, H-2-5, H-2-6, H-2-7, voir `docs/assumptions/phase-2.md`.

## Vue 360° (phase 8)

- **Fiche** : en-tête permanent (identifiants, armateur, agent, capitaine, statut à feux avec ses raisons, heure de mise à jour, lien THEMIS), onglets Indicateurs (score de vigilance et ses composantes), Alertes, Déclarations ZEE, Veille, Communications, Documents, Commentaires, Chronologie ; chaque onglet suit le droit de son module.
- **Chronologie** : lue dans la vue `v_vessel_timeline` (journal `vessel_events`), filtrable par module.
- **Règles** : `App\Support\Vessels\StatusLight` (H-8-1) et `VigilanceScore` (H-8-2), seuils dans `config/vigilance.php`.
- **Lectures des infractions et missions** : uniquement par `App\Queries\OpenInfractionCases` et `VesselMissions`.

## Fiche navire : photos, dossiers, fiche PDF

- **Photos** : documents (`documents`) liés au navire par `document_links`, stockés sur le disque privé et servis par `vessels.photos.show` (contrôle du lien et du type image). Actions `AddVesselPhoto` et `RemoveVesselPhoto` (`vessels.manage`). `vessels.primary_photo_id` copie la photo principale pour afficher la miniature dans les listes sans requête ; sans photo, l'image commune `public/images/vessel-placeholder.svg` est utilisée (`Vessel::photoUrl()`).
- **Carrousel** : dans l'en-tête de la fiche, à gauche des informations, avec le statut global juste en dessous ; photos agrandissables au clic (`resources/js/layout/photos.js`). L'ajout et le retrait passent par un modal (bouton « Gérer les photos ») et par le volet « Photos » de la vue en dossiers.
- **Vue en dossiers** : `?view=folders&pane=<volet>`, arbre à gauche construit par `Support/VesselFolderTree` (dossiers, sous-dossiers, éléments), volet à droite réutilisant les partiels des onglets ; les volets suivent les mêmes permissions que les onglets.
- **Fiche technique** : `vessels.sheet` génère un PDF (identification, caractéristiques, licence, balise et dernière émission, photo principale).
- **Statuts** : au survol, chaque badge explique sa signification (`enums.descriptions.*`).

# Registry (M1 référentiels)

Référentiels de la phase 2, gérés par des écrans génériques décrits dans `Support/ResourceCatalog.php` (liste, création, modification).

- **Tables** : `countries`, `institutions`, `legal_entities`, `people`, `vessel_types`, `species`, `gears`, `presentations`, `ports`, `zone_rules`, `external_lists`, `contacts`, `agents`, `accreditations`, plus les rangs de `source_priorities`.
- **Actions** : `registry.create_record`, `registry.update_record`, `registry.update_source_priorities` (permission `registry.manage`, audit à chaque écriture).
- **Permissions** : `registry.view` (Agent, Chef d'équipe, Directeur), `registry.manage` (Chef d'équipe, Directeur).
- **Écrans** : `/registry` (accueil), `/registry/{ressource}`, `/registry/source-priorities`.
- **Hypothèses** : H-2-1 (ordre des sources), H-2-4 (règles de zone), H-2-8 (données FAO de démonstration), voir `docs/assumptions/phase-2.md`.
- Ajouter un référentiel : déclarer un `RegistryResource` dans le catalogue et les libellés dans `lang/{en,fr}/registry.php`.

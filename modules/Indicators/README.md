# Indicators (vue flotte et détail des indicateurs)

Vue flotte du Directeur et détail filtrable de chaque indicateur (UC25, UC29). Lecture seule : aucune table propre, aucune action d'écriture.

- **Écrans** : `/fleet` (indicateurs cliquables, répartition du statut à feux, classement par score de vigilance), `/indicators/{indicateur}` (liste des navires qui composent le nombre, filtres combinables : période, zone, pavillon, segment, statut à feux, recherche).
- **Indicateurs** : navires actifs en ZEE, balises silencieuses, licences qui expirent, infractions en cours, opérations ouvertes, missions en cours, navires conformes / à surveiller / suspects / en infraction (`Support/IndicatorCatalog`).
- **Lectures** : modèles partagés de `app/Models`, requêtes `App\Queries\SilentBeacons`, `OpenInfractionCases` et `VesselMissions` (les deux dernières sont les seuls points de lecture des infractions et missions, à repointer après la phase 6), statut à feux et score dans `App\Support\Vessels`.
- **Permission** : `indicators.view` (Chef d'équipe, Directeur). Le Directeur arrive sur la vue flotte (`App\Support\Navigation\Landing`).
- **Hypothèses** : H-8-1 à H-8-4, voir `docs/assumptions/phase-8.md`.

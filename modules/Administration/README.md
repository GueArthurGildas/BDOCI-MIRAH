# Administration

Comptes, rôles, groupes, réglages, listes de référence, champs supplémentaires, journal d'audit et supervision des connecteurs.

## Connecteurs (phase 7)

- Tables : `data_sources` (mode, voyant, dernière erreur, données périmées), `data_source_syncs`, `data_source_rejections`, `data_source_errors`, `external_links`.
- Actions : `SynchroniseConnector` (permission `connectors.sync`, Admin), `ChangeConnectorMode` et `UpdateExternalLink` (permission `connectors.manage`).
- Commandes planifiées : `connectors:sync` (horaire, sources dont le mode n'est pas l'import) et `connectors:refresh-freshness` (horaire).
- Noyau : interface `App\Support\Connectors\Connector` (fetch, normalise, upsert, référence externe) ; connecteurs `e-inspection`, `dpep-mrep`, `vms-themis`, `neno` alimentés par des charges utiles enregistrées (`database/fixtures/connectors`). Les modes `api` et `direct_read` lisent ces fichiers, `import` lit un fichier JSON déposé, `scraping` n'est pas implémenté.
- Rejets : un enregistrement invalide est conservé avec son motif ; il est soldé automatiquement quand une synchronisation ultérieure l'accepte.
- Réglages : groupe « Connecteurs » (délai de péremption, seuil d'écart de captures).
- Hypothèses : H-7-1, H-7-2, H-7-3, H-7-7 (`docs/assumptions/phase-7.md`).

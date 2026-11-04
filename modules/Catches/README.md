# Catches (captures, débarquements, transbordements)

Rubriques de l'e-inspection (menu Pilotage), en lecture seule.

- **Captures** (`catches.index`) : quantités déclarées par espèce, par mois et par navire, avec filtres navire, période rapide (30 jours, 3 mois, 12 mois), dates libres, espèce, zone et source. Chaque page indique la source de ses lignes. La fiche d'un navire renvoie vers cette page déjà filtrée (H-6-3).
- **Débarquements** et **transbordements** : lus depuis e-inspection par le connecteur (`App\Support\Connectors\Sources\EInspectionConnector`), créés ou mis à jour sans doublon ; la page affiche l'origine des lignes et la date de dernière synchronisation.
- **Limite connue** : les déclarations de captures (`catch_declarations`) ne sont alimentées par aucun import, seulement par le jeu de démonstration (question dans `docs/QUESTIONS_GILDAS.md`). L'origine « saisie de repli » n'a pas d'écran de saisie.
- **Permission** : `catches.view`.

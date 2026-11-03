# Dashboard (tableau de bord personnalisable)

Page d'accueil des profils opérationnels. Lecture seule, sauf le choix des indicateurs par chaque utilisateur.

- **Catalogue** : `Support/WidgetCatalog` décrit 28 éléments en trois types : indicateurs chiffrés (11), graphiques (12) et tableaux (2), plus les indicateurs de `App\Support\Indicators\IndicatorCatalog`. Chacun a un titre, une explication (`lang/*/widgets.php` ou `indicators::indicators.*`), une permission et une page de détail.
- **Sélection** : colonne `users.dashboard_widgets` (jsonb, ordonnée). `null` = sélection par défaut (`WidgetCatalog::DEFAULT_KEYS`). Un utilisateur ne voit et ne choisit que les éléments dont il détient la permission.
- **Écrans** : `/dashboard`, modal de détail `/dashboard/widgets/{widget}` (explication, valeur, chiffres, aperçu de cinq éléments, lien « Voir plus »), modal `/dashboard/customize`.
- **Cohérence** : un indicateur chiffré calcule sa valeur avec le même `IndicatorCatalog` que la liste détaillée, donc le nombre affiché est celui de la liste ouverte par « Voir plus ».
- **Messages** : le compartiment « Mes messages » lit les notes internes adressées à l'utilisateur ou à son équipe (`InternalNote::addressedTo`).
- **Ajouter un élément** : une entrée dans `WidgetCatalog::all()`, son calcul dans `chart()` ou `kpiValue()`, et ses textes fr et en.

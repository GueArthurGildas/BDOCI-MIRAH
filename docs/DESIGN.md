# Guide du design BDOCI-MIRAH (thème GESICA)

Le thème est posé **par-dessus Adminty** : on ne modifie jamais `resources/scss/adminty/`. Toutes les surcharges vivent dans `resources/scss/theme/`, importé en dernier dans `app.scss`.

## Fichiers

| Fichier | Rôle |
|---|---|
| `scss/theme/_tokens.scss` | Variables CSS : couleurs, rayons, ombres, police. Seule source de vérité. |
| `scss/theme/_mixins.scss` | Mixins de boutons pleins et contour. |
| `scss/theme/_base.scss` | Fond de page, titres, liens. |
| `scss/theme/_topbar.scss` | Barre orange : onglets de groupes, outils, menu utilisateur. |
| `scss/theme/_shell.scss` | Disposition (sidebar + contenu), menu contextuel, carte guide. |
| `scss/theme/_page-header.scss` | Bandeau noir de titre de page. |
| `scss/theme/_cards.scss` | Cartes et cartes de statistique. |
| `scss/theme/_tables.scss` | Tableaux et pagination. |
| `scss/theme/_buttons.scss`, `_forms.scss`, `_labels.scss`, `_tabs.scss` | Contrôles. |
| `views/components/layouts/app.blade.php` | Structure de page. |
| `views/layouts/partials/{header,sidebar}.blade.php` | Barre du haut et menu de gauche. |
| `views/components/ui/{page-header,stat-card}.blade.php` | Composants de page. |

## Règles de design

- **Couleurs unies, aucun dégradé.** Pour une nouvelle couleur, ajouter une variable dans `_tokens.scss`, jamais un code hex ailleurs.
- **Rayons** (`_tokens.scss`) : `--gs-radius` (12px) pour les surfaces (cartes, bandeau, menus déroulants), `--gs-radius-sm` (8px) pour les contrôles (boutons, champs, étiquettes, onglets) et `--gs-radius-pill` pour les éléments en gélule (lignes du menu latéral). Aucune valeur de rayon en dur ailleurs.
- **Cercle (`50%`)** pour les avatars, pastilles d'icône, pastilles de compteur et logos ronds.
- Un enfant d'une carte n'a pas son propre rayon : la carte est en `overflow: hidden` et découpe ses coins (par exemple l'en-tête de tableau).
- **Tableaux** : une carte qui contient un `<x-ui.table>` est encadrée de bleu nuit (`--gs-frame`, rayon `--gs-radius-frame`) ; son titre et ses boutons sont sur le cadre, le tableau est sur une surface blanche arrondie, l'en-tête de colonnes est bleu nuit. Le style repose sur `.card:has(.table-hover)` : aucune classe à ajouter. Les filtres par colonne et les exports de la maquette GESICA ne sont pas génériques : ils se construisent page par page.
- Orange = marque et actions, noir = structure (traits, bandeaux, en-têtes de tableau), vert/rouge/bleu = états.

## Navigation

`App\Support\Navigation\Navigation` fournit les groupes. Chaque groupe est un onglet de la barre orange ; les éléments du groupe actif sont affichés dans la sidebar (une ligne en gélule par élément, l'actif en orange plein ; un compteur optionnel s'ajoute avec l'argument `badge:` de `Navigation::add`, une fonction qui renvoie le nombre d'éléments en attente), qui reste collée sous la barre orange pendant le défilement de la page et défile seule (liste des liens) quand elle dépasse la hauteur de l'écran ; sous 992 px elle devient un menu horizontal. L'élément actif est celui dont la route (ou `<route sans .index>.*`) correspond à la requête. L'icône du bandeau de page est celle de l'élément actif, sauf si `icon` est passé à `<x-ui.page-header>`.

Ajouter une entrée de menu se fait dans la classe `Navigation` (via le fournisseur du module), jamais dans les vues. Les textes passent par `lang/{en,fr}/ui.php` ou `modules/*/lang`.

## Écrire une page

```blade
<x-layouts.app :title="__('dashboard::dashboard.title')">
    <x-ui.page-header :title="__('dashboard::dashboard.title')" :subtitle="__('dashboard::dashboard.subtitle')"/>

    <div class="row">
        <div class="col-md-3">
            <x-ui.stat-card :label="__('dashboard::dashboard.active_vessels')" :value="$active" icon="icon-anchor" color="success"/>
        </div>
    </div>

    <x-ui.card :title="__('dashboard::dashboard.zones')">
        <x-ui.table>
            <x-slot:head><th>{{ __('ui.actions') }}</th></x-slot:head>
        </x-ui.table>
    </x-ui.card>
</x-layouts.app>
```

Couleurs de `stat-card` : `warning` (orange, défaut), `success`, `danger`, `info`.

## Pièges connus avec Adminty

- Adminty applique `.user-profile { padding: 20px 0 }` (page profil) : ne jamais mettre cette classe dans la barre du haut, elle ajoute 40px de hauteur.
- `.table > thead > tr > th` et `.md-tabs .nav-item a` (avec `!important` pour le padding) sont plus spécifiques que les sélecteurs simples : les surcharger avec le même sélecteur.
- `.card .card-header + .card-body` retire le padding haut : le thème le rétablit.
- Adminty met `.btn` et `.label` en capitales initiales (`text-transform: capitalize`) : le thème les remet en casse normale. Les cases à cocher `checkbox-fade` ont une couleur par état, plus spécifique : le thème impose l'orange avec `!important`.
- Si une modification de style n'apparaît pas, redémarrer le conteneur `node` (`docker restart bdoci-mirah-node-1`) : l'observateur de fichiers de Vite sur le volume monté perd parfois les changements.

## Visite guidée

Le bouton « Guide utilisateur » de la sidebar (et l'icône « ? » de la barre du haut) lance une visite de la page courante : une fenêtre d'introduction puis un projecteur sur chaque élément important, avec une explication courte.

- **Pages et cibles** : `config/guide.php` associe un nom de route à une liste ordonnée `étape => sélecteur CSS`. Une page sans entrée affiche la visite générique (menu, onglets, outils).
- **Textes** : `lang/{en,fr}/guide.php`. `pages.<route avec _>.intro` est obligatoire ; `pages.<route>.steps.<étape>` donne le titre et le texte d'une étape, sinon `common.<étape>` sert de texte par défaut (barre d'outils, filtres, tableau, exports…).
- **Cibles dans les vues** : poser `data-guide="clé"` sur une carte (`<x-ui.card data-guide="clé" …>`) plutôt que de compter sur la position ; les sélecteurs de listes (`.gs-table-toolbar`, `.gs-filter-row`, `.card .table`) existent déjà.
- **Droits** : une étape dont la cible est absente de la page (bouton réservé à un autre rôle, carte vide) est ignorée automatiquement.
- **Test** : `tests/Feature/GuideTest.php` vérifie que chaque page configurée est une vraie route, a son introduction et ses textes dans les deux langues.

## Fenêtres

Trois fenêtres Bootstrap sont montées une fois dans le layout (`layouts/partials/confirm-modal`, `remote-modal`, `popup`) et pilotées par `resources/js/layout/` ; les styles sont dans `scss/theme/_modals.scss`.

- **Confirmation** : tout `<form>` (ou bouton d'envoi) portant `data-confirm` ouvre une fenêtre avec le message, un lien « Annuler » et un bouton orange (`data-confirm-label`). `data-confirm-title` change le titre ; `data-confirm-details` (JSON `[{label, value}]`) ajoute des tuiles de récapitulatif. Les textes d'un module sont dans `modules/<Module>/lang/{en,fr}/confirm.php`.
- **Réussite** : une redirection avec `->with('status', …)->with('popup', true)` affiche le message dans une fenêtre à coche verte au lieu du bandeau ; `->with('popup', [['label' => …, 'value' => …]])` ajoute des tuiles.
- **Aperçu** : un lien `data-gs-modal` charge son URL en AJAX dans la fenêtre ; la route renvoie un fragment HTML sans layout (exemple : l'œil à côté d'un navire, `vessels.quick`).


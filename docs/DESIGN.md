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
- **Deux rayons seulement** : `--gs-radius` (6px) pour les surfaces (cartes, sidebar, bandeau, menus déroulants) et `--gs-radius-sm` (4px) pour les contrôles (boutons, champs, étiquettes, onglets, lignes de menu).
- **Cercle (`50%`) uniquement** pour les avatars, pastilles d'icône et logos ronds. Pas de forme « pilule ».
- Un enfant d'une carte n'a pas son propre rayon : la carte est en `overflow: hidden` et découpe ses coins (par exemple l'en-tête de tableau).
- Orange = marque et actions, noir = structure (traits, bandeaux, en-têtes de tableau), vert/rouge/bleu = états.

## Navigation

`App\Support\Navigation\Navigation` fournit les groupes. Chaque groupe est un onglet de la barre orange ; les éléments du groupe actif sont affichés dans la sidebar. L'élément actif est celui dont la route (ou `<route sans .index>.*`) correspond à la requête. L'icône du bandeau de page est celle de l'élément actif, sauf si `icon` est passé à `<x-ui.page-header>`.

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

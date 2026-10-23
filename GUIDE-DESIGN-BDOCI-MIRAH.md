# Guide du design BDOCI-MIRAH (thème GESICA)

Ce guide explique comment la vue est construite et comment la modifier sans casser le reste.
Le thème est posé **par-dessus Adminty** : on ne touche pas aux fichiers `scss/adminty/`.

---

## 1. Installation (à faire une fois)

```bash
npm i @fontsource/poppins      # police des titres
npm run build                  # ou : npm run dev
```

Puis recharger le navigateur sans cache : **Ctrl + F5**.
Si une couleur ne change pas, c'est presque toujours le cache.

---

## 2. Fichiers du thème

| Fichier (dans `resources/`) | Rôle |
|---|---|
| `scss/_gesica.scss` | **Toutes les couleurs et le style du thème.** Importé en dernier dans `app.scss`. |
| `scss/app.scss` | Ajoute les polices Poppins et l'import `gesica`. |
| `views/components/layouts/app.blade.php` | Structure de page : barre du haut + menu de gauche + contenu. |
| `views/layouts/partials/header.blade.php` | Barre orange du haut : onglets, recherche, plein écran, langue, profil. |
| `views/layouts/partials/sidebar.blade.php` | Menu de gauche : logo MIRAH, éléments du groupe actif, carte « Guide utilisateur ». |
| `views/components/ui/page-header.blade.php` | Bandeau noir de titre de page (accepte une icône). |
| `views/components/ui/stat-card.blade.php` | Carte de statistique (pastille d'icône colorée). |
| `js/layout/sidebar-toggle.js` + `index.js` | Bouton burger du menu sur mobile. |
| `images/mirah-bgless.png` | Logo MIRAH (menu de gauche). |

---

## 3. Changer les couleurs

Tout est dans le bloc `:root` en haut de `scss/_gesica.scss`. **Modifier une variable change toute l'application.**

| Variable | Valeur actuelle | Sert à |
|---|---|---|
| `--gs-orange` | `#ef5f00` | Orange principal (boutons, badges, icônes) |
| `--gs-orange-600` | `#c94a00` | Orange foncé (onglets, menu actif, en-têtes de cartes) |
| `--gs-orange-300` | `#ff7a1a` | Orange vif (accents) |
| `--gs-orange-soft` | `#f7a85c` | Fond de la barre du haut |
| `--gs-black` | `#07080d` | Noir (bandeau, en-têtes de tableaux, compteurs) |
| `--gs-ink` | `#0f111a` | Texte foncé |
| `--gs-bg` | `#e5e9f8` | Fond de page |
| `--gs-green` / `--gs-red` / `--gs-blue` / `--gs-teal` | | Succès / danger / info / action |

Règle choisie : **couleurs unies, aucun dégradé**. Pour ajouter une couleur, créer une variable dans `:root` plutôt que d'écrire un code hex ailleurs.

---

## 4. Comment la navigation fonctionne

La classe `App\Support\Navigation\Navigation` (déjà dans ton projet) renvoie des **groupes** d'éléments.

- Chaque **groupe** devient un **onglet** de la barre orange (Pilotage, Surveillance, Référentiel…).
- Les **éléments du groupe actif** s'affichent en pastilles dans le menu de gauche.
- L'onglet actif est celui qui contient la page courante (`$item->isActive()`).
- Le **compteur** rond d'une pastille vient de `$item->badge` : si cette propriété n'existe pas ou vaut `null`, aucun compteur n'est affiché.
- Le texte du lien est traduit avec `__($item->label)`, l'icône est une classe Feather (`icon-home`, `icon-anchor`…).

**Ajouter un onglet ou un menu** : ajoute-le dans la classe `Navigation`, pas dans les vues.

Clés de traduction à avoir : `ui.guide_in_progress`, `ui.user_guide`, `ui.fullscreen`, `ui.menu`, `ui.search`, `ui.log_out`, `ui.home`.

---

## 5. Écrire une page avec les composants

```blade
<x-layouts.app :title="'Tableau de bord'">
    <x-ui.page-header title="Tableau de bord" subtitle="Indicateurs clés" icon="icon-bar-chart-2"/>

    <div class="row">
        <div class="col-md-3">
            <x-ui.stat-card label="Navires actifs" :value="$actifs" icon="icon-anchor" color="success"/>
        </div>
    </div>

    <x-ui.card title="Situation par zone">
        <x-ui.table>
            <x-slot:head><th>Date</th><th>Statut</th></x-slot:head>
            <tr><td>05/10/2026</td><td><span class="label label-success">Validé</span></td></tr>
        </x-ui.table>
    </x-ui.card>
</x-layouts.app>
```

Couleurs possibles pour `stat-card` : `warning` (orange), `success` (vert), `danger` (rouge), `info` (bleu).

### Classes utilitaires du thème

| Classe | Effet |
|---|---|
| `.gs-tile` + `.gs-tile-label` + `.gs-tile-value` | Tuile noire d'information (N° caisse, solde…). Ajouter `.is-accent` pour une valeur sur fond orange. |
| `.gs-actionbtn is-orange / is-teal / is-blue / is-black` | Petits boutons d'action en pilule (Voir, Reçu…). |
| `.gs-pilltabs` (`li.active`, `.count`) | Onglets en pastilles avec compteur. |
| `.label label-success / -danger / -warning / -info / -inverse` | Badges de statut. |
| `.btn btn-primary / -success / -danger / -info / -dark` | Boutons. |

---

## 6. Éléments de l'ancien template conservés

| Élément d'avant | Où il est maintenant |
|---|---|
| Menu mobile (`#mobile-collapse`) | Bouton burger dans la barre du haut (écrans < 992 px), géré par `sidebar-toggle.js` |
| Recherche dépliable (`data-header-search`) | Barre du haut, à droite (visible si la route `search` existe), géré par `header-search.js` |
| Plein écran (`data-toggle-fullscreen`) | Barre du haut, à droite |
| Menu de langue | Barre du haut, via `locale-menu.blade.php` (inchangé) |
| Profil + déconnexion | Menu déroulant avec avatar, à droite |
| Titre de groupe du menu (`pcoded-navigatio-lavel`) | Petit libellé au-dessus des pastilles du menu de gauche |
| Messages flash, préchargeur, infobulles, soumission auto | Inchangés |
| Actions de carte (réduire, plein écran) | Inchangées (`ui/card.blade.php`) |
| Logo | Remplacé par **MIRAH** (`mirah-bgless.png`) |

Ce qui n'est plus utilisé : la structure `#pcoded` d'Adminty (le menu vertical Adminty). Son code JS reste mais ne fait rien.

---

## 7. Pièges connus

1. **Gros espace orange en haut de page.** Le thème timeline contient une règle globale `header { height: 300px }`. C'est pourquoi la barre du haut est une `<div role="banner">` et non une balise `<header>`. Ne pas la remplacer par `<header>`.
2. **Couleur qui ne change pas** : cache du navigateur (Ctrl + F5) ou `npm run build` oublié.
3. **Pas de compteur dans le menu** : ajouter une propriété `badge` à l'élément de navigation.
4. **`vessel-link.blade.php`** vient d'un autre projet : à renommer ou retirer.

---

## 8. Ce qui reste à faire

- **Étape 2** : page de connexion (`layouts/auth`) et pages d'erreur avec le logo MIRAH.
- **Étape 3** : tableaux, filtres et formulaires comme GESICA (filtres dans l'en-tête de tableau, boutons Excel / PDF / Imprimer).
- **Étape 4** : fenêtres modales (en-tête noir, bouton principal orange).

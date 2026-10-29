# Manuel d'utilisation

`BDOCI-MIRAH_Manuel_FR_EN.pdf` : manuel destiné au client et à ses employés, en français puis en anglais (guide par rôle, procédures pas à pas, programme de formation, glossaire, annexes).

## Régénérer le PDF

Prérequis : application démarrée (`make setup`, `make fresh`), Vite actif sur le port 5173, Google Chrome, Ghostscript, `pdftotext`, Pillow.

```bash
python3 docs/manuel/build/screens.py /tmp/manuel-work fr,en
python3 docs/manuel/build/build.py /tmp/manuel-work
```

- `build/screens.py` se connecte avec les quatre comptes de démonstration, photographie chaque écran dans les deux langues et écrit `screens/{fr,en}/*.jpg`.
- `build/content_*.py` contiennent tout le texte, français et anglais côte à côte (`t(fr, en)`) ; `build/lib.py` fournit les composants (titres, encadrés, procédures, tableaux) ; `build/style.css` la mise en page.
- `build/perms.json` liste les permissions et leurs rôles par défaut (annexe A) ; à régénérer quand le catalogue des permissions change.
- Les numéros de page de la table des matières sont calculés par deux passes de rendu.

# Matrice d'acceptation

Cette matrice relie chaque **cas d'usage** (UC01 à UC29) et chaque **règle de gestion** (RG01 à RG10) de la spécification (`database/docs/BOID_MIRAH_specification_conception (1).md`, sections 8 et 14) à la phase qui les livre, et dit quelle garantie le modèle vise.

**Elle ne dit pas où en est la couverture.** Le statut (à faire, partiel, fait) et la liste des tests qui couvrent chaque identifiant sont **calculés à partir des tests** par `php artisan docs:acceptance` et écrits dans `docs/reference/acceptance.md` : c'est ce fichier qui fait foi. Ce document-ci reste la source des identifiants, des intitulés, des acteurs, des modules et des phases.

Les phases sont celles de `docs/PLAN_REFONTE.md`.

---

## 1. Cas d'usage

| Id | Intitulé | Acteur | Modules | Phase |
|---|---|---|---|---|
| UC01 | Créer ou rapprocher un navire | Agent, Chef d'équipe | M1, M10 | 2 |
| UC02 | Enregistrer une licence délivrée | Groupe FNP/licences | M2 | 1 (tranche verticale), 4a |
| UC03 | Suivre les expirations, suspendre ou révoquer | Groupe FNP/licences, Directeur | M2, M8 | 4a |
| UC04 | Vérifier la conformité d'une balise | CSP, armateur, installateur | M3 | 3b |
| UC05 | Prendre son service et surveiller à chaque créneau | Équipe de quart | M5, M3 | 3a, 3b |
| UC06 | Traiter une alerte ou un silence VMS | Opérateur | M3, M5, M12 | 3b |
| UC07 | Gérer une avarie VMS | Opérateur, superviseur | M3, M5, M8 | 3b |
| UC08 | Créer une observation et la relayer | Agent | M5 | 3a |
| UC09 | Passer un navire en suivi renforcé | Agent, Chef d'équipe | M4, M5 | 3a |
| UC10 | Enregistrer une déclaration ZEE | Opérateur | M4, M12 | 4b |
| UC11 | Ouvrir, suivre, clôturer une opération | Opérateur, Chef d'équipe | M5 | 3a |
| UC12 | Tenir la main courante et passer le relais | Équipes de quart | M5 | 3a |
| UC13 | Consulter une DPEP et la décision d'entrée | Chef d'équipe, Directeur | M6, M12 | 7 |
| UC14 | Suivre les notifications aux États et organismes | Opérateur | M6 | 7 |
| UC15 | Recevoir un signalement de témoin | Opérateur, Chef d'équipe | M7 | 6 |
| UC16 | Constituer un dossier d'infraction | Inspecteur, groupe Instruction | M8 | 6 |
| UC17 | Décider des suites | Directeur, Chef d'équipe | M8, M2 | 6 |
| UC18 | Planifier et conduire une mission | Chef d'équipe, chef de mission | M9 | 6 |
| UC19 | Consulter inspection et écarts de captures | Chef d'équipe, Directeur | M11 | 7 |
| UC20 | Envoyer un message préconçu ou programmer un envoi | Agent, groupe Envois auto. | M12 | 5 |
| UC21 | Laisser une note à un collègue | Agent | M12 | 5 |
| UC22 | Ouvrir THEMIS sur la fiche du navire | Agent | M3, M10 | 3b |
| UC23 | Piloter les connecteurs | Admin | M10 | 7 |
| UC24 | Gérer utilisateurs, droits, paramètres | Admin | M10 | 9 |
| UC25 | Obtenir la vue 360° d'un navire ou de la flotte | Directeur | tous | 8 |
| UC26 | Voir les navires à surveiller à l'ouverture | Tous | M5, M4, M10 | 3a |
| UC27 | Confier une tâche à l'agent IA | Opérateur autorisé | M10, M12 | 9 |
| UC28 | Valider des licences en lot | Groupe FNP/licences | M2 | 4a |
| UC29 | Ouvrir le détail d'un indicateur | Directeur | tous | 8 |

---

## 2. Règles de gestion

| Id | Règle | Garantie visée par le modèle | Phase |
|---|---|---|---|---|---|
| RG01 | Le référentiel des navires autorisés est maintenu à jour | liste calculée à partir des licences actives, avec indicateur de fraîcheur | 4a |
| RG02 | Un navire suivi est identifiable de manière unique | identifiants uniques contrôlés, file de validation des doublons | 2 |
| RG03 | Un navire soumis au VMS dispose d'un dispositif associé | balise affectée obligatoire, sinon anomalie | 3b |
| RG04 | La balise émet pendant la période requise | période requise calculée et taux d'émission | 3b |
| RG05 | Le remplacement d'une balise est historisé | historique des affectations avec périodes | 3b |
| RG06 | La surveillance prend en compte position, vitesse et trajectoire | `position_creneau`, règles de cohérence paramétrables | 3b |
| RG07 | Les licences sont suivies sur validité et renouvellement | échéances calculées, alertes paramétrables | 4a |
| RG08 | Les observations sont conservées et rattachées au navire | observation liée au navire | 3a |
| RG09 | Inspections et infractions historisées | versions et historique | 1, 6 |
| RG10 | Les informations importantes restent accessibles aux équipes successives | relais, consignes permanentes, main courante | 3a |

---

## 3. Convention `#[Covers('UC05')]`

Le lien entre un test et un cas d'usage est porté par le test lui-même, pour qu'il ne puisse pas se périmer en silence.

**Attribut.** `Tests\Attributes\Covers` s'applique à une classe ou à une méthode de test, de façon répétable, et accepte un ou plusieurs identifiants. `partial: true` déclare que le test ne couvre qu'une partie du cas ou de la règle :

```php
#[Covers('UC05', 'RG04', partial: true)]
public function test_zone_situation_counts_vessels_present_at_a_given_time(): void
```

Il ne remplace pas `#[CoversClass]` de PHPUnit, qui sert à la couverture de code.

**Commande `php artisan docs:acceptance`.** Elle lit les attributs par réflexion et écrit `docs/reference/acceptance.md` : pour chaque identifiant de ce document, le statut (« à faire » sans test, « partiel » si tous les tests déclarent `partial: true`, « fait » sinon) et les tests qui le couvrent. Elle signale les cas sans test et échoue si un test cite un identifiant absent de ce document. Avec `--check`, elle échoue aussi si le rapport n'est plus à jour ; `GeneratedDocumentationTest` l'exécute à chaque lancement des tests.

**Ce document.** Il reste la source des identifiants, des intitulés, des phases et de l'analyse (colonne « Garantie visée »). Le statut est celui de `docs/reference/acceptance.md`. Quand vous écrivez un test qui recouvre un cas d'usage ou une règle, ajoutez `#[Covers]` puis relancez `php artisan docs:acceptance`.

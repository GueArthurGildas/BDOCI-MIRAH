# Comment je travaille avec Claude Code sur BDOCI-MIRAH

Ce document décrit ma pratique réelle, reconstituée à partir de mes sessions de travail du 17 octobre au 4 novembre 2026.
Il ne dit pas comment on devrait faire, il dit comment j'ai fait, avec ce qui a bien marché et ce qui a coûté du temps.

## 1. Ma posture

Je suis le développeur, pas le décideur. Le design vient de Gildas, les règles métier viennent des documents, et mon rôle est de transformer tout cela en application qui tourne.
Claude écrit le code ; moi, je choisis la direction, je regarde le résultat et je décide de ce qui est gardé.

Concrètement, je ne le laisse jamais décider seul de ce qui change l'architecture ou le métier. Il propose avec une recommandation, je tranche.

## 2. Comment ça a commencé

Première session, 17 octobre : je voulais un environnement Laravel sous Docker pour pouvoir changer d'appareil sans tout refaire.
Dès les premiers messages, j'ai posé les règles qui ne sont jamais revenues en discussion :

- code uniquement en anglais (le README peut être en français) ;
- code lisible, sans commentaires partout ;
- design fidèle au template fourni, pas une réplique irréfléchie des pages du template ;
- README bref, détails dans un README technique ;
- travail sur ma branche `y-branch`, jamais sur `main` ;
- projet modulaire, layout propre, aucune chaîne de texte en dur (FR/EN dès le départ).

Je les ai dites en une fois, au début, puis elles ont été enregistrées dans la mémoire de projet de Claude.
Elles se retrouvent aujourd'hui aussi dans les skills et les décisions d'architecture du dépôt.

## 3. Comment je lui donne le contexte

Je ne résume pas les documents de mémoire, je les lui fais lire : les PDF de cadrage et du cahier des charges, les spécifications,
rangés dans le dossier `docs/` en créant un nouveau dossier s'il le faut, avec des captures et une notice si disponible. 

Quand un sujet est flou, je lui demande d'abord **sa propre compréhension, sans la biaiser** avec la mienne.
C'est ce que j'ai fait pour l'idée de base « staging » puis base centrale après la seconde réunion : je lui ai demandé son analyse avant de lui dire ce que je croyais avoir compris.
Ensuite, je compare avec ce que je sais.

Je lui demande aussi des schémas quand le texte ne suffit pas (pipeline de données, ERD, cas d'utilisation), et je lui demande de les rendre plus détaillés quand ils laissent trop de place à l'interprétation.

## 4. Mon cycle : analyser, planifier, découper, exécuter

1. **Analyse puis plan écrit.** Pour la base du projet, la refonte de la base de données ou les demandes fournies, j'ai d'abord demandé « analyse, fais des suggestions et écris un plan », sans code.
2. **Je tranche les décisions.** Exemples réels : passer à PostgreSQL, garder PostGIS, base de données en anglais avec glossaire, gestion des rôles et permissions modifiables par l'administrateur, rôles de départ répartis par seeders, ne pas toucher au conteneur node.
   Quand Claude me recommande quelque chose, je réponds « ta recommandation est bonne » ou je corrige. Je lui dis aussi : « demande-moi si tu veux que je clarifie ».
3. **Découpage en phases, puis en lots.** La refonte a été découpée en phases numérotées (« lance la phase 0 », « start phase 1 »). Pour les demandes, en lots (« lot 2 », « lot 3 », « lot 4 »).
   Une demande courte avec un numéro suffit parce que le plan est écrit.
4. **Exécution lot par lot, un commit par lot.** « Continue avec le lot 5, et n'oublie pas un commit simple par lot. » Quand il y a plusieurs sujets, je demande des commits séparés, voire de regrouper (« squash X dans Y »).
5. **Tests et recette.** Claude lance la suite de tests. Pour chaque lot, je lui fais préparer la liste des tests manuels, rangée dans un fichier de checklist, parce que les tests ne remplacent pas mes yeux.

## 5. Mes messages sont courts

La plupart de mes messages tiennent en quelques mots : « oui », « go ahead », « continue », « lot 4 », « commit ».
Cela marche parce que le contexte est écrit ailleurs (plan, skills, mémoire) et que Claude propose lui-même la suite (« what is next in the implementation plan? »).

Quand c'est visuel, je corrige en une phrase précise, au moment où je le vois :
- « la sidebar doit rester fixe par rapport au contenu et défiler seule quand il y a trop de liens » ;
- « le lien actif doit correspondre exactement à l'URL, "agent IA" et "rapports programmés" s'allument ensemble » ;
- « utilise des noms ivoiriens pour les utilisateurs » ;
- « supprime les références à Adminty dans les noms de fichiers » ;
- « le logo a changé, corrige les tailles partout ».

Je colle les erreurs brutes (comme des échecs de `make up`) sans les reformuler. Claude diagnostique à partir du texte exact.

## 6. Je ne laisse pas passer ce que je ne comprends pas

Je pose des questions directes, y compris quand tout semble fonctionner :
- « tu t'es servi de l'état actuel du projet ou de ta compréhension des documents ? » (l'état du projet était incomplet, je voulais la spécification) ;
- « qu'entends-tu par fixer comment `y-branch` et `g-branch` s'intègrent ? » ;
- « comment as-tu su que la couverture des documents PDF générés débordait, tu ne peux pas voir ? » (j'avais le même problème sur mes propres scripts Word) ;
- « as-tu créé des modales et popups à partir des images ? où sont-elles utilisées ? ».

Je le fais aussi pour vérifier que ses réponses reposent sur la bonne source.

## 7. Git : mes règles

- Je demande les commits moi-même (« commit », « trois commits séparés ») ; Claude ne commite pas de lui-même.
- Un commit simple par phase ou par lot. Quand j'ai vu environ 150 fichiers non commités, j'ai exigé de vérifier que rien de la phase en cours, inachevée, ne soit embarqué.
- Les spécifications (`database/docs/`) et les documents sources (`docs/new/`) restent hors des commits de code.
- La branche du collègue qui a fait le design (`g-branch`) est conservée ; son design est repris dans la mienne avec une consigne : utiliser intelligemment le nouveau design.

## 8. Quand j'ai eu besoin d'aller très vite

Dans la nuit du 27 au 28 octobre, une présentation était prévue le lendemain. J'ai voulu terminer toutes les phases de la refonte en une nuit. La méthode a été :

- écrire la liste des questions ouvertes et **choisir moi-même la meilleure option pour chaque question**, en gardant un moyen simple de corriger après les réponses du collaborateur ;
- faire travailler plusieurs phases en parallèle : des sous-agents, chacun sur sa branche (`stream/p4a`, `p4b`, `p5`, `p6`, `p7`, `p8`, `p9`, `p3b`) dans des copies de travail séparées ;
- exiger que chaque phase soit livrée avec la suite de tests complète verte, deux fois de suite ;
- reprendre ensuite les phases terminées.

Les limites d'usage ont coupé la session plusieurs fois, et la session reprenait où elle s'était arrêtée. Le lendemain, j'ai mis à jour la présentation et fait le ménage dans les documents.

## 9. Ma gestion de la documentation

Je trouve vite qu'il y en a trop. À plusieurs reprises j'ai demandé : « on a trop de documents, lesquels sont encore à jour ? » (plan de refonte, questions ouvertes, acceptation).
Claude propose, je valide, et on supprime ou on regroupe. Je garde :
- les documents destinés au client (manuel d'utilisation en PDF, français puis anglais, par rôle) ;
- les documents techniques pour les développeurs ;
- les décisions d'architecture et les hypothèses à valider.

Je distingue clairement les deux publics (client et développeurs) et je relis aussi les détails.

## 11. Ce que j'ai appris à ne pas faire

D'après mes sessions :
- Donner un lot trop gros : le lot 6 a dû être découpé en six sous-lots.
- Laisser une commande `git add` trop large : `docs/new/` a déjà été embarqué.
- Lancer deux suites de tests en parallèle sur la même base.
- Mettre des données d'exemple dans une migration (elles cassaient les tests) : elles vont dans un seeder.
- Faire confiance à un test vert sans regarder l'écran.

## 12. Ce que je valide toujours moi-même

- Les règles métier qui ne sont écrites nulle part, que je note comme questions ouvertes avec un identifiant d'hypothèse (H-6-1…).
- Les droits à donner sur une base existante.
- Les écrans, à l'œil.
- La publication sur Github.

## 13. Pour travailler seul

1. Lire `docs/GUIDE.md`, `docs/TECHNICAL.md` et le README du module concerné.
2. Lancer l'application (`make up`) puis `php artisan test`.
3. Retrouver le pourquoi d'une chose avec `git log` (un commit par lot), `docs/decisions/` et `docs/assumptions/`.
4. S'il utilise Claude Code : lui faire lire les mêmes documents, travailler lot par lot, demander les tests manuels, et ajouter au dépôt (skills, docs) toute règle qu'il aura dû répéter deux fois, parce que la mémoire de Claude est locale à la machine.

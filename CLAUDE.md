# Projet : outil de ticketing (socle Mantis-lite)

## Contexte

Atelier d'Agentic Engineering. Un binôme construit un outil de ticketing en 80 minutes.
Le socle fournit la base de données et l'API. Le binôme construit surtout l'interface.
Le modèle de données reprend celui de MantisBT (tables `mantis_*`, codes de statut, priorité, sévérité).

## Déroulé de l'atelier

`/demarrer` → `/specs` → `/maquette` → `/construire` → `/documenter` → `/livrer` → `/revue` → fusion sur github.com → `/lancer`.
Si le binôme demande la suite, indique la prochaine commande de cette liste.
Après la fusion de la pull request, indique `/lancer`. Après `/lancer`, l'atelier est terminé.
Si le binôme est en retard, propose de simplifier la fonctionnalité en cours. S'il reste bloqué, propose d'appeler l'animateur.

## Stack imposée

- PHP 8 sans framework, PDO SQLite. Pas de Composer.
- Front : Vue 3 en build global (`public/vendor/vue`), Bootstrap 5, Bootstrap Icons.
- Pas de npm, pas d'étape de build, pas de CDN.
- N'ajoute aucune dépendance. Si un besoin l'exige, demande d'abord au binôme.
- Écris l'interface en français.

## Carte du code

| Fichier | Rôle |
|---|---|
| `src/constantes.php` | Codes MantisBT, libellés, couleurs de statut |
| `src/db.php` | Connexion SQLite et fonctions `db_all`, `db_one`, `db_exec` |
| `src/api.php` | Routes de l'API, règles métier, validation (`validate_fields`) |
| `public/index.php` | Routeur HTTP : `/api/*` vers l'API, sinon `index.html` |
| `public/app.js` | Application Vue. `ROUTES` et `MENU` déclarent les écrans. |
| `public/app.css` | Styles propres à l'application |
| `db/schema.sql`, `db/seed.sql` | Schéma et données de démonstration |
| `outils/tests/smoke.php` | Tests de fumée de l'API |
| `outils/serveur.php` | Lanceur du serveur de développement |
| `outils/pr.sh` | Crée la pull request et publie la revue, sans la CLI `gh` |
| `outils/installer.ps1`, `outils/git/` | Kit portable : Git sans droits admin (`/installer`), seulement si Git manque |

## Commandes

| Action | Windows (Git Bash) | macOS / Linux |
|---|---|---|
| PHP | `outils/php/php.exe` | `php` |
| Tests | `outils/php/php.exe outils/tests/smoke.php` | `php outils/tests/smoke.php` |
| Serveur | aperçu « atelier (Windows) » | aperçu « atelier (Mac-Linux) » |
| Réinitialiser la base | supprimer `data/mantis.sqlite` | idem |

## Règles de code

- Utilise toujours les requêtes préparées (`db_all`, `db_one`, `db_exec`). Ne concatène jamais une valeur dans du SQL.
- Valide chaque entrée côté serveur, dans `validate_fields()` ou une fonction dédiée.
- N'utilise jamais `v-html` ni `innerHTML`.
- Renvoie les erreurs de l'API au format `{"erreur": "..."}` avec le bon code HTTP.
- Garde les tables `mantis_*` et les codes MantisBT.
- Après chaque fonctionnalité, lance les tests de fumée. Ajoute un test pour chaque nouvelle règle métier.
- Après une modification du schéma, supprime `data/mantis.sqlite`. La base se recrée au prochain appel.

## Git

- Ne travaille jamais sur `main`. Crée la branche `equipe-XX` avant la première modification (XX = numéro du binôme).
- À `/livrer`, la branche prend le nom de l'équipe : `equipe-<nom>`. La pull request part de cette branche.
- Fais un commit par fonctionnalité terminée. Écris le message en français.
- Ne pousse jamais vers `main`. Crée la pull request avec `bash outils/pr.sh creer`. Le binôme relit et fusionne sur github.com.

## Jetons

- Lis seulement les fichiers utiles à la tâche.
- Modifie le code par petites touches. Ne réécris pas un fichier entier sans raison.
- Respecte `SPECS.md` : trois fonctionnalités au maximum. Refuse poliment les ajouts hors périmètre.

## Fichiers produits pendant l'atelier

- `SPECS.md` : spécifications du binôme (`/specs`, `/maquette`).
- `public/maquette.html` : maquette statique (`/maquette`).
- `DOCUMENTATION.md` : documentation (`/documenter`).

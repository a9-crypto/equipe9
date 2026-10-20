# Atelier ticketing – socle Mantis-lite

Socle de départ d'un atelier d'Agentic Engineering avec Claude Code.
Chaque binôme part de ce dépôt et construit son propre outil de ticketing.

## Ce que contient le socle

- **Base SQLite** au modèle de [MantisBT](https://mantisbt.org) : tickets, textes, notes, historique, projets, catégories, utilisateurs.
- **API JSON** en PHP 8 sans framework : liste filtrée, création, fiche, modification, suppression, notes.
- **Interface Vue 3 + Bootstrap 5**, sans build : la liste des tickets.
- **Données de démonstration** : 14 tickets de support informatique, 3 projets, 6 utilisateurs.
- **Harness Claude Code** : `CLAUDE.md`, commandes de l'atelier, garde-fou sur la branche `main`.

Tout est livré dans le dépôt : PHP portable pour Windows, Git portable (plan B), Vue, Bootstrap. Aucun `npm install`, aucun Composer.

## Prérequis

- App Claude desktop (onglet Code), connectée à un compte Claude.
- [Git for Windows](https://git-scm.com/download/win) sous Windows. Sous macOS : PHP 8 (`brew install php`).
- Un compte GitHub.

## Démarrage

1. Sur GitHub, cliquez sur **Use this template** → **Create a new repository**. Cochez **Private**.
2. Dans l'app Claude, onglet Code, demandez : « Clone le dépôt `<adresse de votre dépôt>` dans mon dossier Documents ».
3. Ouvrez une nouvelle session sur le dossier cloné.
4. Tapez `/demarrer`.

## Commandes de l'atelier

| Étape | Commande | Résultat |
|---|---|---|
| 0 | `/installer <adresse>` | Seulement si Git manque : installe Git portable et relie le dossier zip au dépôt |
| 1 | `/demarrer` | Vérifie PHP, lance les tests, ouvre l'aperçu |
| 2 | `/specs` | Interroge le binôme, écrit `SPECS.md` |
| 3 | `/maquette` | Produit `public/maquette.html` |
| 4 | `/construire` | Crée la branche `equipe-XX`, code les fonctionnalités |
| 5 | `/documenter` | Écrit `DOCUMENTATION.md` |
| 6 | `/livrer <nom d'équipe>` | Renomme la branche au nom de l'équipe, la pousse et crée la pull request |
| 7 | `/revue` | Relit la branche (conformité, vulnérabilités, secrets, tests) et publie la revue sur la pull request |
| 8 | `/lancer` | Après la fusion : récupère `main` et lance l'application complète |

## Structure

```
src/            API PHP (constantes MantisBT, accès SQLite, routes)
public/         application Vue, routeur PHP, bibliothèques front (vendor/)
db/             schéma et données de démonstration
outils/         PHP portable Windows, lanceur du serveur, tests, contrôle du poste
.claude/        réglages, garde-fou, aperçu et commandes de l'atelier
```

## Lancer à la main

```bash
php outils/serveur.php              # macOS / Linux
outils/php/php.exe outils/serveur.php   # Windows (Git Bash)
```

Puis ouvrez http://127.0.0.1:8000. Tests : `php outils/tests/smoke.php`.

## Licences

- Code du socle : MIT (voir `LICENSE`). Code original, modèle de données inspiré de MantisBT (GPL v2).
- Vue 3, Bootstrap 5, Bootstrap Icons : MIT (fichiers `LICENSE` dans `public/vendor`).
- PHP : licence PHP (`outils/php/LICENSE-PHP.txt`).

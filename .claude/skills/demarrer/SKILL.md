---
name: demarrer
description: Étape 1 de l'atelier. Vérifie PHP, lance les tests et ouvre l'aperçu du socle. À lancer une seule fois, au début.
disable-model-invocation: true
---

# Démarrer le socle

But : le binôme voit l'application tourner dans l'aperçu en moins de 5 minutes.
Ne modifie aucun fichier du dépôt pendant cette étape.

## Étapes

1. Lance `git --version`. Si Git est absent, arrête-toi. Demande au binôme de taper `/installer` suivi de l'adresse de son dépôt.
2. Vérifie la connexion au dépôt GitHub : `git remote get-url origin`, puis `git ls-remote --heads origin`.
   - Si une fenêtre de connexion GitHub s'ouvre, le binôme s'y connecte avec le compte du binôme.
   - En cas d'échec (« Repository not found », « Authentication failed »), arrête-toi. Affiche le message exact. Demande au binôme d'appeler l'animateur.
3. Vérifie l'identité Git : `git config user.name` et `git config user.email`.
   - Si l'une des deux est vide, règle-la pour ce dépôt seulement : `git config user.name "Binôme atelier"` et `git config user.email "binome@atelier.local"`. Annonce-le au binôme.
4. Lance `uname -s` pour détecter le système.
5. Choisis la commande PHP :
   - Windows (résultat contenant MINGW, MSYS ou CYGWIN) : `outils/php/php.exe`.
   - macOS ou Linux : `php`. Si `php` est absent, arrête-toi. Affiche la commande d'installation (macOS : `brew install php`).
6. Lance `<php> -v`, puis `<php> -m`. Vérifie la présence des modules `pdo_sqlite` et `mbstring`.
   - Si `php.exe` ne démarre pas, arrête-toi. Affiche le message d'erreur exact. Demande au binôme d'appeler l'animateur.
7. Lance les tests : `<php> outils/tests/smoke.php`. Tous les tests doivent réussir.
8. Ouvre l'aperçu avec la configuration de `.claude/launch.json` adaptée au système :
   - Windows : « atelier (Windows) ».
   - macOS ou Linux : « atelier (Mac-Linux) ».
   - En cas d'échec : lance `<php> outils/serveur.php` en arrière-plan, puis ouvre « atelier (déjà lancé) ».
9. Contrôle que la liste des tickets s'affiche dans l'aperçu.
10. Lance `git branch --show-current`. Ne crée pas encore de branche.

## Message final au binôme

Écris 7 lignes au maximum :
- Les vérifications faites : Git, connexion GitHub, identité Git, PHP, tests.
- Ce que contient le socle : une API de tickets inspirée de MantisBT (création, fiche, notes, statuts, affectation, filtres).
- Les données de démonstration : 14 tickets, 3 projets, 6 utilisateurs.
- L'écran existant : la liste des tickets.
- La prochaine étape : taper `/specs`.

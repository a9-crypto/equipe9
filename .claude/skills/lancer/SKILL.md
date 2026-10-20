---
name: lancer
description: Étape finale de l'atelier. Après la fusion de la pull request, récupère main et lance l'application complète en local pour la vérifier.
disable-model-invocation: true
---

# Lancer la version finale

But : vérifier l'application fusionnée dans `main`, en conditions réelles, en 5 minutes.
Ne modifie aucun fichier du code pendant cette étape.

## 1. Récupérer main

1. Lance `git status`. S'il reste des modifications non enregistrées, arrête-toi. Explique au binôme qu'il faut d'abord les commiter sur sa branche.
2. Lance `git switch main`, puis `git pull`.
3. Lance `git log --oneline -3`. Vérifie la présence du commit « Merge pull request ».
   - Si ce commit est absent, arrête-toi. Explique au binôme qu'il faut d'abord fusionner la pull request sur github.com.

## 2. Préparer la base

Demande au binôme : « Quelle base voulez-vous pour la démonstration ? » Options :
- « Base neuve » : supprime `data/mantis.sqlite`. La base se recrée avec les 14 tickets de départ.
- « Garder nos tickets » : ne touche à rien.

## 3. Lancer l'application

1. Choisis la commande PHP : `outils/php/php.exe` sous Windows, `php` sous macOS ou Linux.
2. Lance les tests de fumée. Tous les tests doivent réussir.
3. Si un aperçu est déjà ouvert, arrête-le. Ouvre l'aperçu avec la configuration de `.claude/launch.json` adaptée au système.
   - En cas d'échec : lance `<php> outils/serveur.php` en arrière-plan, puis ouvre « atelier (déjà lancé) ».
4. Note l'adresse du serveur (port 8000 par défaut).

## 4. Guider la vérification

Lis `SPECS.md`. Affiche une liste de contrôle, une ligne par critère d'acceptation :
- la fonctionnalité (F1, F2, F3),
- l'action à faire à l'écran (page, bouton, saisie),
- le résultat attendu.

## Message final au binôme

1. L'adresse de l'application, à ouvrir aussi dans le navigateur du poste (Edge ou Chrome).
2. La liste de contrôle ci-dessus.
3. La consigne : « Vérifiez chaque ligne. Si un critère échoue, notez-le : il servira de première tâche à la prochaine itération. »
4. Le rappel : envoyer le lien du dépôt à l'animateur. L'atelier est terminé.

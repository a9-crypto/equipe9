---
name: livrer
description: Étape 6 de l'atelier. Vérifie les tests, place le travail sur une branche au nom de l'équipe, la pousse et crée la pull request vers main.
argument-hint: <nom de l'équipe>
disable-model-invocation: true
---

# Livrer la branche

But : la pull request de l'équipe est ouverte sur GitHub, depuis une branche à son nom, en 3 minutes.
Nom d'équipe reçu : `$ARGUMENTS`

## 1. Nom de l'équipe et branche

1. Si le nom d'équipe est vide, demande-le au binôme : « Quel est le nom de votre équipe ? »
2. Calcule le nom de branche : `equipe-<nom>`, avec ces règles pour `<nom>` :
   - minuscules, sans accents,
   - espaces et caractères spéciaux remplacés par `-`, sans `-` en double, ni au début, ni à la fin,
   - 30 caractères au maximum.
   Exemple : « Les Faucons Bleus » donne `equipe-les-faucons-bleus`.
3. Vérifie le nom : `git check-ref-format --branch equipe-<nom>`.
4. Lance `git branch --show-current`.
   - Si la branche est `main`, arrête-toi. Explique au binôme qu'il faut d'abord `/construire`.
   - Si la branche porte déjà ce nom, passe à la suite.
   - Sinon, renomme la branche de travail : `git branch -m equipe-<nom>`. Les commits restent les mêmes.
5. Signe les prochains commits au nom de l'équipe : `git config user.name "Équipe <nom saisi>"`.

## 2. Envoyer et ouvrir la pull request

1. Lance les tests de fumée. Si un test échoue, arrête-toi. Propose une correction.
2. Lance `git status`. S'il reste des modifications, fais un commit : `git add -A`, puis `git commit -m "<message en français>"`.
3. Lance `git push -u origin equipe-<nom>`.
   - Si Git demande une connexion, explique au binôme qu'une fenêtre du navigateur s'ouvre pour GitHub. Il s'y connecte, puis tu relances la commande.
4. Écris le fichier `.git/pr.md` :
   - 1re ligne : le titre, `Équipe <nom saisi> – <fonctionnalités>`.
   - Lignes suivantes : la description, 10 lignes au maximum. Fonctionnalités, critères d'acceptation atteints, tests, points à vérifier par les relecteurs.
5. Lance `bash outils/pr.sh creer .git/pr.md`. Le script crée la pull request de la branche `equipe-<nom>` vers `main` et affiche son adresse.
   - Si le script signale une erreur, affiche le message exact. Demande au binôme d'appeler l'animateur.

## Message final au binôme

1. Le nom de la branche et l'adresse de la pull request.
2. La consigne : « Ouvrez le lien pour voir la pull request. Ne fusionnez pas encore. »
3. La prochaine étape : taper `/revue`.

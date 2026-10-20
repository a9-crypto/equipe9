---
name: construire
description: Étape 4 de l'atelier. Crée la branche equipe-XX, présente un plan court, puis code les fonctionnalités de SPECS.md une par une.
disable-model-invocation: true
---

# Construire l'application

But : coder les fonctionnalités de `SPECS.md`, une par une, sur la branche du binôme.

## 1. Préparer la branche

1. Lis le numéro du binôme dans le titre de `SPECS.md`.
2. Lance `git branch --show-current`.
3. Si la branche est `main`, lance `git switch -c equipe-XX` dans une commande séparée.
4. Signe les commits au nom du binôme, pour ce dépôt seulement : `git config user.name "Binôme XX"`. Si `git config user.email` est vide, lance `git config user.email "binome@atelier.local"`.

## 2. Présenter le plan

Vérifie d'abord l'avancement : lance `git log --oneline main..HEAD` et relis les critères cochés dans `SPECS.md`.
Si des fonctionnalités sont déjà terminées, annonce-les et reprends à la suivante.

Écris un plan de 10 lignes au maximum. Pour chaque fonctionnalité restante : les fichiers à modifier et la route d'API.
Demande au binôme : « On lance la construction ? » Options : « Oui », « Ajuster le plan ».

## 3. Coder, fonctionnalité par fonctionnalité

Pour chaque fonctionnalité, dans l'ordre F1, F2, F3 :
1. Si l'API doit changer : modifie `src/api.php`, puis ajoute un test dans `outils/tests/smoke.php`.
2. Crée le composant Vue dans `public/app.js`. Déclare-le dans `ROUTES`, puis dans `MENU` si besoin.
3. Lance les tests de fumée (voir `CLAUDE.md`, section Commandes). Corrige jusqu'au succès.
4. Recharge l'aperçu. Vérifie chaque critère d'acceptation de `SPECS.md`. Coche les critères atteints.
5. Fais un commit : `git add -A`, puis `git commit -m "feat: <fonctionnalité>"`.
6. Annonce au binôme en 2 lignes : ce qui marche, et la fonctionnalité suivante.

## Règles

- Respecte `CLAUDE.md` : stack imposée, requêtes préparées, pas de `v-html`, interface en français.
- Ne code rien hors de `SPECS.md`. Si le binôme demande un ajout, propose de l'inscrire dans « Hors périmètre ».
- Si une fonctionnalité bloque plus de 10 minutes, arrête-toi. Propose au binôme de la simplifier ou de passer à la suivante.

## Message final

Résume les fonctionnalités terminées. Indique la prochaine étape : taper `/documenter`.

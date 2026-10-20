---
name: revue
description: Étape 7 de l'atelier. Relit la branche avant fusion - conformité à SPECS.md, vulnérabilités, secrets, tests. Corrige un point au maximum.
disable-model-invocation: true
---

# Relire la pull request

But : décider si la branche peut être fusionnée dans `main`, en 10 minutes.
Tu joues le rôle d'un relecteur exigeant. Signale seulement des problèmes réels, avec le fichier et la ligne.

## 1. Collecter

1. Lance `git fetch origin`.
2. Lance `git diff origin/main...HEAD --stat`, puis `git diff origin/main...HEAD`.
   Si `origin/main` n'existe pas, utilise `main`.
3. Lis `SPECS.md`.

## 2. Contrôler

**A. Conformité** : pour chaque critère d'acceptation de `SPECS.md`, note « atteint », « partiel » ou « absent ».

**B. Vulnérabilités** (diff seulement) :
- SQL : valeur concaténée dans une requête au lieu d'un paramètre `?`.
- XSS : `v-html`, `innerHTML`, `document.write`.
- Validation : entrée de l'API sans contrôle côté serveur (type, longueur, valeur autorisée).
- Fuite d'information : message d'exception ou chemin de fichier renvoyé au navigateur.
- Accès : route qui modifie ou supprime sans contrôle de l'utilisateur.

**C. Secrets** : cherche dans le diff les mots de passe, clés d'API et jetons
(motifs : `password`, `secret`, `api_key`, `token`, `BEGIN PRIVATE KEY`, `sk-`, `ghp_`, `AKIA`).

**D. Tests** : lance les tests de fumée. Vérifie qu'une règle métier nouvelle a son test.

## 3. Rendre le rapport

Affiche :
1. Un tableau de 5 constats au maximum : gravité (Bloquant, Important, Mineur), fichier:ligne, problème, correction proposée.
2. Le bilan de conformité : critères atteints / total.
3. Le verdict : « Prêt à fusionner » ou « À corriger avant fusion ».

## 4. Corriger

Demande au binôme : « Quel constat corriger maintenant ? » Propose les trois premiers constats, plus « Aucun ».
Corrige un seul constat. Lance les tests. Fais un commit. Lance `git push`.

## 5. Publier la revue

1. Écris le fichier `.git/revue.md` : le rapport de revue en Markdown (tableau des constats, conformité, verdict, correction faite).
2. Lance `bash outils/pr.sh commenter .git/revue.md`. Le script publie la revue en commentaire de la pull request.

## Message final au binôme

1. L'adresse du commentaire publié.
2. La consigne : « Sur GitHub, relisez la pull request : la description, la revue de Claude et la vérification Windows (coche verte). Si tout est bon, cliquez sur Merge pull request, puis Confirm merge. »
3. Après la fusion : taper `/lancer` pour récupérer `main` et vérifier l'application complète.

Pour une analyse de sécurité plus poussée, le binôme peut aussi lancer la commande intégrée `/security-review`.

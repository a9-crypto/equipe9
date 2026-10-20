---
name: documenter
description: Étape 5 de l'atelier. Écrit DOCUMENTATION.md à partir du code et de SPECS.md, puis fait un commit.
disable-model-invocation: true
---

# Documenter l'application

But : une documentation d'une page, en 5 minutes.

## Étapes

1. Vérifie que la branche courante n'est pas `main`.
2. Lis `SPECS.md`. Lance `git diff main --stat` pour repérer les fichiers modifiés. Lis seulement ces fichiers.
3. Crée `DOCUMENTATION.md` avec ce plan :
   - **Présentation** : 3 lignes. Pour qui, pour quoi.
   - **Fonctionnalités** : une sous-partie par fonctionnalité. Écris comment l'utiliser, étape par étape.
   - **API** : tableau des routes (méthode, chemin, rôle). Signale les routes ajoutées par le binôme.
   - **Lancer le projet** : commandes Windows et macOS (voir `CLAUDE.md`).
   - **Tests** : commande et nombre de tests.
   - **Limites connues** : ce qui reste hors périmètre ou incomplet.
4. Écris en français. Phrases courtes, voix active.
5. Fais un commit : `git add DOCUMENTATION.md`, puis `git commit -m "docs: documentation de l'application"`.

## Message final

Indique la prochaine étape : taper `/livrer`.

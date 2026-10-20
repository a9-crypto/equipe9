---
name: maquette
description: Étape 3 de l'atelier. Décrit les écrans à partir de SPECS.md et produit une maquette HTML statique visible dans l'aperçu.
disable-model-invocation: true
---

# Prévoir le front-end

But : le binôme valide ses écrans avant le code, en 5 minutes.

## Étapes

1. Lis `SPECS.md`, `public/index.html` et le début de `public/app.js` (constantes `ROUTES` et `MENU`).
2. Le socle a déjà un écran : la liste des tickets (`#/`). Pars de cet écran. Liste les écrans à ajouter ou à modifier. Pour chaque écran, précise :
   - son nom et son adresse (exemple : `#/kanban`).
   - les blocs affichés (tableau, formulaire, colonnes, cartes).
   - les données utilisées et la route d'API.
3. Crée `public/maquette.html` :
   - Reprends la barre de navigation de `public/index.html`.
   - Utilise seulement Bootstrap et Bootstrap Icons, déjà présents dans `public/vendor`.
   - N'ajoute aucun JavaScript. Écris des données fictives réalistes (contexte support informatique).
   - Affiche les écrans les uns sous les autres, avec un titre pour chacun. Marque chaque écran « existant » ou « nouveau ».
4. Ouvre l'aperçu sur `/maquette.html`.
5. Demande au binôme : « La maquette vous convient-elle ? » Options : « Oui, on construit », « Ajuster » (une seule série d'ajustements).
6. Dans la section « Écrans » de `SPECS.md`, remplace la ligne `<complété par /maquette>` par un tableau : écran, adresse, existant ou nouveau, fonctionnalité (F1, F2, F3), route d'API.

## Message final

Indique la prochaine étape : taper `/construire`.

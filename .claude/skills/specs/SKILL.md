---
name: specs
description: Étape 2 de l'atelier. Interroge le binôme sur son besoin, puis écrit SPECS.md (trois fonctionnalités au maximum). N'écrit aucun code.
disable-model-invocation: true
---

# Définir les spécifications

But : un fichier `SPECS.md` clair, avec trois fonctionnalités au maximum, en 10 minutes.
N'écris aucun code pendant cette étape. Ne lis que `CLAUDE.md` et `src/api.php`.

## 1. Interroger le binôme

Utilise l'outil de questions (AskUserQuestion). Pose les questions en deux appels au maximum.

Premier appel :
- « Quel est le numéro de votre binôme ? » Options : 01 à 04, et « Autre » pour 05 à 10.
- « Qui utilisera votre outil ? » Options : techniciens du support, utilisateurs qui déclarent un incident, responsables d'équipe.
- « Quelles fonctionnalités de base voulez-vous ? » (choix multiple) Options :
  - Création de ticket (formulaire)
  - Fiche ticket avec notes
  - Affectation et changement de statut
  - Filtres et recherche
- « Quelles fonctionnalités avancées voulez-vous ? » (choix multiple) Options :
  - Tableau kanban par statut
  - Tableau de bord (compteurs)
  - Échéances et retards (SLA)
  - Export CSV

Si le binôme choisit plus de trois fonctionnalités, demande-lui d'en garder trois.

Second appel (facultatif) : une question de précision par fonctionnalité retenue, quatre questions au maximum.
Exemples : statuts affichés dans le kanban, délai cible par priorité, colonnes de l'export.

## 2. Vérifier la faisabilité

Pour chaque fonctionnalité, compare le besoin avec l'API existante (`src/api.php`) :
- L'API couvre le besoin : écris « API existante » et cite la route.
- L'API ne couvre pas le besoin : décris la modification minimale (nouvelle route, nouveau filtre, nouveau champ).

## 3. Écrire SPECS.md

Crée `SPECS.md` à la racine avec ce gabarit :

```markdown
# Spécifications – binôme XX

## Contexte
<2 phrases : pour qui, pour quoi>

## Fonctionnalités retenues

### F1 – <nom>
- Besoin : <une phrase>
- Critères d'acceptation :
  - [ ] <critère vérifiable dans l'aperçu>
  - [ ] <critère vérifiable>
- API : <route existante, ou modification minimale>

### F2 – ...
### F3 – ...

## Hors périmètre
- <ce que le binôme ne fera pas aujourd'hui>

## Écrans
<complété par /maquette>
```

Règles :
- Trois critères d'acceptation au maximum par fonctionnalité.
- Chaque critère doit pouvoir se vérifier dans l'aperçu en moins d'une minute.

## 4. Conclure

Affiche le contenu de `SPECS.md`. Demande au binôme de le valider ou de le corriger, en une seule fois.
Indique la prochaine étape : taper `/maquette`.

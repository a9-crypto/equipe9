# Spécifications – binôme 09

## Contexte
Les responsables d'équipe pilotent le support : ils veulent voir d'un coup d'œil la charge de l'équipe, répartir les tickets et repérer ceux qui dépassent leur délai.

## Fonctionnalités retenues

### F1 – Tableau de bord (compteurs)
- Besoin : voir le nombre de tickets par statut, par priorité et non affectés.
- Critères d'acceptation :
  - [x] Un écran « Tableau de bord » est accessible depuis le menu.
  - [x] Des compteurs affichent le nombre de tickets par statut (7 statuts MantisBT) et le total (14 avec les données de démo).
  - [x] Un compteur indique les tickets non affectés ; un clic mène à la liste filtrée.
- API : modification minimale, nouvelle route `GET /api/stats` (compteurs par statut, par priorité, non affectés, en retard). Le calcul côté front à partir de `GET /api/tickets` reste possible en solution de repli.

### F2 – Affectation et changement de statut
- Besoin : affecter un ticket à un utilisateur et changer son statut depuis la fiche ou la liste.
- Critères d'acceptation :
  - [x] Depuis un ticket, une liste déroulante permet de choisir le responsable ; le ticket « Nouveau » passe à « Affecté ».
  - [x] Une liste déroulante permet de changer le statut ; le nouveau statut s'affiche immédiatement.
  - [x] Passer à « Résolu » ou « Fermé » met la résolution à « Corrigé » si elle était ouverte.
- API : API existante, `PATCH /api/tickets/{id}` (champs `handler_id`, `status`, règles MantisBT déjà appliquées) et `GET /api/meta` pour les listes.

### F3 – Échéances et retards (SLA)
- Besoin : repérer les tickets en retard selon un délai cible par priorité.
- Critères d'acceptation :
  - [ ] Chaque ticket ouvert affiche son échéance (champ `due_date`) ; un badge « En retard » apparaît si elle est dépassée.
  - [ ] À la création, l'échéance est calculée selon la priorité : Immédiate 4 h, Urgente 1 j, Élevée 2 j, Normale 5 j, Basse 10 j.
  - [ ] Un filtre « En retard » dans la liste n'affiche que les tickets ouverts dont l'échéance est dépassée.
- API : le champ `due_date` existe (`POST` et `PATCH /api/tickets`). Modification minimale : calcul par défaut de `due_date` selon la priorité dans `ticket_create`, et nouveau filtre `overdue=1` dans `ticket_list`.

## Hors périmètre
- Création de ticket par formulaire et fiche ticket avec notes (écrans de base non retenus aujourd'hui).
- Tableau kanban.
- Export CSV.
- Authentification et droits par rôle.
- Notifications et envoi d'e-mails.

## Écrans
Maquette : `public/maquette.html`.

| Écran | Adresse | Statut | Fonctionnalité | Route d'API |
|---|---|---|---|---|
| Tableau de bord | `#/tableau-de-bord` | nouveau | F1 | `GET /api/stats` (nouvelle) |
| Liste des tickets (colonne et filtre « En retard ») | `#/` | existant, modifié | F2, F3 | `GET /api/tickets` (filtre `overdue=1`) |
| Fiche ticket (responsable, statut, échéance) | `#/ticket/{id}` | nouveau | F2, F3 | `GET` et `PATCH /api/tickets/{id}`, `GET /api/meta` |

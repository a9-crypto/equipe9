# Documentation – binôme 09

## Présentation

Cet outil de ticketing aide les responsables d'équipe à piloter le support informatique.
Il montre la charge de l'équipe, permet de répartir les tickets et repère ceux qui dépassent leur délai.
Il repose sur le socle Mantis-lite : PHP 8, SQLite, Vue 3 et Bootstrap 5, en français.

## Fonctionnalités

### F1 – Tableau de bord

1. Cliquez sur « Tableau de bord » dans le menu (`#/tableau-de-bord`).
2. Lisez les compteurs du haut : total, non affectés, en retard.
3. Consultez les listes « Tickets par statut » et « Tickets ouverts par priorité ».
4. Cliquez sur un compteur ou un statut pour ouvrir la liste filtrée.

### F2 – Affectation et changement de statut

1. Dans la liste, cliquez sur un ticket pour ouvrir sa fiche (`#/ticket/{id}`).
2. Choisissez un responsable dans la liste déroulante. Un ticket « Nouveau » passe seul à « Affecté ».
3. Choisissez un statut si besoin.
4. Cliquez sur « Enregistrer ». « Résolu » et « Fermé » mettent la résolution à « Corrigé » si elle était ouverte.

La fiche affiche aussi la description et l'historique des modifications.

### F3 – Échéances et retards (SLA)

1. À la création d'un ticket par l'API, l'échéance se calcule selon la priorité :

   | Priorité | Délai |
   |---|---|
   | Immédiate | 4 h |
   | Urgente | 1 j |
   | Élevée | 2 j |
   | Normale | 5 j |
   | Basse | 10 j |
   | Aucune | pas d'échéance |

   Une échéance fournie dans la requête reste prioritaire.
2. La colonne « Échéance » de la liste indique le délai restant ou le badge rouge « En retard ».
3. Activez « En retard uniquement » pour ne voir que les tickets ouverts dont l'échéance est dépassée.
4. La fiche du ticket affiche l'échéance et le badge.

Un ticket est en retard s'il n'est pas encore « Résolu » et si son échéance est passée.

## API

Toutes les réponses sont en JSON. Les erreurs ont la forme `{"erreur": "..."}`.

| Méthode | Chemin | Rôle |
|---|---|---|
| GET | `/api/meta` | Listes de valeurs, projets, catégories, utilisateurs |
| **GET** | **`/api/stats`** | **Compteurs : total, par statut, par priorité, non affectés, en retard (ajoutée)** |
| GET | `/api/tickets` | Liste. Filtres : `project_id`, `status`, `handler_id`, `q`, **`overdue=1` (ajouté)** |
| POST | `/api/tickets` | Création. **L'échéance se calcule selon la priorité (modifié)** |
| GET | `/api/tickets/{id}` | Fiche complète : texte, notes, historique |
| PATCH | `/api/tickets/{id}` | Modification partielle |
| DELETE | `/api/tickets/{id}` | Suppression |
| POST | `/api/tickets/{id}/notes` | Ajout d'une note |

## Lancer le projet

| | Windows (Git Bash) | macOS / Linux |
|---|---|---|
| Serveur | `outils/php/php.exe outils/serveur.php` | `php outils/serveur.php` |

Ouvrez ensuite http://localhost:8000. Dans l'application Claude, utilisez l'aperçu « atelier (Windows) » ou « atelier (Mac-Linux) ».
Pour repartir des données de démonstration, supprimez `data/mantis.sqlite`. La base se recrée au prochain appel.

## Tests

| Windows (Git Bash) | macOS / Linux |
|---|---|
| `outils/php/php.exe outils/tests/smoke.php` | `php outils/tests/smoke.php` |

Les tests de fumée comptent 36 tests. Ils utilisent une base temporaire.

## Limites connues

- Pas de formulaire de création de ticket : la création passe par l'API.
- Pas de saisie ni d'affichage des notes sur la fiche (l'API les gère).
- Pas de kanban, pas d'export CSV.
- Pas d'authentification : l'utilisateur affiché est toujours le premier de la base.
- Les tickets existants sans échéance (données de démonstration) n'apparaissent jamais en retard.
- L'échéance d'un ticket ne se recalcule pas quand sa priorité change.

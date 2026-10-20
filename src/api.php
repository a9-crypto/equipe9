<?php
/**
 * API JSON Mantis-lite.
 *
 *   GET    /api/meta                    listes de valeurs, projets, catégories, utilisateurs
 *   GET    /api/tickets                 liste (filtres : project_id, status, handler_id, q)
 *   POST   /api/tickets                 création
 *   GET    /api/tickets/{id}            fiche complète (texte, notes, historique)
 *   PATCH  /api/tickets/{id}            modification partielle
 *   DELETE /api/tickets/{id}            suppression
 *   POST   /api/tickets/{id}/notes      ajout d'une note
 *
 * api_handle() ne lit ni n'écrit HTTP : public/index.php fait le lien. Les tests l'appellent directement.
 */

declare(strict_types=1);

require_once __DIR__ . '/constantes.php';
require_once __DIR__ . '/db.php';

final class ApiError extends RuntimeException
{
    public function __construct(public readonly int $status, string $message)
    {
        parent::__construct($message);
    }
}

/**
 * Point d'entrée de l'API.
 * @return array{status:int, body:mixed}
 */
function api_handle(string $method, string $path, array $query = [], ?array $body = null, int $userId = 1): array
{
    try {
        $user = db_one('SELECT id FROM mantis_user_table WHERE id = ? AND enabled = 1', [$userId]);
        if ($user === null) {
            throw new ApiError(401, 'Utilisateur inconnu.');
        }

        $path = rtrim($path, '/');
        $body ??= [];

        if ($path === '/api/meta' && $method === 'GET') {
            return ok(meta());
        }
        if ($path === '/api/tickets') {
            return match ($method) {
                'GET' => ok(ticket_list($query)),
                'POST' => ok(ticket_create($body, $userId), 201),
                default => throw new ApiError(405, 'Méthode non autorisée.'),
            };
        }
        if (preg_match('#^/api/tickets/(\d+)$#', $path, $m)) {
            $id = (int) $m[1];
            return match ($method) {
                'GET' => ok(ticket_get($id)),
                'PATCH' => ok(ticket_update($id, $body, $userId)),
                'DELETE' => ok(ticket_delete($id)),
                default => throw new ApiError(405, 'Méthode non autorisée.'),
            };
        }
        if (preg_match('#^/api/tickets/(\d+)/notes$#', $path, $m) && $method === 'POST') {
            return ok(note_add((int) $m[1], $body, $userId), 201);
        }
        throw new ApiError(404, 'Ressource introuvable.');
    } catch (ApiError $e) {
        return ['status' => $e->status, 'body' => ['erreur' => $e->getMessage()]];
    } catch (Throwable $e) {
        error_log((string) $e);
        return ['status' => 500, 'body' => ['erreur' => 'Erreur interne du serveur.']];
    }
}

function ok(mixed $body, int $status = 200): array
{
    return ['status' => $status, 'body' => $body];
}

// ---------------------------------------------------------------------------
// Métadonnées
// ---------------------------------------------------------------------------

function meta(): array
{
    return [
        'enums' => mantis_enums(),
        'projects' => db_all('SELECT id, name, description FROM mantis_project_table WHERE enabled = 1 ORDER BY name'),
        'categories' => db_all('SELECT id, project_id, name FROM mantis_category_table ORDER BY name'),
        'users' => db_all('SELECT id, username, realname, access_level FROM mantis_user_table WHERE enabled = 1 ORDER BY realname'),
    ];
}

// ---------------------------------------------------------------------------
// Tickets
// ---------------------------------------------------------------------------

const TICKET_SELECT = <<<SQL
    SELECT b.id, b.project_id, p.name AS project, b.category_id, c.name AS category,
           b.reporter_id, r.realname AS reporter, b.handler_id, h.realname AS handler,
           b.priority, b.severity, b.reproducibility, b.status, b.resolution,
           b.summary, b.date_submitted, b.last_updated, b.due_date
    FROM mantis_bug_table b
    JOIN mantis_project_table p ON p.id = b.project_id
    LEFT JOIN mantis_category_table c ON c.id = b.category_id
    JOIN mantis_user_table r ON r.id = b.reporter_id
    LEFT JOIN mantis_user_table h ON h.id = b.handler_id
    SQL;

const TICKET_INT_FIELDS = ['id', 'project_id', 'category_id', 'reporter_id', 'handler_id', 'priority',
    'severity', 'reproducibility', 'status', 'resolution', 'date_submitted', 'last_updated', 'due_date'];

/** Convertit en entiers les colonnes numériques (SQLite renvoie parfois des chaînes). */
function ticket_cast(array $row): array
{
    foreach (TICKET_INT_FIELDS as $f) {
        if (array_key_exists($f, $row) && $row[$f] !== null) {
            $row[$f] = (int) $row[$f];
        }
    }
    return $row;
}

/** Filtres : project_id, status (liste séparée par des virgules), handler_id ("none" = non affecté), q (texte). */
function ticket_list(array $query): array
{
    $where = [];
    $params = [];

    if (isset($query['project_id']) && $query['project_id'] !== '') {
        $where[] = 'b.project_id = ?';
        $params[] = (int) $query['project_id'];
    }
    if (isset($query['status']) && $query['status'] !== '') {
        $statuses = array_map('intval', explode(',', (string) $query['status']));
        $where[] = 'b.status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
        array_push($params, ...$statuses);
    }
    if (isset($query['handler_id']) && $query['handler_id'] !== '') {
        if ($query['handler_id'] === 'none') {
            $where[] = 'b.handler_id IS NULL';
        } else {
            $where[] = 'b.handler_id = ?';
            $params[] = (int) $query['handler_id'];
        }
    }
    if (isset($query['q']) && trim((string) $query['q']) !== '') {
        $where[] = 'b.summary LIKE ?';
        $params[] = '%' . trim((string) $query['q']) . '%';
    }

    $sql = TICKET_SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY b.last_updated DESC';
    return array_map('ticket_cast', db_all($sql, $params));
}

function ticket_get(int $id): array
{
    $ticket = db_one(TICKET_SELECT . ' WHERE b.id = ?', [$id]);
    if ($ticket === null) {
        throw new ApiError(404, "Ticket $id introuvable.");
    }
    $text = db_one(
        'SELECT t.description, t.steps_to_reproduce, t.additional_information
         FROM mantis_bug_text_table t JOIN mantis_bug_table b ON b.bug_text_id = t.id WHERE b.id = ?',
        [$id]
    ) ?? [];

    $notes = db_all(
        'SELECT n.id, n.reporter_id, u.realname AS reporter, n.note, n.date_submitted
         FROM mantis_bugnote_table n JOIN mantis_user_table u ON u.id = n.reporter_id
         WHERE n.bug_id = ? ORDER BY n.date_submitted',
        [$id]
    );
    $history = db_all(
        'SELECT h.id, h.user_id, u.realname AS user, h.field_name, h.old_value, h.new_value, h.type, h.date_modified
         FROM mantis_bug_history_table h JOIN mantis_user_table u ON u.id = h.user_id
         WHERE h.bug_id = ? ORDER BY h.date_modified, h.id',
        [$id]
    );

    return ticket_cast($ticket) + $text + ['notes' => $notes, 'history' => $history];
}

function ticket_create(array $in, int $userId): array
{
    if (!isset($in['project_id'])) {
        throw new ApiError(422, 'Le projet est obligatoire.');
    }
    $data = validate_fields($in, true);
    $now = time();

    // Règle MantisBT : un ticket créé avec un responsable passe au statut « Affecté ».
    $status = !empty($data['handler_id']) ? STATUS_ASSIGNED : STATUS_NEW;

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $textId = db_exec(
            'INSERT INTO mantis_bug_text_table (description, steps_to_reproduce, additional_information) VALUES (?, ?, ?)',
            [$data['description'], $data['steps_to_reproduce'] ?? '', $data['additional_information'] ?? '']
        );
        $id = db_exec(
            'INSERT INTO mantis_bug_table (project_id, reporter_id, handler_id, category_id, priority, severity,
                reproducibility, status, resolution, summary, bug_text_id, date_submitted, last_updated, due_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['project_id'], $userId, $data['handler_id'] ?? null, $data['category_id'] ?? null,
                $data['priority'] ?? 30, $data['severity'] ?? 50, $data['reproducibility'] ?? 70,
                $status, RESOLUTION_OPEN, $data['summary'], $textId, $now, $now, $data['due_date'] ?? null,
            ]
        );
        history_add($id, $userId, '', '', '', HISTORY_NEW_BUG);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return ticket_get($id);
}

function ticket_update(int $id, array $in, int $userId): array
{
    $before = ticket_get($id);
    $data = validate_fields($in, false, $before);
    if ($data === []) {
        throw new ApiError(422, 'Aucun champ à modifier.');
    }

    // Changement de projet sans catégorie : l'ancienne catégorie n'appartient plus au projet.
    if (isset($data['project_id']) && $data['project_id'] !== $before['project_id'] && !array_key_exists('category_id', $data)) {
        $data['category_id'] = null;
    }

    // Règles MantisBT appliquées automatiquement
    if (array_key_exists('handler_id', $data) && $data['handler_id'] !== null
        && !isset($data['status']) && $before['status'] < STATUS_ASSIGNED) {
        $data['status'] = STATUS_ASSIGNED;
    }
    if (isset($data['status'])) {
        $resolution = $data['resolution'] ?? $before['resolution'];
        if ($data['status'] >= STATUS_RESOLVED && $resolution === RESOLUTION_OPEN) {
            $data['resolution'] = RESOLUTION_FIXED;
        }
        if ($data['status'] < STATUS_RESOLVED && $before['status'] >= STATUS_RESOLVED && !isset($data['resolution'])) {
            $data['resolution'] = 30; // REOPENED
        }
    }

    $textFields = ['description', 'steps_to_reproduce', 'additional_information'];
    $pdo = db();
    $pdo->beginTransaction();
    try {
        foreach ($data as $field => $value) {
            if (in_array($field, $textFields, true)) {
                db_exec("UPDATE mantis_bug_text_table SET $field = ?
                         WHERE id = (SELECT bug_text_id FROM mantis_bug_table WHERE id = ?)", [$value, $id]);
            } else {
                db_exec("UPDATE mantis_bug_table SET $field = ? WHERE id = ?", [$value, $id]);
            }
            $old = (string) ($before[$field] ?? '');
            if ($old !== (string) ($value ?? '')) {
                // Pour les textes longs, l'historique garde seulement le nom du champ.
                $isText = in_array($field, $textFields, true);
                history_add($id, $userId, $field, $isText ? '' : $old, $isText ? '' : (string) ($value ?? ''));
            }
        }
        db_exec('UPDATE mantis_bug_table SET last_updated = ? WHERE id = ?', [time(), $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return ticket_get($id);
}

function ticket_delete(int $id): array
{
    $row = db_one('SELECT bug_text_id FROM mantis_bug_table WHERE id = ?', [$id]);
    if ($row === null) {
        throw new ApiError(404, "Ticket $id introuvable.");
    }
    db_exec('DELETE FROM mantis_bug_table WHERE id = ?', [$id]);
    db_exec('DELETE FROM mantis_bug_text_table WHERE id = ?', [$row['bug_text_id']]);
    return ['supprime' => $id];
}

// ---------------------------------------------------------------------------
// Notes et historique
// ---------------------------------------------------------------------------

function note_add(int $bugId, array $in, int $userId): array
{
    ticket_get($bugId); // 404 si le ticket n'existe pas
    $note = trim((string) ($in['note'] ?? ''));
    if ($note === '') {
        throw new ApiError(422, 'La note est vide.');
    }
    if (mb_strlen($note) > 5000) {
        throw new ApiError(422, 'La note dépasse 5 000 caractères.');
    }
    $now = time();
    $id = db_exec(
        'INSERT INTO mantis_bugnote_table (bug_id, reporter_id, note, date_submitted) VALUES (?, ?, ?, ?)',
        [$bugId, $userId, $note, $now]
    );
    history_add($bugId, $userId, '', (string) $id, '', HISTORY_BUGNOTE_ADDED);
    db_exec('UPDATE mantis_bug_table SET last_updated = ? WHERE id = ?', [$now, $bugId]);
    return db_one('SELECT id, bug_id, reporter_id, note, date_submitted FROM mantis_bugnote_table WHERE id = ?', [$id]);
}

function history_add(int $bugId, int $userId, string $field, string $old, string $new, int $type = HISTORY_NORMAL): void
{
    db_exec(
        'INSERT INTO mantis_bug_history_table (user_id, bug_id, field_name, old_value, new_value, type, date_modified)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$userId, $bugId, $field, $old, $new, $type, time()]
    );
}

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

/**
 * Valide les champs reçus. Ignore les champs inconnus.
 * $creation = true : summary et description sont obligatoires.
 * $before : ticket existant (modification), pour contrôler la catégorie face au projet.
 */
function validate_fields(array $in, bool $creation, ?array $before = null): array
{
    $out = [];

    foreach (['summary' => 128, 'description' => 5000, 'steps_to_reproduce' => 5000, 'additional_information' => 5000] as $f => $max) {
        if (array_key_exists($f, $in)) {
            $v = trim((string) $in[$f]);
            if (mb_strlen($v) > $max) {
                throw new ApiError(422, "Le champ $f dépasse $max caractères.");
            }
            $out[$f] = $v;
        }
    }
    if ($creation || array_key_exists('summary', $out)) {
        if (($out['summary'] ?? '') === '') {
            throw new ApiError(422, 'Le résumé est obligatoire.');
        }
    }
    if ($creation && ($out['description'] ?? '') === '') {
        throw new ApiError(422, 'La description est obligatoire.');
    }

    if (array_key_exists('project_id', $in)) {
        $out['project_id'] = (int) $in['project_id'];
        if (db_one('SELECT id FROM mantis_project_table WHERE id = ?', [$out['project_id']]) === null) {
            throw new ApiError(422, 'Projet inconnu.');
        }
    }

    foreach (['priority', 'severity', 'reproducibility', 'status', 'resolution'] as $enum) {
        if (array_key_exists($enum, $in)) {
            $v = (int) $in[$enum];
            if (!in_array($v, mantis_enum_ids($enum), true)) {
                throw new ApiError(422, "Valeur invalide pour $enum.");
            }
            $out[$enum] = $v;
        }
    }

    if (array_key_exists('handler_id', $in)) {
        if ($in['handler_id'] === null || $in['handler_id'] === '') {
            $out['handler_id'] = null;
        } else {
            $out['handler_id'] = (int) $in['handler_id'];
            if (db_one('SELECT id FROM mantis_user_table WHERE id = ? AND enabled = 1', [$out['handler_id']]) === null) {
                throw new ApiError(422, 'Responsable inconnu.');
            }
        }
    }

    if (array_key_exists('category_id', $in)) {
        if ($in['category_id'] === null || $in['category_id'] === '') {
            $out['category_id'] = null;
        } else {
            $out['category_id'] = (int) $in['category_id'];
            $projectId = $out['project_id'] ?? $before['project_id'] ?? null;
            $cat = db_one('SELECT project_id FROM mantis_category_table WHERE id = ?', [$out['category_id']]);
            if ($cat === null || (int) $cat['project_id'] !== $projectId) {
                throw new ApiError(422, 'Catégorie inconnue pour ce projet.');
            }
        }
    }

    if (array_key_exists('due_date', $in)) {
        $out['due_date'] = ($in['due_date'] === null || $in['due_date'] === '') ? null : (int) $in['due_date'];
    }

    return $out;
}

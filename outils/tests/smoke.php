<?php
/**
 * Tests de fumée de l'API : php outils/tests/smoke.php
 * Ils utilisent une base temporaire. La base de travail (data/mantis.sqlite) reste intacte.
 * Ajoutez un test pour chaque nouvelle règle métier.
 */

declare(strict_types=1);

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atelier-test-' . getmypid() . '.sqlite';
@unlink($tmp);
putenv('ATELIER_DB=' . $tmp);
register_shutdown_function(fn() => @unlink($tmp));

require __DIR__ . '/../../src/api.php';

$echecs = 0;
$total = 0;

function verifie(string $nom, bool $condition, mixed $detail = null): void
{
    global $echecs, $total;
    $total++;
    if ($condition) {
        echo "  OK  $nom\n";
    } else {
        $echecs++;
        echo "  KO  $nom\n";
        if ($detail !== null) {
            echo '      ' . json_encode($detail, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
}

echo "Tests de fumée de l'API Mantis-lite\n";

// Métadonnées
$r = api_handle('GET', '/api/meta');
verifie('meta : statut 200', $r['status'] === 200, $r);
verifie('meta : 7 statuts MantisBT', count($r['body']['enums']['status']) === 7);
verifie('meta : projets présents', count($r['body']['projects']) >= 1);

// Liste et filtres
$r = api_handle('GET', '/api/tickets');
verifie('liste : statut 200', $r['status'] === 200, $r);
$nb = count($r['body']);
verifie('liste : données de démo chargées', $nb >= 10, $nb);
$r = api_handle('GET', '/api/tickets', ['status' => '10']);
verifie('filtre statut : seulement « Nouveau »', $r['body'] !== [] && array_unique(array_column($r['body'], 'status')) === [10]);
$r = api_handle('GET', '/api/tickets', ['handler_id' => 'none']);
verifie('filtre non affectés', $r['body'] !== [] && array_unique(array_column($r['body'], 'handler_id')) === [null]);

// Création
$r = api_handle('POST', '/api/tickets', [], ['summary' => 'x']);
verifie('création : projet obligatoire (422)', $r['status'] === 422, $r);
$r = api_handle('POST', '/api/tickets', [], ['project_id' => 1, 'summary' => 'Test']);
verifie('création : description obligatoire (422)', $r['status'] === 422, $r);
$r = api_handle('POST', '/api/tickets', [], [
    'project_id' => 1, 'summary' => 'Souris défectueuse', 'description' => 'Le clic droit ne répond plus.',
    'priority' => 40, 'category_id' => 1,
]);
verifie('création : statut 201', $r['status'] === 201, $r);
$id = $r['body']['id'] ?? 0;
verifie('création : statut « Nouveau »', ($r['body']['status'] ?? 0) === STATUS_NEW);
verifie('création : historique NEW_BUG', ($r['body']['history'][0]['type'] ?? -1) == HISTORY_NEW_BUG);

// Affectation : passage automatique au statut « Affecté »
$r = api_handle('PATCH', "/api/tickets/$id", [], ['handler_id' => 2]);
verifie('affectation : statut « Affecté »', ($r['body']['status'] ?? 0) === STATUS_ASSIGNED, $r);

// Résolution : passage automatique à « Corrigé »
$r = api_handle('PATCH', "/api/tickets/$id", [], ['status' => STATUS_RESOLVED]);
verifie('résolution : « Corrigé »', ($r['body']['resolution'] ?? 0) === RESOLUTION_FIXED, $r);

// Valeur invalide
$r = api_handle('PATCH', "/api/tickets/$id", [], ['priority' => 999]);
verifie('priorité invalide (422)', $r['status'] === 422, $r);

// Note
$r = api_handle('POST', "/api/tickets/$id/notes", [], ['note' => '  ']);
verifie('note vide refusée (422)', $r['status'] === 422, $r);
$r = api_handle('POST', "/api/tickets/$id/notes", [], ['note' => 'Souris remplacée.']);
verifie('note ajoutée (201)', $r['status'] === 201, $r);
$r = api_handle('GET', "/api/tickets/$id");
verifie('fiche : note visible', count($r['body']['notes'] ?? []) === 1, $r);

// Injection SQL : le texte est traité comme une donnée
$r = api_handle('GET', '/api/tickets', ['q' => "' OR 1=1 --"]);
verifie('recherche : injection sans effet', $r['status'] === 200 && $r['body'] === [], $r);

// Suppression
$r = api_handle('DELETE', "/api/tickets/$id");
verifie('suppression : 200', $r['status'] === 200, $r);
$r = api_handle('GET', "/api/tickets/$id");
verifie('suppression : ticket introuvable (404)', $r['status'] === 404, $r);

// Route inconnue
$r = api_handle('GET', '/api/inconnu');
verifie('route inconnue (404)', $r['status'] === 404, $r);

echo "\n" . ($total - $echecs) . "/$total tests réussis\n";
exit($echecs === 0 ? 0 : 1);

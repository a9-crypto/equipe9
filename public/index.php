<?php
/**
 * Routeur du serveur intégré PHP (php -S).
 *   /api/...          → API JSON (src/api.php)
 *   fichier existant  → servi tel quel (CSS, JS, images)
 *   autre chemin      → index.html (application Vue)
 */

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

if (str_starts_with($path, '/api/') || $path === '/api') {
    require __DIR__ . '/../src/api.php';

    $body = null;
    $raw = file_get_contents('php://input');
    if ($raw !== '' && $raw !== false) {
        $body = json_decode($raw, true);
        if (!is_array($body)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erreur' => 'Corps JSON invalide.'], JSON_UNESCAPED_UNICODE);
            return true;
        }
    }

    // Pas d'authentification dans le socle : l'en-tête X-User-Id simule l'utilisateur connecté (1 par défaut).
    $userId = (int) ($_SERVER['HTTP_X_USER_ID'] ?? 1);

    $res = api_handle($_SERVER['REQUEST_METHOD'] ?? 'GET', $path, $_GET, $body, $userId);
    http_response_code($res['status']);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($res['body'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return true;
}

header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__ . '/index.html');
return true;

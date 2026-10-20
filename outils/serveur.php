<?php
/**
 * Lance le serveur de développement : php outils/serveur.php
 * - Port : variable PORT (fournie par l'aperçu de l'app Claude), sinon 8000.
 * - Écoute sur 127.0.0.1 uniquement : le serveur reste invisible depuis le réseau.
 * - Sous Windows, charge les extensions du PHP portable (outils/php/ext).
 */

declare(strict_types=1);

$racine = dirname(__DIR__);
$port = (int) (getenv('PORT') ?: 8000);

$options = '';
if (PHP_OS_FAMILY === 'Windows' && !extension_loaded('pdo_sqlite')) {
    $ext = __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'ext';
    $options = ' -d ' . escapeshellarg('extension_dir=' . $ext)
        . ' -d extension=pdo_sqlite -d extension=mbstring';
}

$cmd = escapeshellarg(PHP_BINARY) . $options
    . ' -S 127.0.0.1:' . $port
    . ' -t ' . escapeshellarg($racine . DIRECTORY_SEPARATOR . 'public')
    . ' ' . escapeshellarg($racine . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

fwrite(STDERR, "Atelier ticketing : http://127.0.0.1:$port (Ctrl+C pour arrêter)\n");
passthru($cmd, $code);
exit($code);

<?php
/**
 * Accès à la base SQLite.
 * La base est créée au premier appel à partir de db/schema.sql et db/seed.sql.
 * Variable d'environnement ATELIER_DB : chemin d'une autre base (tests).
 */

declare(strict_types=1);

const RACINE = __DIR__ . '/..';

function db_path(): string
{
    return getenv('ATELIER_DB') ?: RACINE . '/data/mantis.sqlite';
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = db_path();
    $nouvelle = $path === ':memory:' || !is_file($path);
    if ($path !== ':memory:' && !is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    if ($nouvelle) {
        db_init($pdo);
    }
    return $pdo;
}

/** Crée les tables et charge les données de démonstration. */
function db_init(PDO $pdo): void
{
    $pdo->exec(file_get_contents(RACINE . '/db/schema.sql'));
    $pdo->exec(file_get_contents(RACINE . '/db/seed.sql'));
}

/** Exécute une requête préparée et renvoie toutes les lignes. */
function db_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Exécute une requête préparée et renvoie la première ligne, ou null. */
function db_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

/** Exécute une requête préparée d'écriture. Renvoie le dernier identifiant inséré. */
function db_exec(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return (int) db()->lastInsertId();
}

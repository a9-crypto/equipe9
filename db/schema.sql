-- Schéma Mantis-lite (SQLite)
-- Tables et codes repris de MantisBT (https://mantisbt.org), version simplifiée pour l'atelier.
-- Dates : timestamps Unix (secondes), comme dans MantisBT.

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS mantis_user_table (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT    NOT NULL UNIQUE,
    realname      TEXT    NOT NULL DEFAULT '',
    email         TEXT    NOT NULL DEFAULT '',
    access_level  INTEGER NOT NULL DEFAULT 10,   -- VIEWER=10 ... ADMINISTRATOR=90
    enabled       INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS mantis_project_table (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL UNIQUE,
    description  TEXT    NOT NULL DEFAULT '',
    enabled      INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS mantis_category_table (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id  INTEGER NOT NULL REFERENCES mantis_project_table(id),
    name        TEXT    NOT NULL
);

-- Texte long d'un ticket, séparé du ticket comme dans MantisBT
CREATE TABLE IF NOT EXISTS mantis_bug_text_table (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    description             TEXT NOT NULL DEFAULT '',
    steps_to_reproduce      TEXT NOT NULL DEFAULT '',
    additional_information  TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS mantis_bug_table (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id       INTEGER NOT NULL REFERENCES mantis_project_table(id),
    reporter_id      INTEGER NOT NULL REFERENCES mantis_user_table(id),
    handler_id       INTEGER          REFERENCES mantis_user_table(id),  -- NULL = non affecté
    category_id      INTEGER          REFERENCES mantis_category_table(id),
    priority         INTEGER NOT NULL DEFAULT 30,  -- NORMAL
    severity         INTEGER NOT NULL DEFAULT 50,  -- MINOR
    reproducibility  INTEGER NOT NULL DEFAULT 70,  -- HAVENOTTRIED
    status           INTEGER NOT NULL DEFAULT 10,  -- NEW_
    resolution       INTEGER NOT NULL DEFAULT 10,  -- OPEN
    summary          TEXT    NOT NULL,
    bug_text_id      INTEGER NOT NULL REFERENCES mantis_bug_text_table(id),
    date_submitted   INTEGER NOT NULL,
    last_updated     INTEGER NOT NULL,
    due_date         INTEGER                        -- échéance, NULL = aucune
);

CREATE INDEX IF NOT EXISTS idx_bug_status  ON mantis_bug_table(status);
CREATE INDEX IF NOT EXISTS idx_bug_project ON mantis_bug_table(project_id);
CREATE INDEX IF NOT EXISTS idx_bug_handler ON mantis_bug_table(handler_id);

-- Notes (commentaires) d'un ticket. MantisBT sépare le texte : ici, il est en ligne.
CREATE TABLE IF NOT EXISTS mantis_bugnote_table (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    bug_id          INTEGER NOT NULL REFERENCES mantis_bug_table(id) ON DELETE CASCADE,
    reporter_id     INTEGER NOT NULL REFERENCES mantis_user_table(id),
    note            TEXT    NOT NULL,
    date_submitted  INTEGER NOT NULL
);

-- Historique des modifications d'un ticket
CREATE TABLE IF NOT EXISTS mantis_bug_history_table (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL REFERENCES mantis_user_table(id),
    bug_id         INTEGER NOT NULL REFERENCES mantis_bug_table(id) ON DELETE CASCADE,
    field_name     TEXT    NOT NULL DEFAULT '',
    old_value      TEXT    NOT NULL DEFAULT '',
    new_value      TEXT    NOT NULL DEFAULT '',
    type           INTEGER NOT NULL DEFAULT 0,      -- NORMAL_TYPE=0, NEW_BUG=1, BUGNOTE_ADDED=2
    date_modified  INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_history_bug ON mantis_bug_history_table(bug_id);

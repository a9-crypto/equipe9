<?php
/**
 * Constantes Mantis-lite.
 * Les codes numériques viennent de MantisBT (core/constant_inc.php).
 * Les libellés et les couleurs servent à l'interface.
 */

declare(strict_types=1);

// Niveaux d'accès
const VIEWER = 10;
const REPORTER = 25;
const UPDATER = 40;
const DEVELOPER = 55;
const MANAGER = 70;
const ADMINISTRATOR = 90;

// Statuts
const STATUS_NEW = 10;          // NEW_ dans MantisBT
const STATUS_FEEDBACK = 20;
const STATUS_ACKNOWLEDGED = 30;
const STATUS_CONFIRMED = 40;
const STATUS_ASSIGNED = 50;
const STATUS_RESOLVED = 80;
const STATUS_CLOSED = 90;

// Résolutions
const RESOLUTION_OPEN = 10;
const RESOLUTION_FIXED = 20;

// Types d'historique
const HISTORY_NORMAL = 0;
const HISTORY_NEW_BUG = 1;
const HISTORY_BUGNOTE_ADDED = 2;

/** Libellés et couleurs par liste de valeurs. Les couleurs de statut reprennent celles de MantisBT. */
function mantis_enums(): array
{
    return [
        'status' => [
            ['id' => 10, 'code' => 'new',          'label' => 'Nouveau',       'color' => '#fcbdbd'],
            ['id' => 20, 'code' => 'feedback',     'label' => 'Commentaire',   'color' => '#e3b7eb'],
            ['id' => 30, 'code' => 'acknowledged', 'label' => 'Accepté',       'color' => '#ffcd85'],
            ['id' => 40, 'code' => 'confirmed',    'label' => 'Confirmé',      'color' => '#fff494'],
            ['id' => 50, 'code' => 'assigned',     'label' => 'Affecté',       'color' => '#c2dfff'],
            ['id' => 80, 'code' => 'resolved',     'label' => 'Résolu',        'color' => '#d2f5b0'],
            ['id' => 90, 'code' => 'closed',       'label' => 'Fermé',         'color' => '#c9ccc4'],
        ],
        'priority' => [
            ['id' => 10, 'code' => 'none',      'label' => 'Aucune'],
            ['id' => 20, 'code' => 'low',       'label' => 'Basse'],
            ['id' => 30, 'code' => 'normal',    'label' => 'Normale'],
            ['id' => 40, 'code' => 'high',      'label' => 'Élevée'],
            ['id' => 50, 'code' => 'urgent',    'label' => 'Urgente'],
            ['id' => 60, 'code' => 'immediate', 'label' => 'Immédiate'],
        ],
        'severity' => [
            ['id' => 10, 'code' => 'feature', 'label' => 'Fonctionnalité'],
            ['id' => 20, 'code' => 'trivial', 'label' => 'Simple'],
            ['id' => 30, 'code' => 'text',    'label' => 'Texte'],
            ['id' => 40, 'code' => 'tweak',   'label' => 'Ajustement'],
            ['id' => 50, 'code' => 'minor',   'label' => 'Mineur'],
            ['id' => 60, 'code' => 'major',   'label' => 'Majeur'],
            ['id' => 70, 'code' => 'crash',   'label' => 'Plantage'],
            ['id' => 80, 'code' => 'block',   'label' => 'Bloquant'],
        ],
        'resolution' => [
            ['id' => 10, 'code' => 'open',                'label' => 'Ouvert'],
            ['id' => 20, 'code' => 'fixed',               'label' => 'Corrigé'],
            ['id' => 30, 'code' => 'reopened',            'label' => 'Réouvert'],
            ['id' => 40, 'code' => 'unable_to_reproduce', 'label' => 'Impossible à reproduire'],
            ['id' => 50, 'code' => 'not_fixable',         'label' => 'Impossible à corriger'],
            ['id' => 60, 'code' => 'duplicate',           'label' => 'Doublon'],
            ['id' => 70, 'code' => 'no_change_required',  'label' => 'Aucune modification requise'],
            ['id' => 80, 'code' => 'suspended',           'label' => 'Suspendu'],
            ['id' => 90, 'code' => 'wont_fix',            'label' => 'Ne sera pas corrigé'],
        ],
        'reproducibility' => [
            ['id' => 10,  'code' => 'always',            'label' => 'Toujours'],
            ['id' => 30,  'code' => 'sometimes',         'label' => 'Parfois'],
            ['id' => 50,  'code' => 'random',            'label' => 'Aléatoire'],
            ['id' => 70,  'code' => 'have_not_tried',    'label' => 'Non essayé'],
            ['id' => 90,  'code' => 'unable_to_reproduce', 'label' => 'Impossible à reproduire'],
            ['id' => 100, 'code' => 'not_applicable',    'label' => 'Sans objet'],
        ],
        'access_level' => [
            ['id' => 10, 'code' => 'viewer',        'label' => 'Observateur'],
            ['id' => 25, 'code' => 'reporter',      'label' => 'Rapporteur'],
            ['id' => 40, 'code' => 'updater',       'label' => 'Contributeur'],
            ['id' => 55, 'code' => 'developer',     'label' => 'Technicien'],
            ['id' => 70, 'code' => 'manager',       'label' => 'Responsable'],
            ['id' => 90, 'code' => 'administrator', 'label' => 'Administrateur'],
        ],
    ];
}

/** Liste des identifiants valides pour une liste de valeurs. */
function mantis_enum_ids(string $enum): array
{
    return array_column(mantis_enums()[$enum] ?? [], 'id');
}

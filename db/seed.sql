-- Données de démonstration Mantis-lite (contexte support informatique, sans donnée réelle)
-- Les dates sont relatives au moment de création de la base.

INSERT INTO mantis_user_table (id, username, realname, email, access_level) VALUES
  (1, 'admin',     'Administrateur Atelier', 'admin@atelier.local',     90),
  (2, 'c.martin',  'Camille Martin',         'c.martin@atelier.local',  55),
  (3, 'a.bernard', 'Alex Bernard',           'a.bernard@atelier.local', 55),
  (4, 'l.petit',   'Lou Petit',              'l.petit@atelier.local',   70),
  (5, 'j.durand',  'Jordan Durand',          'j.durand@atelier.local',  25),
  (6, 's.moreau',  'Sacha Moreau',           's.moreau@atelier.local',  25);

INSERT INTO mantis_project_table (id, name, description) VALUES
  (1, 'Support poste de travail', 'Matériel, logiciels et comptes des postes utilisateurs'),
  (2, 'Réseau et télécoms',       'VPN, Wi-Fi, téléphonie et accès réseau'),
  (3, 'Applications métier',      'Messagerie, RH, gestion documentaire');

INSERT INTO mantis_category_table (id, project_id, name) VALUES
  (1, 1, 'Matériel'), (2, 1, 'Logiciel'), (3, 1, 'Compte et accès'),
  (4, 2, 'VPN'), (5, 2, 'Wi-Fi'), (6, 2, 'Téléphonie'),
  (7, 3, 'Messagerie'), (8, 3, 'RH'), (9, 3, 'Gestion documentaire');

INSERT INTO mantis_bug_text_table (id, description, steps_to_reproduce, additional_information) VALUES
  (1,  'L''imprimante du 2e étage affiche « hors ligne » sur tous les postes.', '1. Lancer une impression' || char(10) || '2. Le travail reste en file d''attente', 'Le redémarrage de l''imprimante ne change rien.'),
  (2,  'Le VPN se déconnecte toutes les 10 minutes environ.', '1. Se connecter au VPN' || char(10) || '2. Attendre 10 minutes', 'Constaté depuis la dernière mise à jour du client VPN.'),
  (3,  'Demande d''accès à l''application RH pour un nouvel arrivant.', '', 'Arrivée prévue lundi prochain.'),
  (4,  'Écran noir au démarrage du poste, le voyant de l''unité centrale reste orange.', '1. Allumer le poste', ''),
  (5,  'La boîte de messagerie est pleine, impossible d''envoyer des courriels.', '1. Envoyer un courriel' || char(10) || '2. Message d''erreur « quota dépassé »', ''),
  (6,  'Le Wi-Fi de la salle de réunion B est très lent.', '1. Se connecter au Wi-Fi « Reunion-B »' || char(10) || '2. Lancer un test de débit', 'Débit mesuré : 2 Mb/s.'),
  (7,  'Installer le logiciel de cartographie sur 3 postes du bureau études.', '', 'Licences déjà achetées.'),
  (8,  'Le téléphone fixe du poste 4127 ne sonne plus.', '1. Appeler le 4127 depuis un autre poste', 'Le téléphone affiche bien la ligne.'),
  (9,  'La recherche plein texte de la GED ne trouve pas les documents récents.', '1. Déposer un document' || char(10) || '2. Le rechercher par un mot du titre', 'Les documents de plus d''une semaine sont trouvés.'),
  (10, 'Mot de passe expiré, compte verrouillé après trois tentatives.', '', 'Utilisateur en déplacement.'),
  (11, 'Clavier du poste d''accueil : plusieurs touches ne répondent plus.', '', ''),
  (12, 'Les bulletins de paie du mois ne s''affichent pas dans l''espace RH.', '1. Ouvrir l''espace RH' || char(10) || '2. Cliquer sur « Mes bulletins »', 'Page blanche, aucune erreur affichée.'),
  (13, 'Demande de création d''une liste de diffusion pour l''équipe projet.', '', '12 membres, liste fournie en pièce jointe du courriel.'),
  (14, 'Le poste redémarre seul pendant les mises à jour, perte de travail.', '', 'Résolu par report des mises à jour en dehors des heures de travail.');

INSERT INTO mantis_bug_table
  (id, project_id, reporter_id, handler_id, category_id, priority, severity, reproducibility, status, resolution, summary, bug_text_id, date_submitted, last_updated, due_date) VALUES
  (1,  1, 5, 2,    1, 40, 60, 10, 50, 10, 'Imprimante du 2e étage hors ligne',               1,  CAST(strftime('%s','now') AS INTEGER) - 3*86400,  CAST(strftime('%s','now') AS INTEGER) - 2*86400,  CAST(strftime('%s','now') AS INTEGER) + 1*86400),
  (2,  2, 6, 3,    4, 50, 70, 30, 50, 10, 'VPN : déconnexion toutes les 10 minutes',         2,  CAST(strftime('%s','now') AS INTEGER) - 5*86400,  CAST(strftime('%s','now') AS INTEGER) - 1*86400,  CAST(strftime('%s','now') AS INTEGER) - 1*86400),
  (3,  3, 4, NULL, 8, 30, 10, 100, 10, 10, 'Accès à l''application RH pour un nouvel arrivant', 3, CAST(strftime('%s','now') AS INTEGER) - 1*86400,  CAST(strftime('%s','now') AS INTEGER) - 1*86400,  CAST(strftime('%s','now') AS INTEGER) + 4*86400),
  (4,  1, 5, NULL, 1, 50, 80, 10, 10, 10, 'Écran noir au démarrage du poste',                4,  CAST(strftime('%s','now') AS INTEGER) - 2*3600,   CAST(strftime('%s','now') AS INTEGER) - 2*3600,   NULL),
  (5,  3, 6, 2,    7, 40, 60, 10, 30, 10, 'Boîte de messagerie pleine',                      5,  CAST(strftime('%s','now') AS INTEGER) - 2*86400,  CAST(strftime('%s','now') AS INTEGER) - 1*86400,  NULL),
  (6,  2, 5, NULL, 5, 30, 50, 30, 20, 10, 'Wi-Fi lent en salle de réunion B',                6,  CAST(strftime('%s','now') AS INTEGER) - 6*86400,  CAST(strftime('%s','now') AS INTEGER) - 4*86400,  NULL),
  (7,  1, 4, 3,    2, 20, 10, 100, 40, 10, 'Installer le logiciel de cartographie (3 postes)', 7, CAST(strftime('%s','now') AS INTEGER) - 8*86400,  CAST(strftime('%s','now') AS INTEGER) - 7*86400,  CAST(strftime('%s','now') AS INTEGER) + 7*86400),
  (8,  2, 6, 3,    6, 30, 50, 10, 80, 20, 'Téléphone du poste 4127 muet',                    8,  CAST(strftime('%s','now') AS INTEGER) - 10*86400, CAST(strftime('%s','now') AS INTEGER) - 3*86400,  NULL),
  (9,  3, 5, 2,    9, 30, 50, 30, 40, 10, 'GED : recherche sans résultats récents',          9,  CAST(strftime('%s','now') AS INTEGER) - 4*86400,  CAST(strftime('%s','now') AS INTEGER) - 4*86400,  NULL),
  (10, 1, 6, 2,    3, 60, 80, 100, 90, 20, 'Compte verrouillé après expiration du mot de passe', 10, CAST(strftime('%s','now') AS INTEGER) - 12*86400, CAST(strftime('%s','now') AS INTEGER) - 12*86400, NULL),
  (11, 1, 5, NULL, 1, 20, 50, 10, 10, 10, 'Clavier du poste d''accueil défectueux',          11, CAST(strftime('%s','now') AS INTEGER) - 5*3600,   CAST(strftime('%s','now') AS INTEGER) - 5*3600,   NULL),
  (12, 3, 6, 3,    8, 50, 70, 10, 50, 10, 'Bulletins de paie non affichés',                  12, CAST(strftime('%s','now') AS INTEGER) - 1*86400,  CAST(strftime('%s','now') AS INTEGER) - 6*3600,   CAST(strftime('%s','now') AS INTEGER) + 1*86400),
  (13, 3, 4, NULL, 7, 20, 10, 100, 10, 10, 'Créer une liste de diffusion équipe projet',     13, CAST(strftime('%s','now') AS INTEGER) - 3*3600,   CAST(strftime('%s','now') AS INTEGER) - 3*3600,   NULL),
  (14, 1, 5, 2,    2, 30, 60, 50, 90, 20, 'Redémarrages forcés pendant les mises à jour',    14, CAST(strftime('%s','now') AS INTEGER) - 20*86400, CAST(strftime('%s','now') AS INTEGER) - 15*86400, NULL);

INSERT INTO mantis_bugnote_table (bug_id, reporter_id, note, date_submitted) VALUES
  (1,  2, 'Prise en charge. Le pilote réseau de l''imprimante semble en cause.', CAST(strftime('%s','now') AS INTEGER) - 2*86400),
  (2,  3, 'Je reproduis le problème. Le serveur VPN coupe les sessions inactives.', CAST(strftime('%s','now') AS INTEGER) - 4*86400),
  (2,  6, 'Toujours présent ce matin, même en activité.', CAST(strftime('%s','now') AS INTEGER) - 1*86400),
  (5,  2, 'Archivage des courriels de plus de deux ans proposé à l''utilisateur.', CAST(strftime('%s','now') AS INTEGER) - 1*86400),
  (6,  4, 'Pouvez-vous préciser l''heure des mesures de débit ?', CAST(strftime('%s','now') AS INTEGER) - 4*86400),
  (8,  3, 'Câble remplacé. Merci de confirmer le bon fonctionnement.', CAST(strftime('%s','now') AS INTEGER) - 3*86400),
  (10, 2, 'Compte déverrouillé, mot de passe temporaire transmis par téléphone.', CAST(strftime('%s','now') AS INTEGER) - 12*86400);

INSERT INTO mantis_bug_history_table (user_id, bug_id, field_name, old_value, new_value, type, date_modified)
  SELECT reporter_id, id, '', '', '', 1, date_submitted FROM mantis_bug_table;

INSERT INTO mantis_bug_history_table (user_id, bug_id, field_name, old_value, new_value, type, date_modified) VALUES
  (4, 1,  'status', '10', '50', 0, CAST(strftime('%s','now') AS INTEGER) - 2*86400),
  (4, 2,  'status', '10', '50', 0, CAST(strftime('%s','now') AS INTEGER) - 4*86400),
  (3, 8,  'status', '50', '80', 0, CAST(strftime('%s','now') AS INTEGER) - 3*86400),
  (2, 10, 'status', '50', '90', 0, CAST(strftime('%s','now') AS INTEGER) - 12*86400),
  (2, 14, 'status', '80', '90', 0, CAST(strftime('%s','now') AS INTEGER) - 15*86400);

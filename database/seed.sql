-- Données de démonstration : 6e édition (2026)
SET NAMES utf8mb4;

INSERT INTO editions (id, year, label, starts_on, ends_on, is_current) VALUES
  (1, 2021, '1re édition', '2021-07-01', '2021-08-30', 0),
  (2, 2022, '2e édition',  '2022-07-01', '2022-08-30', 0),
  (3, 2023, '3e édition',  '2023-07-01', '2023-08-30', 0),
  (4, 2024, '4e édition',  '2024-07-01', '2024-08-30', 0),
  (5, 2025, '5e édition',  '2025-07-01', '2025-08-30', 0),
  (6, 2026, '6e édition',  '2026-07-01', '2026-08-24', 1);

INSERT INTO competitions (id, edition_id, name, slug, type, format, color, qualify_slots) VALUES
  (1, 6, 'Championnat de vacances',       'championnat', 'league',   '10 équipes · 18 journées · aller simple',      '#7a1c2a', 2),
  (2, 6, 'Grand Prix Gabriel Mbaïrobé',   'grand-prix',  'cup',      'Élimination directe · 8 qualifiés',            '#e8720c', 0),
  (3, 6, 'Super Coupe',                   'super-coupe', 'supercup', 'Champion contre vainqueur du Grand Prix',      '#9e7820', 0);

INSERT INTO phases (competition_id, name, ord, status) VALUES
  (1, 'Phase régulière (J1–J9)', 1, 'running'),
  (1, 'Barrages 5e–8e',          2, 'planned'),
  (1, 'Playoffs',                3, 'planned'),
  (2, 'Tour préliminaire',       1, 'done'),
  (2, 'Quarts de finale',        2, 'done'),
  (2, 'Demi-finales',            3, 'done'),
  (2, 'Finale · 22 août',        4, 'planned'),
  (3, 'Qualifiés désignés',      1, 'planned'),
  (3, 'Finale · 24 août',        2, 'planned');

INSERT INTO venues (id, name, city, capacity) VALUES
  (1, 'Stade Roumdé Adjia',        'Garoua',      22000),
  (2, 'Complexe de Bibémiré',      'Garoua',       3000),
  (3, 'Terrain de Djamboutou',     'Garoua',       1500),
  (4, 'Stade municipal de Pitoa',  'Pitoa',        2000),
  (5, 'Stade de Guider',           'Guider',       2500);

INSERT INTO teams (id, edition_id, name, short_name, city, coach, founded, color_primary, color_secondary, status, description) VALUES
  (1,  6, 'Étoile du Nord',     'EDN', 'Garoua',      'Ibrahim Moussa',   2021, '#e8720c', '#1a0c0e', 'validated', "L'une des équipes les plus titrées du GFC, connue pour son pressing intense et ses attaquants rapides."),
  (2,  6, 'Lions de Garoua',    'LDG', 'Garoua',      'Alioum Boukar',    2020, '#7a1c2a', '#e8720c', 'validated', "Fondateurs du GFC et vainqueurs de la première édition."),
  (3,  6, 'FC Bénoué',          'FCB', 'Garoua',      'Christophe Nana',  2021, '#1565c0', '#ffffff', 'validated', "Une équipe technique qui s'appuie sur un jeu de possession."),
  (4,  6, 'Diamants FC',        'DFC', 'Garoua',      'Théodore Wabo',    2022, '#2e7d32', '#f5f5f5', 'validated', "Révélation de l'édition 2022, les Diamants misent sur la jeunesse."),
  (5,  6, 'Tornado Ngaoundéré', 'TNG', 'Ngaoundéré',  'Salif Ouedraogo',  2023, '#6a1b9a', '#ffffff', 'pending',   "Représentant de Ngaoundéré, une rivalité régionale passionnante."),
  (6,  6, 'United Pitoa',       'UPT', 'Pitoa',       'Darius Kamga',     2022, '#e65100', '#1a1a1a', 'validated', "Club de la ville de Pitoa, une défense solide."),
  (7,  6, 'AS Guider',          'ASG', 'Guider',      'Moïse Noubissie',  2023, '#01579b', '#fdd835', 'validated', "Néophyte courageux du GFC, l'AS Guider progresse édition après édition."),
  (8,  6, 'FC Figuil',          'FCF', 'Figuil',      'Etienne Dinga',    2024, '#880e4f', '#ffffff', 'pending',   "Nouvelle recrue du GFC, FC Figuil découvre la compétition."),
  (9,  6, 'Nomades FC',         'NFC', 'Garoua',      'Pascal Enow',      2023, '#37474f', '#cfd8dc', 'validated', "Une équipe au style nomade, toujours en quête de résultats."),
  (10, 6, 'Racing Garoua',      'RCG', 'Garoua',      'Ferdinand Nlend',  2024, '#bf360c', '#ffeb3b', 'validated', "Dernier au classement mais premier dans le cœur des supporters du quartier.");

INSERT INTO team_competition (team_id, competition_id)
  SELECT id, 1 FROM teams WHERE edition_id = 6;
INSERT INTO team_competition (team_id, competition_id) VALUES
  (1,2),(2,2),(3,2),(4,2),(6,2),(7,2),(9,2),(10,2),(1,3),(2,3);

INSERT INTO players (team_id, first_name, last_name, position, shirt_no, license_status) VALUES
  (1,'Adamou','Maïga','ATT',9,'valid'),(1,'Hamidou','Yaya','MIL',8,'valid'),(1,'Barka','Ali','DEF',4,'valid'),(1,'Oumar','Sali','DEF',5,'valid'),(1,'Mahamat','Djidda','GB',1,'valid'),
  (2,'Bello','Hamadou','MIL',10,'valid'),(2,'Saidou','Njoya','ATT',11,'valid'),(2,'Issa','Ngaroua','DEF',3,'valid'),(2,'Vidal','Mbaye','MIL',6,'valid'),(2,'Arouna','Diko','GB',1,'valid'),
  (3,'Moussa','Djibrine','ATT',7,'valid'),(3,'Patrick','Nkolo','MIL',8,'valid'),(3,'Ahmed','Garba','DEF',2,'valid'),(3,'Junior','Tchoupo','MIL',10,'valid'),(3,'Raoul','Mengue','GB',1,'valid'),
  (4,'Youssouf','Issa','DEF',5,'valid'),(4,'Franck','Momo','ATT',9,'valid'),(4,'Serge','Ngono','MIL',6,'valid'),(4,'David','Ateba','DEF',3,'valid'),(4,'Boris','Kana','GB',1,'valid'),
  (5,'Ali','Mbodj','MIL',8,'missing'),(5,'Kalil','Hamza','ATT',9,'missing'),(5,'Cheick','Diallo','DEF',4,'pending'),(5,'Eric','Tchibozo','MIL',7,'valid'),(5,'Samuel','Mvogo','GB',1,'missing'),
  (6,'Jules','Mfou','ATT',9,'valid'),(6,'Romain','Biya','DEF',4,'valid'),(6,'Steve','Nkoa','MIL',8,'valid'),
  (7,'Gilles','Tsafack','MIL',6,'valid'),(7,'Bruno','Amvela','ATT',11,'valid'),(7,'Thierry','Njike','DEF',5,'valid'),
  (8,'Marcel','Ekoa','ATT',10,'pending'),(8,'Roméo','Akoa','MIL',8,'pending'),
  (9,'Alexis','Mbe','DEF',3,'valid'),(9,'Norbert','Ayissi','MIL',7,'valid'),
  (10,'William','Banga','ATT',9,'valid'),(10,'Oscar','Eto','MIL',10,'valid');

-- Mot de passe de démonstration : gfc2026
INSERT INTO users (id, name, phone, email, password_hash, role, team_id, status) VALUES
  (1, 'Gabriel Ndjidda',  '+237690000001', 'admin@gfc.cm',   '$2y$10$TvFdYz9ofjADyU5CmQxFPuWH4PzimSAxzpWzjHTO06jbB8vggE52q', 'admin',    NULL, 'active'),
  (2, 'Ibrahim Moussa',   '+237677000002', NULL,             '$2y$10$TvFdYz9ofjADyU5CmQxFPuWH4PzimSAxzpWzjHTO06jbB8vggE52q', 'delegate', 1,    'active'),
  (3, 'Hamadou Oumarou',  '+237696000003', NULL,             '$2y$10$TvFdYz9ofjADyU5CmQxFPuWH4PzimSAxzpWzjHTO06jbB8vggE52q', 'referee',  NULL, 'active'),
  (4, 'Aboubakar Sanda',  '+237671000004', NULL,             '$2y$10$TvFdYz9ofjADyU5CmQxFPuWH4PzimSAxzpWzjHTO06jbB8vggE52q', 'referee',  NULL, 'active'),
  (5, 'Marlyse Tchana',   '+237699000005', 'presse@gfc.cm',  '$2y$10$TvFdYz9ofjADyU5CmQxFPuWH4PzimSAxzpWzjHTO06jbB8vggE52q', 'editor',   NULL, 'invited'),
  (6, 'Alioum Boukar',    '+237655000006', NULL,             '$2y$10$TvFdYz9ofjADyU5CmQxFPuWH4PzimSAxzpWzjHTO06jbB8vggE52q', 'delegate', 2,    'active');

INSERT INTO matches (id, competition_id, phase_id, matchday, home_team_id, away_team_id, venue_id, referee_id, kickoff_at, status, minute, home_score, away_score, sheet_status) VALUES
  (1, 2, 5, NULL, 1, 2, 1, 4, '2026-08-02 16:00:00', 'finished', 90, 2, 1, 'validated'),
  (2, 1, 1, 6,    3, 4, 2, 3, '2026-08-02 18:30:00', 'live',     62, 1, 1, 'draft'),
  (3, 1, 1, 6,    5, 6, 4, 4, '2026-08-03 16:00:00', 'scheduled', NULL, NULL, NULL, 'draft'),
  (4, 2, 6, NULL, 2, 7, 1, NULL, '2026-08-03 18:30:00', 'scheduled', NULL, NULL, NULL, 'draft'),
  (5, 3, 9, NULL, 10, 8, 3, 4, '2026-08-04 17:00:00', 'scheduled', NULL, NULL, NULL, 'draft'),
  (6, 1, 1, 5,    9, 3, 3, 3, '2026-08-01 17:00:00', 'finished', 90, 0, 2, 'submitted'),
  (7, 1, 1, 5,    1, 10, 1, 4, '2026-07-30 17:00:00', 'finished', 90, 3, 0, 'validated'),
  (8, 1, 1, 4,    4, 7, 2, 3, '2026-07-27 17:00:00', 'finished', 90, 2, 2, 'validated');

INSERT INTO match_events (match_id, minute, type, team_id, player_id, note) VALUES
  (2, 9,  'goal',   3, 11, 'Frappe du gauche à l''entrée de la surface'),
  (2, 23, 'yellow', 4, 18, 'Faute sur contre-attaque'),
  (2, 41, 'goal',   4, 17, 'Reprise de volée sur corner'),
  (2, 45, 'period', NULL, NULL, 'Mi-temps : 1 – 1'),
  (2, 58, 'sub',    3, 14, 'Sortie de Junior Tchoupo'),
  (2, 62, 'shot',   3, 12, 'Tir cadré repoussé par le gardien'),
  (1, 12, 'goal',   1, 1,  NULL),
  (1, 34, 'goal',   1, 2,  NULL),
  (1, 71, 'goal',   2, 7,  NULL),
  (7, 21, 'goal',   1, 1,  NULL),
  (7, 55, 'goal',   1, 1,  NULL),
  (7, 80, 'goal',   1, 2,  NULL),
  (6, 30, 'goal',   3, 11, NULL),
  (6, 66, 'goal',   3, 12, NULL),
  (8, 15, 'goal',   4, 17, NULL),
  (8, 49, 'goal',   4, 17, NULL),
  (8, 60, 'goal',   7, 30, NULL),
  (8, 88, 'goal',   7, 30, NULL),
  (8, 75, 'red',    4, 16, 'Contestation');

INSERT INTO match_stats (match_id, team_id, possession, shots, shots_on_target, corners, fouls) VALUES
  (2, 3, 54, 11, 5, 5, 9),
  (2, 4, 46, 8, 3, 3, 12);

INSERT INTO news (title, slug, category, excerpt, author_id, status, published_at) VALUES
  ("L'Étoile du Nord s'impose dans le choc au sommet", 'etoile-du-nord-choc-au-sommet', 'Championnat', "Deux buts en première période ont suffi aux hommes d'Ibrahim Moussa pour prendre la tête du classement.", 5, 'published', '2026-08-02 21:00:00'),
  ('Tirage au sort des quarts du Grand Prix Mbaïrobé', 'tirage-quarts-grand-prix', 'Grand Prix', "Les huit qualifiés connaissent leur adversaire. Le tenant du titre hérite du Racing Garoua.", 1, 'published', '2026-07-31 12:00:00'),
  ("Trois jours de stage arbitrage avant la phase finale", 'stage-arbitrage-2026', 'Formation', "Douze arbitres de la région du Nord ont suivi le module de la nouvelle feuille de match numérique.", 5, 'published', '2026-07-28 09:00:00'),
  ('Programme de la finale de la Super Coupe', 'programme-finale-super-coupe', 'Super Coupe', "Le protocole et les horaires de la finale du 24 août.", 1, 'draft', NULL);

INSERT INTO media (edition_id, type, path, caption) VALUES
  (6, 'photo', '/assets/img/gallery/gfc-1.jpg', 'Grand Prix Gabriel Mbaïrobé'),
  (6, 'photo', '/assets/img/gallery/gfc-2.jpg', 'Coup d''envoi à Roumdé Adjia'),
  (6, 'photo', '/assets/img/gallery/gfc-3.jpg', 'Le derby de Garoua'),
  (6, 'photo', '/assets/img/gallery/gfc-4.jpg', 'Célébration d''un but'),
  (6, 'photo', '/assets/img/gallery/gfc-5.jpg', 'Tribune comble'),
  (6, 'photo', '/assets/img/gallery/gfc-6.jpg', 'Feuille de match numérique'),
  (6, 'photo', '/assets/img/gallery/gfc-7.jpg', 'Trophée de la 6e édition'),
  (6, 'photo', '/assets/img/gallery/gfc-8.jpg', 'Les champions en titre'),
  (6, 'video', '/storage/uploads/resume-j5.mp4', 'Résumé vidéo de la journée 5');

INSERT INTO ticket_types (match_id, label, price, quota, sold, status) VALUES
  (5, 'Tribune',   2000, 3000, 1240, 'open'),
  (4, 'Tribune',   2000, 3000,  860, 'open'),
  (3, 'Populaire', 1000, 2000,  540, 'closed'),
  (2, 'Populaire',  500, 1500,  210, 'open');

INSERT INTO ticket_orders (ticket_type_id, buyer_phone, qty, amount, channel, status, created_at) VALUES
  (1, '+237690111111', 4, 8000, 'mobile_money', 'paid', DATE_SUB(CURDATE(), INTERVAL 6 DAY)),
  (1, '+237690222222', 2, 4000, 'gate',         'paid', DATE_SUB(CURDATE(), INTERVAL 5 DAY)),
  (2, '+237690333333', 6, 12000,'mobile_money', 'paid', DATE_SUB(CURDATE(), INTERVAL 4 DAY)),
  (2, '+237690444444', 3, 6000, 'gate',         'paid', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
  (1, '+237690555555', 8, 16000,'mobile_money', 'paid', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
  (1, '+237690666666', 12,24000,'partner',      'paid', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
  (4, '+237690777777', 5, 2500, 'gate',         'paid', CURDATE());

INSERT INTO sponsors (name, tier, placement, status) VALUES
  ('Brasseries du Nord', 'Partenaire principal',   'Accueil + maillots',   'active'),
  ('Garoua Telecom',     'Partenaire officiel',    'Bannière matchs',      'active'),
  ('Mobile Money CM',    'Billetterie',            'Tunnel de paiement',   'active'),
  ('Hôtel La Bénoué',    'Partenaire hospitalité', 'Page équipes',         'expiring'),
  ('Radio Sahel FM',     'Média',                  'Page actualités',      'active');

INSERT INTO honours (edition_id, title, team_id, team_label, player_label) VALUES
  (5, 'Champion',                  4, NULL, NULL),
  (5, 'Grand Prix Mbaïrobé',       1, NULL, NULL),
  (5, 'Meilleur buteur',        NULL, NULL, 'Bello Hamadou (11)'),
  (4, 'Champion',                  1, NULL, NULL),
  (4, 'Grand Prix Mbaïrobé',       1, NULL, NULL),
  (4, 'Meilleur buteur',        NULL, NULL, 'Adamou Maïga (13)'),
  (3, 'Champion',                  1, NULL, NULL),
  (3, 'Grand Prix Mbaïrobé',       4, NULL, NULL),
  (3, 'Meilleur buteur',        NULL, NULL, 'Saidou Njoya (9)'),
  (2, 'Champion',                  2, NULL, NULL),
  (2, 'Grand Prix Mbaïrobé',       3, NULL, NULL),
  (2, 'Meilleur buteur',        NULL, NULL, 'Issa Ngaroua (8)'),
  (1, 'Champion',                  2, NULL, NULL),
  (1, 'Grand Prix Mbaïrobé',       2, NULL, NULL),
  (1, 'Meilleur buteur',        NULL, NULL, 'Vidal Mbaye (10)');

INSERT INTO registrations (team_name, city, manager_name, phone, coach, squad_size, target, status) VALUES
  ('AS Bibémiré',   'Garoua', 'Souleymane Bello', '+237690888888', 'Nasser Abba',    20, 'Championnat', 'reviewing'),
  ('Espoir Djamboutou', 'Garoua', 'Rachid Yaouba', '+237690999999', 'Célestin Bidoung', 18, 'Les deux',  'received');

INSERT INTO audit_log (user_id, action, entity, entity_id) VALUES
  (3, 'a enregistré un but à la 62e minute',        'match_events', 6),
  (1, 'a publié le classement après la journée 6',  'competitions', 1),
  (2, "a ajouté 2 joueurs à l'effectif",            'teams',        1),
  (5, "a publié l'article « Choc au sommet »",      'news',         1),
  (1, 'a ouvert le guichet de la Super Coupe',      'ticket_types', 1);

-- Garoua Football Challenge — schéma MySQL 8 / MariaDB 10.4+
SET NAMES utf8mb4;
SET time_zone = '+01:00';

CREATE TABLE editions (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year         SMALLINT NOT NULL,
  label        VARCHAR(60) NOT NULL,
  starts_on    DATE NULL,
  ends_on      DATE NULL,
  is_current   TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uk_editions_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE competitions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  edition_id    INT UNSIGNED NOT NULL,
  name          VARCHAR(120) NOT NULL,
  slug          VARCHAR(120) NOT NULL,
  type          ENUM('league','cup','supercup') NOT NULL,
  format        VARCHAR(160) NULL,
  color         CHAR(7) NOT NULL DEFAULT '#7a1c2a',
  points_win    TINYINT NOT NULL DEFAULT 3,
  points_draw   TINYINT NOT NULL DEFAULT 1,
  qualify_slots TINYINT NOT NULL DEFAULT 2,
  UNIQUE KEY uk_comp_slug (edition_id, slug),
  CONSTRAINT fk_comp_edition FOREIGN KEY (edition_id) REFERENCES editions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE phases (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  competition_id INT UNSIGNED NOT NULL,
  name           VARCHAR(120) NOT NULL,
  ord            TINYINT NOT NULL DEFAULT 1,
  status         ENUM('planned','running','done') NOT NULL DEFAULT 'planned',
  starts_on      DATE NULL,
  CONSTRAINT fk_phase_comp FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE venues (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name    VARCHAR(120) NOT NULL,
  city    VARCHAR(80) NOT NULL,
  capacity SMALLINT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teams (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  edition_id      INT UNSIGNED NOT NULL,
  name            VARCHAR(120) NOT NULL,
  short_name      VARCHAR(6) NOT NULL,
  city            VARCHAR(80) NOT NULL,
  coach           VARCHAR(120) NULL,
  founded         SMALLINT NULL,
  color_primary   CHAR(7) NOT NULL DEFAULT '#7a1c2a',
  color_secondary CHAR(7) NOT NULL DEFAULT '#e8720c',
  logo_path       VARCHAR(255) NULL,
  description     TEXT NULL,
  status          ENUM('pending','validated','rejected') NOT NULL DEFAULT 'pending',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_teams_edition (edition_id),
  CONSTRAINT fk_teams_edition FOREIGN KEY (edition_id) REFERENCES editions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE team_competition (
  team_id        INT UNSIGNED NOT NULL,
  competition_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (team_id, competition_id),
  CONSTRAINT fk_tc_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  CONSTRAINT fk_tc_comp FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE players (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_id        INT UNSIGNED NOT NULL,
  first_name     VARCHAR(80) NOT NULL,
  last_name      VARCHAR(80) NOT NULL,
  position       ENUM('GB','DEF','MIL','ATT') NOT NULL DEFAULT 'MIL',
  shirt_no       TINYINT UNSIGNED NULL,
  birth_date     DATE NULL,
  license_no     VARCHAR(40) NULL,
  license_status ENUM('pending','valid','missing') NOT NULL DEFAULT 'pending',
  photo_path     VARCHAR(255) NULL,
  KEY idx_players_team (team_id),
  CONSTRAINT fk_players_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  phone         VARCHAR(20) NOT NULL,
  email         VARCHAR(160) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','delegate','referee','editor') NOT NULL,
  team_id       INT UNSIGNED NULL,
  status        ENUM('active','invited','disabled') NOT NULL DEFAULT 'active',
  last_login_at DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_users_phone (phone),
  CONSTRAINT fk_users_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE matches (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  competition_id INT UNSIGNED NOT NULL,
  phase_id       INT UNSIGNED NULL,
  matchday       TINYINT UNSIGNED NULL,
  home_team_id   INT UNSIGNED NOT NULL,
  away_team_id   INT UNSIGNED NOT NULL,
  venue_id       INT UNSIGNED NULL,
  referee_id     INT UNSIGNED NULL,
  kickoff_at     DATETIME NOT NULL,
  status         ENUM('scheduled','live','halftime','finished','postponed') NOT NULL DEFAULT 'scheduled',
  minute         TINYINT UNSIGNED NULL,
  home_score     TINYINT UNSIGNED NULL,
  away_score     TINYINT UNSIGNED NULL,
  sheet_status   ENUM('draft','submitted','validated') NOT NULL DEFAULT 'draft',
  KEY idx_matches_comp (competition_id),
  KEY idx_matches_kickoff (kickoff_at),
  KEY idx_matches_status (status),
  CONSTRAINT fk_m_comp FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
  CONSTRAINT fk_m_phase FOREIGN KEY (phase_id) REFERENCES phases(id) ON DELETE SET NULL,
  CONSTRAINT fk_m_home FOREIGN KEY (home_team_id) REFERENCES teams(id),
  CONSTRAINT fk_m_away FOREIGN KEY (away_team_id) REFERENCES teams(id),
  CONSTRAINT fk_m_venue FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE SET NULL,
  CONSTRAINT fk_m_ref FOREIGN KEY (referee_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lineups (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  match_id   INT UNSIGNED NOT NULL,
  team_id    INT UNSIGNED NOT NULL,
  player_id  INT UNSIGNED NOT NULL,
  shirt_no   TINYINT UNSIGNED NULL,
  is_starter TINYINT(1) NOT NULL DEFAULT 1,
  is_captain TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uk_lineup (match_id, player_id),
  CONSTRAINT fk_l_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  CONSTRAINT fk_l_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  CONSTRAINT fk_l_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE match_events (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  match_id     INT UNSIGNED NOT NULL,
  minute       TINYINT UNSIGNED NOT NULL,
  type         ENUM('goal','own_goal','penalty','miss','yellow','red','sub','shot','period','note') NOT NULL,
  team_id      INT UNSIGNED NULL,
  player_id    INT UNSIGNED NULL,
  player_in_id INT UNSIGNED NULL,
  note         VARCHAR(255) NULL,
  created_by   INT UNSIGNED NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_ev_match (match_id, minute),
  CONSTRAINT fk_ev_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  CONSTRAINT fk_ev_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
  CONSTRAINT fk_ev_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
  CONSTRAINT fk_ev_player_in FOREIGN KEY (player_in_id) REFERENCES players(id) ON DELETE SET NULL,
  CONSTRAINT fk_ev_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE match_stats (
  match_id        INT UNSIGNED NOT NULL,
  team_id         INT UNSIGNED NOT NULL,
  possession      TINYINT UNSIGNED NULL,
  shots           TINYINT UNSIGNED NULL,
  shots_on_target TINYINT UNSIGNED NULL,
  corners         TINYINT UNSIGNED NULL,
  fouls           TINYINT UNSIGNED NULL,
  PRIMARY KEY (match_id, team_id),
  CONSTRAINT fk_ms_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  CONSTRAINT fk_ms_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sanctions (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  player_id    INT UNSIGNED NULL,
  team_id      INT UNSIGNED NOT NULL,
  match_id     INT UNSIGNED NULL,
  type         ENUM('yellow_accumulation','red','misconduct','forfeit','fine') NOT NULL,
  reason       VARCHAR(255) NOT NULL,
  games_banned TINYINT UNSIGNED NOT NULL DEFAULT 0,
  fine_amount  INT UNSIGNED NOT NULL DEFAULT 0,
  status       ENUM('open','applied','appealed','cancelled') NOT NULL DEFAULT 'open',
  decided_at   DATETIME NULL,
  CONSTRAINT fk_sa_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  CONSTRAINT fk_sa_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
  CONSTRAINT fk_sa_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200) NOT NULL,
  slug         VARCHAR(220) NOT NULL,
  category     VARCHAR(60) NOT NULL DEFAULT 'Championnat',
  excerpt      VARCHAR(400) NULL,
  body         MEDIUMTEXT NULL,
  cover_path   VARCHAR(255) NULL,
  author_id    INT UNSIGNED NULL,
  status       ENUM('draft','scheduled','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  UNIQUE KEY uk_news_slug (slug),
  CONSTRAINT fk_news_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  edition_id INT UNSIGNED NULL,
  match_id   INT UNSIGNED NULL,
  type       ENUM('photo','video') NOT NULL DEFAULT 'photo',
  path       VARCHAR(255) NOT NULL,
  caption    VARCHAR(200) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_media_edition FOREIGN KEY (edition_id) REFERENCES editions(id) ON DELETE SET NULL,
  CONSTRAINT fk_media_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_types (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  match_id INT UNSIGNED NOT NULL,
  label    VARCHAR(80) NOT NULL,
  price    INT UNSIGNED NOT NULL,
  quota    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  sold     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  status   ENUM('open','closed') NOT NULL DEFAULT 'open',
  CONSTRAINT fk_tt_match FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ticket_orders (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_type_id INT UNSIGNED NOT NULL,
  buyer_phone    VARCHAR(20) NOT NULL,
  qty            TINYINT UNSIGNED NOT NULL DEFAULT 1,
  amount         INT UNSIGNED NOT NULL,
  channel        ENUM('gate','mobile_money','partner') NOT NULL DEFAULT 'gate',
  reference      VARCHAR(60) NULL,
  status         ENUM('pending','paid','cancelled','used') NOT NULL DEFAULT 'pending',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_to_type (ticket_type_id),
  CONSTRAINT fk_to_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sponsors (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(120) NOT NULL,
  tier      VARCHAR(80) NOT NULL DEFAULT 'Partenaire',
  logo_path VARCHAR(255) NULL,
  url       VARCHAR(255) NULL,
  placement VARCHAR(120) NULL,
  status    ENUM('active','expiring','inactive') NOT NULL DEFAULT 'active',
  starts_on DATE NULL,
  ends_on   DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registrations (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  team_name    VARCHAR(120) NOT NULL,
  city         VARCHAR(80) NOT NULL,
  manager_name VARCHAR(120) NOT NULL,
  phone        VARCHAR(20) NOT NULL,
  coach        VARCHAR(120) NULL,
  squad_size   TINYINT UNSIGNED NULL,
  target       VARCHAR(60) NULL,
  file_path    VARCHAR(255) NULL,
  status       ENUM('received','reviewing','accepted','rejected') NOT NULL DEFAULT 'received',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE app_users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phone      VARCHAR(20) NOT NULL,
  name       VARCHAR(120) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_app_users_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE otp_codes (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phone      VARCHAR(20) NOT NULL,
  code_hash  VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at    DATETIME NULL,
  KEY idx_otp_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_tokens (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  app_user_id  INT UNSIGNED NULL,
  user_id      INT UNSIGNED NULL,
  token_hash   CHAR(64) NOT NULL,
  expires_at   DATETIME NOT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_token (token_hash),
  CONSTRAINT fk_tok_app FOREIGN KEY (app_user_id) REFERENCES app_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_tok_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE favorites (
  app_user_id INT UNSIGNED NOT NULL,
  team_id     INT UNSIGNED NOT NULL,
  PRIMARY KEY (app_user_id, team_id),
  CONSTRAINT fk_fav_user FOREIGN KEY (app_user_id) REFERENCES app_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_fav_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE devices (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  app_user_id INT UNSIGNED NOT NULL,
  push_token  VARCHAR(255) NOT NULL,
  platform    ENUM('web','android','ios') NOT NULL DEFAULT 'web',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_push (push_token),
  CONSTRAINT fk_dev_user FOREIGN KEY (app_user_id) REFERENCES app_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_log (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NULL,
  action     VARCHAR(120) NOT NULL,
  entity     VARCHAR(60) NULL,
  entity_id  INT UNSIGNED NULL,
  meta       JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_audit_created (created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE honours (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  edition_id    INT UNSIGNED NOT NULL,
  title         VARCHAR(120) NOT NULL,
  team_id       INT UNSIGNED NULL,
  team_label    VARCHAR(120) NULL,
  player_label  VARCHAR(120) NULL,
  CONSTRAINT fk_hon_edition FOREIGN KEY (edition_id) REFERENCES editions(id) ON DELETE CASCADE,
  CONSTRAINT fk_hon_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classement calculé : une ligne par équipe et par compétition.
CREATE OR REPLACE VIEW v_standings AS
SELECT
  r.competition_id,
  r.team_id,
  t.name        AS team_name,
  t.short_name,
  t.city,
  t.color_primary,
  COUNT(*)                                             AS played,
  SUM(r.gf > r.ga)                                     AS won,
  SUM(r.gf = r.ga)                                     AS drawn,
  SUM(r.gf < r.ga)                                     AS lost,
  SUM(r.gf)                                            AS goals_for,
  SUM(r.ga)                                            AS goals_against,
  SUM(r.gf) - SUM(r.ga)                                AS goal_diff,
  SUM(CASE WHEN r.gf > r.ga THEN c.points_win
           WHEN r.gf = r.ga THEN c.points_draw
           ELSE 0 END)                                 AS points
FROM (
  SELECT competition_id, home_team_id AS team_id, home_score AS gf, away_score AS ga
  FROM matches WHERE status = 'finished'
  UNION ALL
  SELECT competition_id, away_team_id AS team_id, away_score AS gf, home_score AS ga
  FROM matches WHERE status = 'finished'
) r
JOIN teams t        ON t.id = r.team_id
JOIN competitions c ON c.id = r.competition_id
GROUP BY r.competition_id, r.team_id;

-- Buteurs calculés depuis la feuille de match.
CREATE OR REPLACE VIEW v_top_scorers AS
SELECT
  p.id AS player_id,
  CONCAT(p.first_name, ' ', p.last_name) AS player_name,
  p.position,
  t.id AS team_id,
  t.name AS team_name,
  SUM(e.type IN ('goal','penalty'))      AS goals,
  SUM(e.type = 'sub')                    AS subs,
  SUM(e.type = 'yellow')                 AS yellows,
  SUM(e.type = 'red')                    AS reds
FROM players p
JOIN teams t ON t.id = p.team_id
LEFT JOIN match_events e ON e.player_id = p.id
GROUP BY p.id;

-- Garoua Football Challenge — schéma MySQL 8
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS gfc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gfc;

CREATE TABLE seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,            -- "6e édition"
  year SMALLINT NOT NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE competitions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  type ENUM('league','cup','supercup') NOT NULL,
  description TEXT,
  start_date DATE, end_date DATE,
  sort_order TINYINT DEFAULT 0,
  CONSTRAINT fk_comp_season FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  abbr VARCHAR(5) NOT NULL,
  quarter VARCHAR(80),                  -- quartier de Garoua
  founded_year SMALLINT,
  logo VARCHAR(255),
  color VARCHAR(9) DEFAULT '#7A1F30',
  coach VARCHAR(120),
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE competition_team (
  competition_id INT NOT NULL,
  team_id INT NOT NULL,
  group_name VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (competition_id, team_id),
  FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  jersey_number TINYINT UNSIGNED,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  position ENUM('GB','DEF','MIL','ATT') NOT NULL,
  position_label VARCHAR(40),
  birth_date DATE,
  height_cm SMALLINT,
  strong_foot ENUM('droit','gauche','les deux') DEFAULT 'droit',
  photo VARCHAR(255),
  licence_no VARCHAR(40) UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  INDEX idx_players_team (team_id)
) ENGINE=InnoDB;

CREATE TABLE matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  competition_id INT NOT NULL,
  matchday TINYINT UNSIGNED DEFAULT NULL,   -- championnat
  round_label VARCHAR(40) DEFAULT NULL,     -- "Demi-finale"
  home_team_id INT NOT NULL,
  away_team_id INT NOT NULL,
  kickoff_at DATETIME NOT NULL,
  venue VARCHAR(120),
  referee VARCHAR(120),
  attendance INT DEFAULT NULL,
  status ENUM('scheduled','live','halftime','finished','postponed','cancelled') NOT NULL DEFAULT 'scheduled',
  minute TINYINT UNSIGNED DEFAULT NULL,
  -- Cache derive des evenements : recalcule par Score::recompute() dans la
  -- transaction qui ecrit l'evenement. Aucun formulaire ne l'ecrit (invariant I1).
  home_score TINYINT UNSIGNED DEFAULT NULL,
  away_score TINYINT UNSIGNED DEFAULT NULL,
  -- Tirs au but : departagent un tour du Grand Prix. N'entrent jamais dans
  -- goals_for / goals_against de v_standings.
  home_pens TINYINT UNSIGNED DEFAULT NULL,
  away_pens TINYINT UNSIGNED DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
  FOREIGN KEY (home_team_id) REFERENCES teams(id),
  FOREIGN KEY (away_team_id) REFERENCES teams(id),
  INDEX idx_matches_kickoff (kickoff_at),
  INDEX idx_matches_status (status)
) ENGINE=InnoDB;

CREATE TABLE match_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  team_id INT DEFAULT NULL,
  player_id INT DEFAULT NULL,
  related_player_id INT DEFAULT NULL,       -- passeur, joueur remplacé
  minute TINYINT UNSIGNED NOT NULL,
  type ENUM('kickoff','goal','own_goal','penalty','penalty_missed','yellow','red','sub','halftime','fulltime','var') NOT NULL,
  detail VARCHAR(180),
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  -- Identifiant genere par l'appareil de l'operateur. Une saisie faite hors
  -- reseau puis rejouee au retour de la connexion porte le meme client_ref :
  -- l'unicite ci-dessous empeche qu'un but soit compte deux fois (FR-041).
  client_ref CHAR(36) DEFAULT NULL,
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL,
  UNIQUE KEY uniq_event_client_ref (match_id, client_ref),
  INDEX idx_events_match (match_id, minute)
) ENGINE=InnoDB;

CREATE TABLE match_team_stats (
  match_id INT NOT NULL,
  team_id INT NOT NULL,
  possession TINYINT UNSIGNED DEFAULT NULL,
  shots TINYINT UNSIGNED DEFAULT 0,
  shots_on_target TINYINT UNSIGNED DEFAULT 0,
  corners TINYINT UNSIGNED DEFAULT 0,
  fouls TINYINT UNSIGNED DEFAULT 0,
  offsides TINYINT UNSIGNED DEFAULT 0,
  PRIMARY KEY (match_id, team_id),
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lineups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  match_id INT NOT NULL,
  team_id INT NOT NULL,
  player_id INT NOT NULL,
  is_starter TINYINT(1) NOT NULL DEFAULT 1,
  minutes_played SMALLINT DEFAULT 0,
  UNIQUE KEY uniq_lineup (match_id, player_id),
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
  FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(220) NOT NULL UNIQUE,
  category VARCHAR(60) DEFAULT 'Actualité',
  excerpt VARCHAR(400),
  body MEDIUMTEXT,
  cover_image VARCHAR(255),
  published_at DATETIME DEFAULT NULL,
  author_id INT DEFAULT NULL,
  INDEX idx_news_pub (published_at)
) ENGINE=InnoDB;

CREATE TABLE media (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('photo','video') NOT NULL,
  title VARCHAR(200) NOT NULL,
  url VARCHAR(400) NOT NULL,
  thumbnail VARCHAR(400),
  duration_seconds SMALLINT DEFAULT NULL,
  match_id INT DEFAULT NULL,
  published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','secretaire','arbitre') NOT NULL DEFAULT 'secretaire',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE api_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE device_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expo_token VARCHAR(200) NOT NULL UNIQUE,
  platform ENUM('android','ios') NOT NULL,
  favourite_team_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Classement calculé (championnat) : vue réutilisée par l'API et le back-office
CREATE OR REPLACE VIEW v_standings AS
SELECT
  m.competition_id,
  t.id  AS team_id,
  t.name, t.abbr, t.logo, t.color,
  COUNT(*)                                                   AS played,
  SUM(gf > ga)                                               AS won,
  SUM(gf = ga)                                               AS drawn,
  SUM(gf < ga)                                               AS lost,
  SUM(gf)                                                    AS goals_for,
  SUM(ga)                                                    AS goals_against,
  SUM(gf) - SUM(ga)                                          AS goal_diff,
  SUM(CASE WHEN gf > ga THEN 3 WHEN gf = ga THEN 1 ELSE 0 END) AS points
FROM (
  SELECT id, competition_id, home_team_id AS team_id, home_score AS gf, away_score AS ga FROM matches WHERE status='finished'
  UNION ALL
  SELECT id, competition_id, away_team_id AS team_id, away_score AS gf, home_score AS ga FROM matches WHERE status='finished'
) AS m
JOIN teams t ON t.id = m.team_id
GROUP BY m.competition_id, t.id;

# Modèle de données — Plateforme GFC

**Feature**: 001-plateforme-gfc | **Date**: 2026-08-29 | **SGBD**: MySQL 8, InnoDB, `utf8mb4_unicode_ci`

13 tables et une vue. La règle qui gouverne l'ensemble : **tout chiffre affiché
est dérivé de `match_events` ou des matchs terminés**. Les seules valeurs
dénormalisées autorisées sont des caches recalculés dans la transaction qui les
invalide.

## Entités

### `seasons` — édition

L'édition courante (la 6e). `is_current` marque celle que l'API sert par défaut.

| Colonne | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `name` | VARCHAR(60) | « 6e édition » |
| `year` | SMALLINT | |
| `is_current` | TINYINT(1) | une seule à 1 |

### `competitions` — compétition

| Colonne | Type | Notes |
|---|---|---|
| `id` | INT PK | |
| `season_id` | INT FK → seasons | cascade |
| `name`, `slug` | VARCHAR | slug unique, utilisé comme filtre d'API |
| `type` | ENUM(`league`,`cup`,`supercup`) | pilote le mode de classement |
| `description`, `start_date`, `end_date`, `sort_order` | | |

`type = league` → classement par points. `type = cup` / `supercup` → tableau à
élimination, pas de classement.

### `teams` — équipe

`name`, `abbr` (5 car., utilisé par l'écusson de repli), `quarter` (quartier de
Garoua), `founded_year`, `logo`, `color` (couleur du club, alimente l'écusson),
`coach`, `is_active`.

### `competition_team` — engagement

Table de liaison `(competition_id, team_id)` avec `group_name` optionnel pour une
phase de groupes. Une équipe participe à plusieurs compétitions de la même saison.

### `players` — joueur

`team_id`, `jersey_number`, `first_name`, `last_name`, `position`
(`GB`/`DEF`/`MIL`/`ATT`, qui pilote le groupement de l'effectif), `position_label`,
`birth_date`, `height_cm`, `strong_foot`, `photo`, `licence_no` (unique),
`is_active`.

### `matches` — rencontre

| Colonne | Type | Notes |
|---|---|---|
| `competition_id` | INT FK | |
| `matchday` | TINYINT | journée de championnat, NULL en coupe |
| `round_label` | VARCHAR(40) | « Demi-finale », NULL en championnat |
| `home_team_id`, `away_team_id` | INT FK → teams | |
| `kickoff_at` | DATETIME | heure locale de Garoua |
| `venue`, `referee`, `attendance` | | |
| `status` | ENUM(`scheduled`,`live`,`halftime`,`finished`,`postponed`) | seul `finished` entre au classement |
| `minute` | TINYINT | minute de jeu en direct |
| `home_score`, `away_score` | TINYINT | **cache dérivé** — voir invariant I1 |
| `updated_at` | TIMESTAMP | mis à jour à chaque écriture, sert au polling |

### `match_events` — événement de match *(source de vérité)*

| Colonne | Type | Notes |
|---|---|---|
| `match_id` | INT FK | cascade |
| `team_id`, `player_id` | INT FK | équipe et auteur |
| `related_player_id` | INT | passeur, ou joueur remplacé |
| `minute` | TINYINT | |
| `type` | ENUM | `kickoff`, `goal`, `own_goal`, `penalty`, `penalty_missed`, `yellow`, `red`, `sub`, `halftime`, `fulltime`, `var` |
| `detail` | VARCHAR(180) | précision libre |
| `is_published` | TINYINT(1) | un événement non publié ne sort pas dans l'API publique |
| `created_by`, `created_at` | | traçabilité de l'opérateur |

Index `(match_id, minute)` : le fil d'événements est lu à chaque cycle de polling.

### `match_team_stats` — statistiques de rencontre

`possession`, `shots`, `shots_on_target`, `corners`, `fouls`, `offsides` par
`(match_id, team_id)`. Saisies à la main : elles ne sont dérivables d'aucun
événement, ce qui les place hors de l'invariant I1.

### `lineups` — composition

`(match_id, team_id, player_id)`, `is_starter`, `minutes_played`. Unicité sur
`(match_id, player_id)` : un joueur n'apparaît qu'une fois par match.

### `news` — actualité

`title`, `slug` (unique), `category`, `excerpt`, `body`, `cover_image`,
`published_at` (NULL = brouillon, invisible dans l'API publique), `author_id`.

### `media` — photo ou vidéo

`type` (`photo`/`video`), `title`, `url`, `thumbnail`, `duration_seconds`,
`match_id` optionnel, `published_at`.

### `users` — opérateur du back-office

`name`, `email` (unique), `password_hash`, `role` (`admin`/`secretaire`/`arbitre`),
`is_active`.

### `api_tokens` — jeton d'écriture

`user_id`, `token` CHAR(64) unique, `expires_at`. Tout jeton expiré est refusé et
purgé à la connexion suivante.

### `device_tokens` — appareil notifié

`expo_token` (unique), `platform`, `favourite_team_id`, `created_at`.

## Vue dérivée

### `v_standings` — classement

Déplie chaque match `finished` en deux lignes (recevant / visiteur), puis agrège
par `(competition_id, team_id)` : `played`, `won`, `drawn`, `lost`, `goals_for`,
`goals_against`, `goal_diff`, `points` (3 / 1 / 0).

Tri appliqué à la lecture : `points DESC, goal_diff DESC, goals_for DESC, name ASC`
(décision D4). Aucune position n'est stockée : le rang est l'indice de la ligne
dans ce tri.

## Règles de dérivation

| Chiffre affiché | Dérivé de |
|---|---|
| Score d'un match | `match_events` de type `goal`, `penalty` (à l'équipe qui marque) et `own_goal` (à l'équipe adverse) |
| Classement, points, différence de buts | `v_standings`, sur les matchs `finished` |
| Buts d'un joueur | `count(match_events WHERE player_id = ? AND type IN ('goal','penalty'))` |
| Passes décisives | `count(match_events WHERE related_player_id = ? AND type IN ('goal','penalty'))` |
| Cartons | `count(match_events WHERE player_id = ? AND type IN ('yellow','red'))` |
| Attaque / défense d'une équipe | `goals_for` / `goals_against` de `v_standings` |
| Possession, affluence | `match_team_stats.possession` et `matches.attendance`, moyennés |
| Buts par journée d'un joueur | événements joints à `matches.matchday` |

Un but contre son camp compte pour l'équipe adverse au score, mais n'est **pas**
compté dans les buts personnels du joueur au classement des buteurs.

## Invariants

- **I1** — `matches.home_score` / `away_score` sont recalculés par
  `Score::recompute($matchId)` dans la même transaction que toute écriture sur
  `match_events` (insertion, modification, suppression). Aucun autre code
  n'écrit ces colonnes. Une commande de vérification doit pouvoir recalculer tous
  les matchs et ne produire aucun écart.
- **I2** — Seuls les matchs `finished` entrent dans `v_standings`. Un match
  `postponed` ou annulé n'apparaît dans aucun classement.
- **I3** — Un événement `is_published = 0` n'est jamais exposé par l'API
  publique, mais est visible dans le back-office.
- **I4** — Une actualité de `published_at` NULL ou futur n'est pas exposée
  publiquement.
- **I5** — Les statistiques d'un joueur sont rattachées à l'équipe portée par
  l'événement (`match_events.team_id`), pas à `players.team_id` : un joueur
  transféré en cours d'édition conserve ses buts marqués sous son ancien maillot.
- **I6** — `home_team_id <> away_team_id` sur tout match.
- **I7** — Un jeton d'API dont `expires_at` est dépassé n'autorise aucune
  écriture.

## Écarts à combler par rapport au schéma existant

- **E1 — Tirs au but.** Le schéma ne porte pas le résultat des tirs au but, alors
  que le cas limite « match à élimination se terminant sur un nul » l'exige.
  Ajouter `matches.home_pens` et `matches.away_pens` (TINYINT UNSIGNED, NULL par
  défaut). Ils qualifient le vainqueur d'un tour de coupe et **ne doivent pas**
  entrer dans `goals_for` / `goals_against` de `v_standings`.
- **E2 — Statut annulé.** L'énumération `status` porte `postponed` mais pas
  `cancelled`, exigé par la spécification. Ajouter la valeur.
- **E3 — Saisie concurrente.** Le cas limite « deux opérateurs sur le même match »
  n'est pas couvert. Le recalcul du score doit se faire dans une transaction avec
  `SELECT ... FOR UPDATE` sur la ligne du match, afin qu'aucun événement
  simultané ne soit perdu ni compté deux fois.
- **E5 — Idempotence des saisies mobiles.** L'espace opérateur mobile doit
  pouvoir rejouer une saisie faite hors réseau (FR-041) sans créer de doublon.
  Ajouter `match_events.client_ref` (CHAR(36), NULL) avec une contrainte
  d'unicité sur `(match_id, client_ref)`. Une seconde transmission du même
  `client_ref` renvoie l'événement existant au lieu d'en insérer un nouveau.
  Sans cela, un réseau instable au bord du terrain produirait des buts comptés
  deux fois.
- **E4 — Journalisation des corrections.** La suppression d'un événement ne laisse
  aucune trace. Prévoir une suppression logique ou une table de journal, pour que
  la correction d'un score reste auditable — le principe II demande la
  traçabilité de chaque chiffre.

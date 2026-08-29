# Contrat de l'API REST — Plateforme GFC

**Feature**: 001-plateforme-gfc | **Format**: JSON, `utf8mb4`

**Base** : `https://gfc.trugroup.cm/api` en production,
`http://localhost:8000/api` en développement (`http://10.0.2.2:8000/api` depuis
un émulateur Android). Voir `deploy/DEPLOIEMENT.md` sur la branche `web`.

Ce document est le **seul lien** entre la branche `web` et la branche `mobile`.
Toute évolution cassante doit être décrite ici avant d'être implémentée d'un côté
ou de l'autre ; les évolutions additives (nouveau champ, nouvelle route) ne
nécessitent pas de version.

## Conventions

- Toutes les réponses sont en JSON. Les libellés destinés à l'affichage sont en
  français.
- Les dates sont en ISO 8601 avec l'heure locale de Garoua (`2026-08-29T16:00:00+01:00`).
- Une erreur renvoie `{"error": {"code": "...", "message": "message en français"}}`
  avec le code HTTP correspondant : `400` requête invalide, `401` jeton absent ou
  expiré, `403` rôle insuffisant, `404` ressource inconnue, `413` fichier trop
  volumineux, `422` données refusées, `500` erreur serveur.
- Les listes renvoient un tableau JSON à la racine ; les fiches renvoient un objet.
- Les routes de lecture sont **publiques**. Les routes d'écriture exigent
  l'en-tête `Authorization: Bearer <token>`.
- Chaque réponse porte `Cache-Control` : `max-age=60` en lecture stable,
  `no-cache` sur les ressources d'un match en direct.

## Lecture publique

### `GET /api/competitions`

Les compétitions de la saison courante.

```json
[{ "id": 1, "name": "Championnat", "slug": "championnat", "type": "league",
   "description": "...", "start_date": "2026-07-05", "end_date": "2026-09-12" }]
```

### `GET /api/matches?scope=upcoming|results&competition={slug}&team={id}`

Calendrier (`upcoming`) ou résultats (`results`). `competition` et `team` sont
des filtres facultatifs. Trié par `kickoff_at` croissant pour `upcoming`,
décroissant pour `results`.

```json
[{ "id": 42, "competition": { "slug": "championnat", "name": "Championnat" },
   "matchday": 5, "round_label": null,
   "home": { "id": 3, "name": "...", "abbr": "TON", "logo": null, "color": "#7A1F30" },
   "away": { "id": 7, "name": "...", "abbr": "DJA", "logo": null, "color": "#1F4E7A" },
   "kickoff_at": "2026-08-29T16:00:00+01:00", "venue": "Stade Roumdé Adjia",
   "status": "live", "minute": 63, "home_score": 2, "away_score": 1,
   "home_pens": null, "away_pens": null }]
```

### `GET /api/matches/{id}`

Fiche complète : le match, son fil d'événements (uniquement `is_published = 1`),
les compositions et les statistiques de rencontre.

```json
{ "match": { "...": "comme ci-dessus", "referee": "...", "attendance": 1200 },
  "events": [{ "id": 900, "minute": 23, "type": "goal", "team_id": 3,
               "player": { "id": 51, "name": "..." },
               "assist": { "id": 55, "name": "..." }, "detail": null }],
  "lineups": { "home": [{ "player_id": 51, "name": "...", "jersey_number": 9,
                          "position": "ATT", "is_starter": true }], "away": [] },
  "stats": { "home": { "possession": 56, "shots": 12, "shots_on_target": 5,
                       "corners": 4, "fouls": 9, "offsides": 2 }, "away": {} } }
```

C'est la route interrogée toutes les 15 s pendant un match en direct. Elle doit
répondre en moins de 300 ms et porter `Cache-Control: no-cache` tant que
`status` vaut `live` ou `halftime`.

### `GET /api/standings?competition={slug}`

Classement calculé depuis `v_standings`, trié `points DESC, goal_diff DESC,
goals_for DESC, name ASC`. Le rang est l'indice dans le tableau ; il n'est pas
stocké. `zone` signale la qualification aux quarts de finale du Grand Prix.

```json
[{ "rank": 1, "team": { "id": 3, "name": "...", "abbr": "TON", "logo": null, "color": "#7A1F30" },
   "played": 6, "won": 4, "drawn": 1, "lost": 1,
   "goals_for": 12, "goals_against": 5, "goal_diff": 7, "points": 13,
   "zone": "qualification" }]
```

`zone` ∈ `qualification` | `null`. Le championnat de la 6e édition ne comporte
pas de barrages : la seule zone signalée est la qualification aux quarts de
finale du Grand Prix, sur les 8 premières équipes des 10. Le seuil est porté par
la configuration de la compétition, jamais codé en dur — une édition à 12 équipes
ou un autre critère de qualification ne doit pas demander de modifier le code.
La valeur `barrage` reste réservée pour une édition future.

### `GET /api/teams` et `GET /api/teams/{id}`

La liste renvoie les équipes actives. La fiche renvoie l'équipe, son effectif
groupé par poste et sa position au classement du championnat.

```json
{ "team": { "id": 3, "name": "...", "abbr": "TON", "quarter": "...", "coach": "...",
            "logo": null, "color": "#7A1F30", "founded_year": 2009 },
  "standing": { "rank": 1, "points": 13, "played": 6 },
  "squad": { "GB": [], "DEF": [], "MIL": [], "ATT": [] } }
```

### `GET /api/players/{id}`

Fiche joueur et statistiques dérivées des événements.

```json
{ "player": { "id": 51, "first_name": "...", "last_name": "...", "jersey_number": 9,
              "position": "ATT", "position_label": "Avant-centre", "photo": null,
              "birth_date": "2004-03-11", "height_cm": 181, "strong_foot": "droit",
              "team": { "id": 3, "name": "...", "abbr": "TON" } },
  "stats": { "matches": 6, "goals": 5, "assists": 2, "yellow": 1, "red": 0, "minutes": 480 },
  "by_matchday": [{ "matchday": 1, "goals": 1 }] }
```

### `GET /api/stats/players?metric=goals|assists|cards&competition={slug}&limit=20`

Classement des buteurs, des passeurs ou de la discipline.

```json
[{ "rank": 1, "player": { "id": 51, "name": "...", "photo": null },
   "team": { "id": 3, "abbr": "TON", "name": "...", "color": "#7A1F30" },
   "value": 5, "matches": 6 }]
```

### `GET /api/stats/teams?competition={slug}`

Attaque, défense, possession moyenne et affluence moyenne par équipe.

### `GET /api/news` et `GET /api/news/{slug}`

Actualités publiées uniquement (`published_at` renseigné et passé), les plus
récentes d'abord.

### `GET /api/media?type=photo|video`

Photos et vidéos publiées, les plus récentes d'abord.

### `GET /api/about`

Mission du GFC, présentation de l'édition et contacts.

## Écriture — jeton requis

### `POST /api/auth/login`

```json
// requête
{ "email": "...", "password": "..." }
// réponse 200
{ "token": "…64 caractères…", "expires_at": "2026-08-30T16:00:00+01:00",
  "user": { "id": 2, "name": "...", "role": "arbitre" } }
```

`401` si les identifiants sont invalides ou le compte désactivé.

### `POST /api/matches/{id}/events`

Ajoute un événement et **recalcule le score du match dans la même transaction**.

```json
// requête
{ "type": "goal", "minute": 63, "team_id": 3, "player_id": 51,
  "related_player_id": 55, "detail": null, "is_published": 1 }
// réponse 201
{ "event": { "id": 900, "...": "..." },
  "match": { "id": 42, "home_score": 2, "away_score": 1, "minute": 63, "status": "live" } }
```

Rôles autorisés : `admin`, `secretaire`, `arbitre`. Refus `422` si `player_id`
n'appartient pas à `team_id`, ou si `team_id` ne joue pas ce match.

### `DELETE /api/matches/{id}/events/{eventId}`

Corrige une saisie erronée et recalcule le score. Rôles : `admin`, `secretaire`.

### `PATCH /api/matches/{id}`

Met à jour `status`, `minute`, `attendance`, `referee`, `venue`, `home_pens`,
`away_pens`. Ne permet **jamais** d'écrire `home_score` ni `away_score`
(invariant I1). Rôles : `admin`, `secretaire`, `arbitre` (ce dernier limité à
`status` et `minute`).

### `GET /api/me/matches`

Les matchs que l'opérateur connecté peut saisir, à venir et en cours. C'est
l'écran d'accueil de l'espace opérateur mobile (FR-037).

```json
[{ "id": 42, "competition": { "slug": "championnat", "name": "Championnat" },
   "matchday": 5, "home": { "id": 3, "name": "...", "abbr": "TON" },
   "away": { "id": 7, "name": "...", "abbr": "DJA" },
   "kickoff_at": "2026-08-29T16:00:00+01:00", "venue": "Stade Roumdé Adjia",
   "status": "scheduled", "lineups_ready": false }]
```

`lineups_ready` indique si les deux compositions ont été enregistrées — l'espace
opérateur s'en sert pour signaler ce qui reste à faire avant le coup d'envoi.

### `GET /api/matches/{id}/squads`

Les effectifs des deux équipes du match, pour composer. Ne renvoie que des
joueurs actifs de chaque équipe, ce qui rend impossible d'aligner un joueur
étranger à l'équipe (FR-038).

```json
{ "home": [{ "id": 51, "name": "...", "jersey_number": 9, "position": "ATT" }],
  "away": [] }
```

### `PUT /api/matches/{id}/lineups`

Enregistre la composition d'une équipe. Idempotent : le renvoi remplace
intégralement la composition de cette équipe pour ce match.

```json
// requête
{ "team_id": 3,
  "players": [{ "player_id": 51, "is_starter": true },
              { "player_id": 55, "is_starter": false }] }
```

Refus `422` si un joueur n'appartient pas à `team_id`, si `team_id` ne joue pas
ce match, ou si un même joueur figure deux fois. Rôles : `admin`, `secretaire`,
`arbitre`.

### `PUT /api/matches/{id}/stats`

Statistiques de la rencontre, saisies après le match (FR-040). Idempotent.

```json
{ "team_id": 3, "possession": 56, "shots": 12, "shots_on_target": 5,
  "corners": 4, "fouls": 9, "offsides": 2 }
```

### Idempotence des événements

Pour tenir FR-041 — une saisie faite hors réseau, transmise au retour de la
connexion, ne doit être comptée qu'une fois — `POST /api/matches/{id}/events`
accepte un champ `client_ref` : un identifiant unique généré par l'appareil.

```json
{ "type": "goal", "minute": 63, "team_id": 3, "player_id": 51,
  "client_ref": "a3f1c9e2-..." }
```

Si un événement portant ce `client_ref` existe déjà pour ce match, le serveur
renvoie `200` avec l'événement existant au lieu d'en créer un second. Sans ce
mécanisme, un réseau instable produirait des doublons de buts — exactement ce que
le principe II cherche à empêcher.

### `POST /api/devices`

Enregistre un appareil pour les notifications. Route publique, sans jeton :
l'appareil n'est pas un compte.

```json
{ "expo_token": "ExponentPushToken[…]", "platform": "android", "favourite_team_id": null }
```

Réponse `201` à la création, `200` si le jeton est déjà connu.

## Contrat de latence

`POST /api/matches/{id}/events` doit rendre l'événement visible sur
`GET /api/matches/{id}` immédiatement après sa réponse. Combiné au polling de
15 s côté mobile, cela tient la cible de 20 s du principe I.

# Tasks: Plateforme Garoua Football Challenge

**Input**: Documents de conception de `/specs/001-plateforme-gfc/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/api.md`

**Tests**: la spécification ne demande pas de suite de tests automatisés. La
vérification passe par les scénarios de recette de `quickstart.md` et par les
deux portes automatisées (zéro emoji, zéro SQL concaténé). Les tâches de
vérification sont explicitement nommées.

## Découpe en deux lots

| Lot | Contenu | Échéance |
|---|---|---|
| **Lot 1 — MVP** | Une journée de championnat couverte de bout en bout : administrer, saisir en direct depuis le terrain, suivre sur téléphone, voir le classement bouger. | **Coup d'envoi de la 6e édition** |
| **Lot 2 — Complément** | Ce qui enrichit la compétition une fois qu'elle tourne : talents, statistiques, contenus éditoriaux, notifications. | Pendant l'édition |

La règle de partage : **tout ce qui n'est pas nécessaire pour diffuser le premier
match est dans le lot 2.** Le lot 1 doit rester tenable ; ce qui déborde bascule
vers le lot 2, jamais l'inverse.

**La saisie live se fait depuis le mobile.** Le commissaire, le commentateur ou
l'organisateur saisissent au bord du terrain, sur téléphone (US8). Le back-office
`live.php` est livré comme filet de secours — il est déjà maquetté, donc peu
coûteux — mais ce n'est pas le canal principal.

## Format : `[ID] [P?] [Story] Description`

- **[P]** : parallélisable — fichiers distincts, aucune dépendance
- **[Story]** : histoire utilisateur concernée (US1 à US8)
- **[web]** / **[mobile]** : branche de travail

---

# LOT 1 — MVP

## Phase 1 : Mise en place

- [ ] T001 [web] Poser l'arborescence `backend/` (`config/`, `public/`, `src/`, `sql/`) selon `plan.md`
- [ ] T002 [P] [mobile] Poser l'arborescence `mobile/` et initialiser le projet Expo
- [x] T003 [web] `backend/config/config.php` : lecture de `config.local.php` puis des variables `GFC_*` — aucun secret en dur
- [x] T004 [P] [web] `.htaccess` de production et protection de `config/`, `src/`, `sql/`
- [x] T005 [P] [mobile] Régler `expo.extra.apiUrl` sur `https://gfc.trugroup.cm/api`
- [ ] T006 Script des deux portes qualité (zéro emoji, zéro SQL concaténé), à lancer avant chaque fusion

## Phase 2 : Socle bloquant

Aucune histoire utilisateur ne peut commencer avant.

- [ ] T007 [web] `backend/sql/schema.sql` : les 13 tables de `data-model.md`, InnoDB, `utf8mb4_unicode_ci`, index `(match_id, minute)` sur `match_events`
- [ ] T008 [web] Écart **E1** : `matches.home_pens` et `matches.away_pens`, exclus de `v_standings` — nécessaires au Grand Prix (décision D11)
- [ ] T009 [web] Écart **E2** : valeur `cancelled` dans l'énumération `matches.status`
- [ ] T010 [web] Écart **E5** : `match_events.client_ref` + unicité `(match_id, client_ref)` — idempotence des saisies mobiles hors réseau (FR-041)
- [ ] T011 [web] Vue `v_standings` : matchs `finished` uniquement, dépliés recevant/visiteur, agrégés en points et différence de buts (invariant I2)
- [ ] T012 [P] [web] `backend/sql/seed.sql` — démonstration uniquement, jamais en production
- [ ] T013 [web] `backend/src/Database.php` : PDO, `ATTR_EMULATE_PREPARES => false`, `ERRMODE_EXCEPTION`, `utf8mb4` (principe V)
- [ ] T014 [P] [web] `backend/src/Response.php` : JSON, codes HTTP et messages d'erreur en français
- [ ] T015 [web] `backend/src/Auth.php` : `password_hash`/`password_verify`, session du back-office, jetons d'API expirants, CSRF, contrôle des trois rôles (FR-024 à FR-028, invariant I7)
- [ ] T016 [web] `backend/src/Score.php` : `recompute($matchId)` en transaction avec `SELECT ... FOR UPDATE` sur la ligne du match (invariant I1, écart E3)
- [ ] T017 [web] `backend/src/Repo.php` : requêtes préparées, aucune concaténation SQL
- [ ] T018 [web] Routeur `backend/public/index.php` : dispatch `/api`, en-têtes `Cache-Control`, erreurs centralisées
- [ ] T019 [P] [mobile] `mobile/src/theme.js` : charte issue du logo — seule source de couleurs et de polices (principe III)
- [ ] T020 [P] [mobile] `mobile/src/components/Icon.js` : icônes SVG maison, aucun emoji
- [ ] T021 [P] [mobile] `mobile/src/components/Ui.js` : composants communs, états vides et messages en français (FR-029, FR-032)
- [ ] T022 [P] [mobile] `mobile/src/components/Crest.js` : écusson d'équipe
- [ ] T023 [mobile] `mobile/src/api.js` : client REST, cache mémoire 60 s, repli AsyncStorage avec indicateur « données non à jour », `usePolling(15s)` actif au premier plan seulement (D6, FR-031)
- [ ] T024 [mobile] `mobile/App.js` : navigation publique — 5 onglets et pile

**Point de contrôle** : la base répond, l'API renvoie une route, l'app démarre.

## Phase 3 : US3 — Administrer la compétition (P1)

Sans données saisies, les autres histoires n'ont rien à afficher.

- [ ] T025 [web] [US3] `admin/login.php` : session, CSRF, erreurs en français
- [ ] T026 [web] [US3] Filtre d'autorisation par rôle en tête de chaque page — l'arbitre n'accède qu'à `live.php` (FR-025)
- [ ] T027 [P] [web] [US3] `admin/assets/admin.css` : charte en variables CSS, boutons larges (principe IV)
- [ ] T028 [P] [web] [US3] Sprite d'icônes SVG du back-office — aucun emoji
- [ ] T029 [web] [US3] `admin/index.php` : tableau de bord — indicateurs, prochains matchs, classement
- [ ] T030 [P] [web] [US3] `admin/teams.php` : les 10 équipes (FR-017)
- [ ] T031 [P] [web] [US3] `admin/players.php` : effectifs, postes, numéros, licences (FR-018)
- [ ] T032 [web] [US3] `admin/matches.php` : programmation des 9 journées et des tours de coupe (FR-003, FR-004)
- [ ] T033 [web] [US3] CSRF vérifié sur **chaque** formulaire, échappement systématique en sortie (FR-027)
- [ ] T034 [web] [US3] Créer les 3 compétitions et les comptes opérateurs par script SQL — `competitions.php` et `users.php` sont dans le lot 2
- [ ] T035 [US3] Vérifier les scénarios C du `quickstart.md` : accès par rôle, rejet CSRF

**Point de contrôle** : la compétition existe et s'administre.

## Phase 4 : US1 + US8 — Le direct, saisi depuis le terrain (P1) — MVP

C'est l'incrément qui justifie le produit.

### API

- [ ] T036 [web] `POST /api/auth/login` : jeton et date d'expiration
- [ ] T037 [web] `POST /api/matches/{id}/events` : ajoute l'événement et appelle `Score::recompute()` dans la même transaction ; `422` si le joueur n'est pas de l'équipe ou l'équipe pas du match (FR-007, FR-008)
- [ ] T038 [web] [US8] Idempotence par `client_ref` sur cette route : un `client_ref` déjà vu renvoie `200` et l'événement existant, sans doublon (FR-041, écart E5)
- [ ] T039 [web] `DELETE /api/matches/{id}/events/{eventId}` : correction et recalcul
- [ ] T040 [web] `PATCH /api/matches/{id}` : statut, minute, affluence, arbitre, stade, tirs au but — interdiction d'écrire `home_score`/`away_score` (FR-010, invariant I1)
- [ ] T041 [web] `GET /api/matches/{id}` : match, événements publiés, compositions, statistiques ; `no-cache` si `live` ou `halftime` (invariant I3)
- [ ] T042 [web] `GET /api/matches?scope=upcoming|results` avec filtres
- [ ] T043 [web] [US8] `GET /api/me/matches` : les matchs que l'opérateur connecté peut saisir, avec `lineups_ready` (FR-037)
- [ ] T044 [web] [US8] `GET /api/matches/{id}/squads` : effectifs des deux équipes, joueurs actifs seulement (FR-038)
- [ ] T045 [web] [US8] `PUT /api/matches/{id}/lineups` : composition d'une équipe, idempotent, `422` sur joueur étranger ou doublon (FR-038)
- [ ] T046 [web] [US8] `PUT /api/matches/{id}/stats` : statistiques de rencontre, idempotent (FR-040)
- [ ] T047 [web] Commande de vérification qui recalcule le score de tous les matchs et signale tout écart (invariant I1)
- [ ] T048 [web] Journaliser les suppressions d'événements (écart E4)

### Espace opérateur mobile (US8)

- [ ] T049 [mobile] [US8] Écran de connexion opérateur, cloisonné de la consultation publique — invisible pour un supporter sans compte (FR-036)
- [ ] T050 [mobile] [US8] Stockage du jeton dans le stockage sécurisé de l'appareil, jamais en clair ; déconnexion à l'expiration (FR-042)
- [ ] T051 [mobile] [US8] Écran « Mes matchs » : ce que l'opérateur peut saisir, avec l'état de préparation (FR-037)
- [ ] T052 [mobile] [US8] **Avant le match** — composition des deux équipes, titulaires et remplaçants, choisis dans l'effectif (FR-038)
- [ ] T053 [mobile] [US8] **Pendant le match** — coup d'envoi, minute, boutons d'événement (but, penalty, cartons, remplacement), équipe, joueur, passeur ; utilisable au pouce en une main (FR-039, SC-004)
- [ ] T054 [mobile] [US8] Correction d'un événement saisi par erreur (FR-039, scénario US8-6)
- [ ] T055 [mobile] [US8] File d'attente locale : une saisie hors réseau est conservée puis transmise au retour du réseau, avec son `client_ref` et sa minute d'origine (FR-041, scénario US8-5)
- [ ] T056 [mobile] [US8] **Après le match** — affluence, statistiques de rencontre, clôture (FR-040, scénario US8-7)
- [ ] T057 [web] [US1] `admin/live.php` : saisie live web, conservée comme filet de secours

### Consultation publique

- [ ] T058 [mobile] [US1] `MatchScreen.js` : score live, fil d'événements, compositions, statistiques, rafraîchi toutes les 15 s (FR-009)
- [ ] T059 [mobile] [US1] `HomeScreen.js` : match en direct en tête, sinon prochain match ; raccourci classement (scénarios US1-1 et US1-5)
- [ ] T060 [mobile] [US1] Indicateur visuel de direct et minute de jeu
- [ ] T061 [US1] [US8] Recette de bout en bout, chronomètre en main : saisie depuis le mobile opérateur visible sur un second téléphone en moins de 20 s (SC-001)

**Point de contrôle** : un match se suit en direct pendant qu'un commissaire le saisit depuis son téléphone.

## Phase 5 : US2 — Classement et calendrier (P1)

Format arbitré (décision D11) : aller simple, 9 journées ; pas de barrages ; la
seule zone du classement est la qualification aux quarts, sur les 8 premiers.

- [ ] T062 [web] [US2] `GET /api/standings?competition={slug}` : `v_standings`, tri `points DESC, goal_diff DESC, goals_for DESC, name ASC` (FR-011, FR-012, D4)
- [ ] T063 [web] [US2] `GET /api/competitions` (FR-001)
- [ ] T064 [web] [US2] Seuil de qualification (8 premiers) porté par la configuration de la compétition, jamais codé en dur ; champ `zone` renseigné (FR-013)
- [ ] T065 [P] [mobile] [US2] `StandingsScreen.js` : classement, zone de qualification distinguée
- [ ] T066 [P] [mobile] [US2] `FixturesScreen.js` : calendrier et résultats, filtres par compétition (FR-004)
- [ ] T067 [mobile] [US2] Statuts reporté et annulé visibles, exclus de tout classement (invariant I2)
- [ ] T068 [US2] Vérifier le scénario B du `quickstart.md` : recalcul manuel identique à l'affichage (SC-003)

## Phase 6 : US4 — Équipes et effectifs (P1 dans le lot 1)

Réduit au nécessaire : savoir qui joue. La fiche joueur détaillée est en lot 2.

- [ ] T069 [web] [US4] `GET /api/teams` et `GET /api/teams/{id}` : équipe, effectif groupé par poste, position au classement (FR-019)
- [ ] T070 [P] [mobile] [US4] `TeamsScreen.js` : les 10 équipes avec recherche par nom
- [ ] T071 [P] [mobile] [US4] `SquadScreen.js` : effectif par poste

## Phase 7 : Mise en service du lot 1

- [ ] T072 Émettre le certificat SSL de `gfc.trugroup.cm` (AutoSSL / Let's Encrypt) — **bloquant** : sans lui l'application ne joint pas l'API
- [ ] T073 Créer la base de production, importer `schema.sql`, renseigner `config/config.local.php` sur le serveur
- [ ] T074 Premier déploiement via `deploy/deploy-ftp.sh`, puis vérifications d'après-déploiement de `DEPLOIEMENT.md`
- [ ] T075 Régénérer le mot de passe FTP et celui du compte administrateur ; supprimer le compte de démonstration
- [ ] T076 Saisir les données réelles : 10 équipes, effectifs complets, 9 journées du championnat (SC-007)
- [ ] T077 Créer les comptes des opérateurs de saisie (commissaires, commentateurs, organisateurs)
- [ ] T078 [P] Logo haute définition, icônes et écran de démarrage Android
- [ ] T079 Construire et distribuer l'APK Android
- [ ] T080 Vérifier tous les écrans du lot 1 en état chargement, vide et erreur (FR-032)
- [ ] T081 Vérifier le scénario D du `quickstart.md` : navigation sans réseau (FR-031, SC-006)
- [ ] T082 Vérifier l'affichage des horaires à l'heure de Garoua (cas limite)
- [ ] T083 Exécuter les deux portes automatisées (SC-008, SC-009)
- [ ] T084 Mesurer le premier contenu utile sur Android d'entrée de gamme en 3G — cible sous 3 s (SC-005)
- [ ] T085 Répétition générale : un match de préparation saisi de bout en bout par l'opérateur qui officiera

---

# LOT 2 — Complément

Livré pendant que l'édition se joue. Chaque phase est indépendante des autres.

## Phase 8 : US4bis + US5 — Talents et statistiques (P2)

C'est la mission « vulgariser les talents » rendue visible. Prend son sens après
quelques journées jouées, d'où son placement en lot 2.

- [ ] T086 [web] `GET /api/players/{id}` : fiche et statistiques dérivées des événements, rattachées à `match_events.team_id` (FR-020, invariant I5)
- [ ] T087 [web] Buts par journée d'un joueur, joints à `matches.matchday`
- [ ] T088 [web] `GET /api/stats/players?metric=goals|assists|cards` (FR-014, FR-016)
- [ ] T089 [web] `GET /api/stats/teams` : attaque, défense, possession, affluence (FR-015)
- [ ] T090 [P] [mobile] `PlayerScreen.js` : fiche joueur, statistiques, buts par journée
- [ ] T091 [P] [mobile] `StatsScreen.js` : buteurs, passeurs, discipline / attaque, défense, possession, affluence
- [ ] T092 Vérifier qu'un but contre son camp compte au score de l'adversaire sans entrer dans les buts personnels du joueur
- [ ] T093 Vérifier que le classement des buteurs correspond au décompte des événements

## Phase 9 : US6 — Contenus éditoriaux (P3)

À n'activer que si quelqu'un publie régulièrement : un écran d'actualités vide
fait plus de mal que pas d'écran.

- [ ] T094 [P] [web] `admin/news.php` : rédaction, brouillon, publication (FR-021)
- [ ] T095 [P] [web] `admin/media.php` : envoi (jpg, png, webp, mp4 ≤ 25 Mo) ou URL externe, contrôle d'extension et de taille (FR-022)
- [ ] T096 [web] `GET /api/news`, `/api/news/{slug}`, `/api/media` : contenus publiés uniquement (invariant I4)
- [ ] T097 [web] `GET /api/about` (FR-023)
- [ ] T098 [P] [mobile] `MediaScreen.js` et `AboutScreen.js`
- [ ] T099 [P] [mobile] `CompetitionsScreen.js` : les trois compétitions et leur format
- [ ] T100 [mobile] Fil d'actualités sur l'accueil

## Phase 10 : US7 — Notifications (P3)

- [ ] T101 [web] `POST /api/devices` : enregistrement, `201` à la création, `200` si déjà connu (FR-033)
- [ ] T102 [web] Envoi Expo Push au coup d'envoi, à chaque but et au coup de sifflet final (D10)
- [ ] T103 [mobile] `expo-notifications` : autorisation et enregistrement au premier démarrage
- [ ] T104 Vérifier qu'un but déclenche une notification portant le nouveau score

## Phase 11 : Administration complète

- [ ] T105 [P] [web] `admin/competitions.php` : création et engagement des équipes (FR-002)
- [ ] T106 [P] [web] `admin/users.php` : comptes et rôles depuis l'interface (FR-025)
- [ ] T107 [mobile] Recherche globale équipes et joueurs
- [ ] T108 Arbitrer FR-035 (critère de qualification aux quarts) et le cas du vainqueur unique en Super Coupe, puis répercuter

---

## Dépendances

- **Phase 1 → 2 → tout le reste.** `Score.php` (T016) et `Auth.php` (T015) conditionnent US1, US3 et US8 ; `api.js` (T023) conditionne tous les écrans.
- **US3 (phase 3) → US1, US8, US2, US4** : sans données saisies, rien à afficher.
- **T036 à T046 (API) → T049 à T060 (écrans)** : l'API précède toujours l'écran qui la consomme (règle de la constitution).
- **T038 (idempotence serveur) → T055 (file d'attente mobile)** : la file locale est inutilisable sans garantie côté serveur.
- **T072 (certificat SSL) → T079 (APK)** : ne pas distribuer une application qui ne peut pas joindre l'API.
- **Lot 1 complet → Lot 2.** La phase 10 dépend en plus du direct (phase 4).

## Parallélisation

- T019 à T022 (thème, icônes, composants) sont indépendants de tout le travail `web` : la branche `mobile` démarre dès la phase 1.
- T030, T031 et les pages du back-office touchent des fichiers distincts.
- Une fois l'API d'une histoire livrée, ses écrans marqués `[P]` se font en parallèle.
- Dans le lot 2, les phases 8, 9, 10 et 11 sont indépendantes.

## Ce qui est démontrable, et quand

| Après | Démonstration |
|---|---|
| Phase 3 | La compétition s'administre |
| Phase 4 | **MVP** — un match saisi au terrain depuis un téléphone, suivi en direct sur un autre |
| Phase 6 | Le lot 1 est fonctionnellement complet |
| Phase 7 | **Prêt pour le coup d'envoi** |
| Phase 8 | Buteurs et fiches joueurs — les talents deviennent visibles |
| Phases 9-11 | Contenus, notifications, administration complète |

# Tasks: Plateforme Garoua Football Challenge

**Input**: Documents de conception de `/specs/001-plateforme-gfc/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/api.md`

**Tests**: la spécification ne demande pas de suite de tests automatisés. La
vérification passe par les scénarios de recette de `quickstart.md` et par les
deux portes automatisées (zéro emoji, zéro SQL concaténé). Les tâches de
vérification sont explicitement nommées.

## Format : `[ID] [P?] [Story] Description`

- **[P]** : parallélisable — fichiers distincts, aucune dépendance
- **[Story]** : histoire utilisateur concernée (US1 à US7)
- **[web]** / **[mobile]** : branche de travail

## Conventions de chemins

- Branche `web` : `backend/src/`, `backend/public/`, `backend/sql/`
- Branche `mobile` : `mobile/src/`, `mobile/App.js`

---

## Phase 1 : Mise en place

**Objectif** : les deux branches démarrent et communiquent.

- [ ] T001 [web] Poser l'arborescence `backend/` (`config/`, `public/`, `src/`, `sql/`) selon `plan.md`
- [ ] T002 [P] [mobile] Poser l'arborescence `mobile/` et initialiser le projet Expo (`package.json`, `app.json`, `babel.config.js`)
- [ ] T003 [web] Écrire `backend/config/config.php` : lecture des variables `GFC_DB_HOST`, `GFC_DB_NAME`, `GFC_DB_USER`, `GFC_DB_PASS`, `GFC_BASE_URL` — aucun secret en dur
- [ ] T004 [P] [web] Écrire `backend/public/.htaccess` (réécriture vers `index.php`) et documenter l'équivalent Nginx dans `backend/README.md`
- [ ] T005 [P] [mobile] Régler `expo.extra.apiUrl` dans `mobile/app.json` et documenter `10.0.2.2` pour l'émulateur Android
- [ ] T006 Écrire les deux portes qualité de `quickstart.md` (zéro emoji, zéro SQL concaténé) comme script exécutable, à lancer avant chaque fusion

---

## Phase 2 : Socle bloquant

**Objectif** : le modèle de données, l'accès base, l'authentification et le thème.
Aucune histoire utilisateur ne peut commencer avant.

- [ ] T007 [web] Écrire `backend/sql/schema.sql` : les 13 tables de `data-model.md`, InnoDB, `utf8mb4_unicode_ci`, index `(match_id, minute)` sur `match_events`
- [ ] T008 [web] Appliquer l'écart **E1** : ajouter `matches.home_pens` et `matches.away_pens` (TINYINT UNSIGNED, NULL), exclus de `v_standings`
- [ ] T009 [web] Appliquer l'écart **E2** : ajouter la valeur `cancelled` à l'énumération `matches.status`
- [ ] T010 [web] Créer la vue `v_standings` dans `schema.sql` : matchs `finished` uniquement, dépliés recevant/visiteur, agrégés en points, différence de buts et bilan (invariant I2)
- [ ] T011 [P] [web] Écrire `backend/sql/seed.sql` — données de démonstration clairement identifiées comme telles, jamais destinées à la production
- [ ] T012 [web] Écrire `backend/src/Database.php` : PDO singleton, `ATTR_EMULATE_PREPARES => false`, `ATTR_ERRMODE => EXCEPTION`, `utf8mb4` (principe V)
- [ ] T013 [P] [web] Écrire `backend/src/Response.php` : réponses JSON, codes HTTP et messages d'erreur en français selon `contracts/api.md`
- [ ] T014 [web] Écrire `backend/src/Auth.php` : `password_hash`/`password_verify`, session du back-office, jetons d'API expirants (`api_tokens`), jeton CSRF, contrôle de rôle admin/secrétaire/arbitre (FR-024 à FR-028, invariant I7)
- [ ] T015 [web] Écrire `backend/src/Score.php` : `recompute($matchId)` — score dérivé des événements `goal`, `penalty`, `own_goal` (au crédit de l'adversaire), en transaction avec `SELECT ... FOR UPDATE` sur la ligne du match (invariant I1, écart E3)
- [ ] T016 [web] Écrire le squelette de `backend/src/Repo.php` : requêtes préparées de lecture et d'écriture, aucune concaténation SQL
- [ ] T017 [web] Écrire le routeur `backend/public/index.php` : dispatch des routes `/api`, en-têtes `Cache-Control`, gestion centralisée des erreurs
- [ ] T018 [P] [mobile] Écrire `mobile/src/theme.js` : bordeaux, orange, crème issus du logo ; Anton pour les titres, Manrope pour le texte — seule source de couleurs et de polices (principe III)
- [ ] T019 [P] [mobile] Écrire `mobile/src/components/Icon.js` : jeu d'icônes SVG maison via `react-native-svg`, aucun emoji, aucune bibliothèque d'icônes tierce
- [ ] T020 [P] [mobile] Écrire `mobile/src/components/Ui.js` : `Card`, `SectionTitle`, `Chip`, `Segmented`, `LiveDot`, `StatBar`, `MetricRow`, `Loader`, `EmptyState` — états vides et messages en français (FR-029, FR-032)
- [ ] T021 [P] [mobile] Écrire `mobile/src/components/Crest.js` : écusson d'équipe, logo ou abréviation sur la couleur du club
- [ ] T022 [mobile] Écrire `mobile/src/api.js` : client REST, cache mémoire 60 s, repli AsyncStorage avec indicateur « données non à jour », `usePolling(15s)` actif seulement au premier plan (décision D6, FR-031)
- [ ] T023 [mobile] Écrire `mobile/App.js` : 5 onglets et la pile de navigation (match, effectif, joueur, médias, compétitions, à propos), en-tête commun

**Point de contrôle** : la base répond, l'API renvoie une route de test, l'app mobile démarre sur un écran vide thémé.

---

## Phase 3 : US3 — Saisir et administrer la compétition (P1)

**Objectif** : les données réelles de la 6e édition peuvent être saisies. Sans
cette phase, les autres histoires n'ont rien à afficher.

- [ ] T024 [web] [US3] `backend/public/admin/login.php` : connexion par session, jeton CSRF, message d'erreur en français
- [ ] T025 [web] [US3] Filtre d'autorisation par rôle appliqué en tête de chaque page du back-office — l'arbitre n'accède qu'à `live.php` (FR-025, scénario US3-1)
- [ ] T026 [P] [web] [US3] `backend/public/admin/assets/admin.css` : charte en variables CSS, boutons larges pour la saisie à une main (principe IV)
- [ ] T027 [P] [web] [US3] Sprite d'icônes SVG du back-office — aucun emoji (principe III)
- [ ] T028 [web] [US3] `backend/public/admin/index.php` : tableau de bord — indicateurs, prochains matchs, classement en direct
- [ ] T029 [P] [web] [US3] `backend/public/admin/competitions.php` : création de compétition et engagement des équipes (FR-001, FR-002)
- [ ] T030 [P] [web] [US3] `backend/public/admin/teams.php` : fiches d'équipe — nom, abréviation, quartier, couleurs, écusson, entraîneur (FR-017)
- [ ] T031 [P] [web] [US3] `backend/public/admin/players.php` : effectifs, postes, numéros, licences (FR-018)
- [ ] T032 [web] [US3] `backend/public/admin/matches.php` : programmation — date, heure, stade, arbitre, compétition, journée ou tour (FR-003, FR-004)
- [ ] T033 [P] [web] [US3] `backend/public/admin/users.php` : comptes et rôles, condensats de mots de passe (FR-025, FR-028)
- [ ] T034 [web] [US3] Jeton CSRF vérifié sur **chaque** formulaire du back-office, échappement systématique en sortie (FR-027, scénario US3-2)
- [ ] T035 [web] [US3] Vérifier les scénarios C du `quickstart.md` : accès par rôle, rejet CSRF

**Point de contrôle** : une compétition, deux équipes, deux effectifs et un match programmé existent et ressortent dans l'API.

---

## Phase 4 : US1 — Suivre un match en direct (P1) 🎯 MVP

**Objectif** : la chaîne complète saisie → API → mobile, en moins de 20 secondes.
C'est l'incrément démontrable.

- [ ] T036 [web] [US1] `POST /api/auth/login` : renvoie un jeton et sa date d'expiration (`contracts/api.md`)
- [ ] T037 [web] [US1] `POST /api/matches/{id}/events` : ajoute l'événement et appelle `Score::recompute()` dans la même transaction ; refuse en `422` un joueur hors de son équipe ou une équipe hors du match (FR-007, FR-008)
- [ ] T038 [web] [US1] `DELETE /api/matches/{id}/events/{eventId}` : correction d'une saisie et recalcul (scénario US1-4)
- [ ] T039 [web] [US1] `PATCH /api/matches/{id}` : statut, minute, affluence, arbitre, stade, tirs au but — interdiction absolue d'écrire `home_score` et `away_score` (FR-010, invariant I1)
- [ ] T040 [web] [US1] `GET /api/matches/{id}` : match, fil d'événements publiés seulement, compositions, statistiques ; `Cache-Control: no-cache` si le match est `live` ou `halftime` (invariant I3)
- [ ] T041 [web] [US1] `GET /api/matches?scope=upcoming|results` avec filtres `competition` et `team`
- [ ] T042 [web] [US1] `backend/public/admin/live.php` : sélection du match, boutons d'événement (but, penalty, cartons, remplacement), minute, équipe, joueur, passeur ; score et classement recalculés automatiquement (FR-007, SC-004)
- [ ] T043 [web] [US1] Écrire une commande de vérification qui recalcule le score de tous les matchs et signale tout écart (invariant I1)
- [ ] T044 [web] [US1] Journaliser les suppressions d'événements pour rendre les corrections auditables (écart E4)
- [ ] T045 [mobile] [US1] `mobile/src/screens/MatchScreen.js` : score live, fil d'événements, compositions, statistiques, rafraîchi toutes les 15 s (FR-009)
- [ ] T046 [mobile] [US1] `mobile/src/screens/HomeScreen.js` : match en direct en tête, ou prochain match programmé ; raccourcis classement et meilleur buteur (scénarios US1-1 et US1-5)
- [ ] T047 [mobile] [US1] Indicateur visuel de direct (`LiveDot`) et affichage de la minute de jeu
- [ ] T048 [US1] Vérifier le scénario A du `quickstart.md` de bout en bout, chronomètre en main : but saisi visible en moins de 20 s (SC-001)

**Point de contrôle** : MVP démontrable — un match se suit en direct sur téléphone pendant qu'un opérateur le saisit.

---

## Phase 5 : US2 — Classement et calendrier (P1)

**Objectif** : l'application a une valeur permanente entre deux matchs.

Format arbitré le 2026-08-29 : championnat en aller simple (9 journées, 45 matchs),
Grand Prix à partir des quarts de finale (8 équipes, 7 matchs), Super Coupe en
rencontre unique. Plus de barrages — la seule zone à signaler est la
qualification aux quarts, sur les 8 premiers.

- [ ] T049 [web] [US2] `GET /api/standings?competition={slug}` : lecture de `v_standings`, tri `points DESC, goal_diff DESC, goals_for DESC, name ASC`, rang calculé à la lecture (FR-011, FR-012, décision D4)
- [ ] T050 [web] [US2] `GET /api/competitions` : les compétitions de la saison courante (FR-001)
- [ ] T051 [web] [US2] Porter le seuil de qualification (8 premiers) dans la configuration de la compétition et renseigner `zone` dans la réponse du classement — plus de zone de barrage (FR-013, décision D11) ; le critère exact reste suspendu à FR-035
- [ ] T052 [P] [mobile] [US2] `mobile/src/screens/StandingsScreen.js` : classement complet, zone de qualification aux quarts distinguée visuellement (scénarios US2-1 et US2-2)
- [ ] T053 [P] [mobile] [US2] `mobile/src/screens/FixturesScreen.js` : calendrier et résultats, filtres par compétition (scénario US2-3)
- [ ] T054 [P] [mobile] [US2] `mobile/src/screens/CompetitionsScreen.js` : Championnat, Grand Prix Gabriel MBAÏROBÉ, Super Coupe
- [ ] T055 [mobile] [US2] Afficher les statuts reporté et annulé dans le calendrier, exclus de tout classement (cas limite, invariant I2)
- [ ] T056 [US2] Vérifier le scénario B du `quickstart.md` : recalcul manuel du classement identique à l'affichage (SC-003)

---

## Phase 6 : US4 — Équipes et joueurs (P2)

- [ ] T057 [web] [US4] `GET /api/teams` et `GET /api/teams/{id}` : équipe, effectif groupé par poste, position au classement (FR-019)
- [ ] T058 [web] [US4] `GET /api/players/{id}` : fiche et statistiques dérivées des événements, rattachées à `match_events.team_id` (FR-020, invariant I5)
- [ ] T059 [web] [US4] Buts par journée d'un joueur, joints à `matches.matchday`
- [ ] T060 [P] [mobile] [US4] `mobile/src/screens/TeamsScreen.js` : les 10 équipes avec recherche par nom (scénario US4-1)
- [ ] T061 [P] [mobile] [US4] `mobile/src/screens/SquadScreen.js` : effectif par poste et indicateurs de l'équipe (scénario US4-2)
- [ ] T062 [P] [mobile] [US4] `mobile/src/screens/PlayerScreen.js` : fiche joueur, statistiques, buts par journée (scénario US4-3)
- [ ] T063 [US4] Vérifier qu'un but contre son camp compte au score de l'adversaire sans entrer dans les buts personnels du joueur

---

## Phase 7 : US5 — Classements statistiques (P2)

- [ ] T064 [web] [US5] `GET /api/stats/players?metric=goals|assists|cards` : agrégats sur `match_events` (FR-014, FR-016)
- [ ] T065 [web] [US5] `GET /api/stats/teams` : attaque et défense depuis `v_standings`, possession depuis `match_team_stats`, affluence depuis `matches.attendance` (FR-015)
- [ ] T066 [mobile] [US5] `mobile/src/screens/StatsScreen.js` : deux vues segmentées — joueurs (buteurs, passeurs, discipline) et équipes (attaque, défense, possession, affluence)
- [ ] T067 [US5] Vérifier que le classement des buteurs correspond au décompte des événements `goal` et `penalty`

---

## Phase 8 : US6 — Actualités et médias (P3)

- [ ] T068 [P] [web] [US6] `backend/public/admin/news.php` : rédaction, brouillon, publication (FR-021)
- [ ] T069 [P] [web] [US6] `backend/public/admin/media.php` : envoi de fichier (jpg, png, webp, mp4 ≤ 25 Mo) ou URL externe, contrôle d'extension et de taille avec message de refus en français (FR-022, scénario US3-4)
- [ ] T070 [web] [US6] `GET /api/news`, `GET /api/news/{slug}` et `GET /api/media?type=` : contenus publiés uniquement (invariant I4)
- [ ] T071 [web] [US6] `GET /api/about` : mission du GFC et contacts (FR-023)
- [ ] T072 [P] [mobile] [US6] `mobile/src/screens/MediaScreen.js` : bascule photos et vidéos
- [ ] T073 [P] [mobile] [US6] `mobile/src/screens/AboutScreen.js` : mission du GFC et contacts
- [ ] T074 [mobile] [US6] Fil d'actualités sur l'accueil, les plus récentes d'abord

---

## Phase 9 : US7 — Notifications (P3)

- [ ] T075 [web] [US7] `POST /api/devices` : enregistrement d'un appareil, route publique, `201` à la création et `200` si déjà connu (FR-033)
- [ ] T076 [web] [US7] Envoi vers Expo Push depuis PHP au coup d'envoi, à chaque but et au coup de sifflet final (décision D10)
- [ ] T077 [mobile] [US7] Intégrer `expo-notifications` : demande d'autorisation et enregistrement au premier démarrage (scénario US7-1)
- [ ] T078 [US7] Vérifier qu'un but saisi déclenche une notification portant le nouveau score (scénario US7-2)

---

## Phase 10 : Finition et mise en service

- [ ] T079 Vérifier tous les écrans mobiles en état chargement, vide et erreur (FR-032)
- [ ] T080 Vérifier le scénario D du `quickstart.md` : navigation sans réseau, dernier contenu servi avec indication (FR-031, SC-006)
- [ ] T081 Vérifier que les horaires s'affichent à l'heure de Garoua quelle que soit la configuration de l'appareil (cas limite)
- [ ] T082 Exécuter les deux portes automatisées : zéro emoji, zéro SQL concaténé (SC-008, SC-009)
- [ ] T083 Mesurer le temps de premier contenu utile sur Android d'entrée de gamme en 3G — cible sous 3 s (SC-005)
- [ ] T084 [P] Remplacer `mobile/assets/logo.png` et le logo du back-office par le logo haute définition, générer les icônes et l'écran de démarrage Android
- [ ] T085 Saisir les données réelles de la 6e édition : 10 équipes, effectifs complets, calendrier (SC-007)
- [ ] T086 Durcir la production : régénérer le condensat du compte administrateur, retirer le jeu de démonstration, servir `public/` comme racine web, variables `GFC_*` renseignées hors dépôt
- [ ] T087 [P] Mettre à jour `backend/README.md` et `mobile/README.md` si une commande de démarrage ou le contrat d'API a changé

---

## Dépendances

- **Phase 1 → Phase 2** : l'arborescence et la configuration précèdent tout.
- **Phase 2 → toutes les histoires** : bloquante. `Score.php` (T015) et `Auth.php` (T014) conditionnent US1 et US3 ; `api.js` (T022) conditionne tous les écrans.
- **US3 (phase 3) → US1, US2, US4, US5** : sans données saisies, rien à afficher.
- **US1 (phase 4) → US7** : les notifications se branchent sur les événements du direct.
- **US2 (phase 5)** : plus aucun blocage, le format a été arbitré (décision D11).
- **US4 (phase 6) → US5 (phase 7)** : les statistiques réutilisent les agrégats de joueur.
- Au sein d'une même histoire, l'API précède toujours l'écran qui la consomme (règle de la constitution).

## Parallélisation

- T018 à T021 (thème, icônes, composants mobiles) sont indépendants entre eux et de tout le travail `web` — la branche `mobile` peut démarrer dès la phase 1.
- T029 à T031 et T033 touchent des pages distinctes du back-office.
- Une fois la route d'une histoire livrée, ses écrans mobiles marqués `[P]` se font en parallèle.
- Les phases 6, 7 et 8 sont indépendantes les unes des autres une fois US3 terminée.

## Périmètre livré par incrément

| Après la phase | Ce qui est démontrable |
|---|---|
| 3 (US3) | La compétition existe et s'administre |
| 4 (US1) | **MVP** — un match se suit en direct sur téléphone |
| 5 (US2) | Classement et calendrier complets |
| 6-7 (US4, US5) | Talents et statistiques mis en avant |
| 8-9 (US6, US7) | Contenus éditoriaux et rappels |
| 10 | Prêt pour la 6e édition |

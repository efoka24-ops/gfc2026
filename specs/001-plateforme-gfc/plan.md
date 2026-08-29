# Implementation Plan: Plateforme Garoua Football Challenge

**Branch**: `001-plateforme-gfc` | **Date**: 2026-08-29 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-plateforme-gfc/spec.md`

## Summary

Livrer la plateforme du Garoua Football Challenge en trois blocs qui partagent un
seul modèle de données : une API REST publique en PHP 8 sur MySQL 8, un
back-office d'administration et de saisie live servi depuis la même application
PHP, et une application mobile React Native (Expo) en français qui consomme
uniquement l'API.

L'approche technique repose sur un choix structurant : **rien n'est stocké de ce
qui peut être dérivé**. Le score d'un match est recalculé depuis
`match_events` à chaque écriture d'événement, le classement est une vue SQL
(`v_standings`) sur les seuls matchs terminés, les statistiques de joueurs sont
des agrégats sur les événements. Le direct est obtenu par polling HTTP de 15 s
côté mobile plutôt que par WebSocket : la contrainte de latence (20 s) l'autorise,
et cela évite d'imposer un serveur à état à un hébergement PHP mutualisé.

## Technical Context

**Language/Version**: PHP 8.1+ (API et back-office) ; JavaScript ES2021 / React
Native via Expo SDK (mobile)

**Primary Dependencies**: aucune dépendance applicative côté PHP — PDO et les
sessions natives uniquement. Côté mobile : `expo`, `react-navigation`,
`react-native-svg` (jeu d'icônes maison), `@react-native-async-storage/async-storage`
(cache hors-ligne), `expo-notifications` (phase notifications).

**Storage**: MySQL 8, InnoDB, `utf8mb4_unicode_ci` ; fichiers médias sur le
système de fichiers sous `public/uploads/`.

**Testing**: tests de contrat d'API par scénarios HTTP rejouables (buts saisis →
score et classement attendus) ; vérification manuelle guidée par `quickstart.md`
pour les écrans mobiles ; contrôle automatisé « zéro emoji » et « zéro SQL
concaténé » en porte de fusion.

**Target Platform**: Android 8+ en priorité (iOS possible via le même code Expo) ;
serveur PHP 8.1 + MySQL 8, Apache ou Nginx.

**Project Type**: Mobile + API — application mobile, service API et back-office web.

**Performance Goals**: événement saisi visible sur mobile en moins de 20 s (P95) ;
premier contenu utile affiché en moins de 3 s sur Android d'entrée de gamme en 3G ;
réponse d'API en moins de 300 ms sur les routes de lecture.

**Constraints**: fonctionne en réseau dégradé, avec repli sur le dernier contenu
en cache ; aucun emoji dans l'interface ; back-office de saisie live utilisable au
pouce en une main ; aucun secret dans le dépôt.

**Scale/Scope**: une édition, 3 compétitions, 10 équipes, ~250 joueurs, quelques
dizaines de matchs par édition, quelques milliers d'utilisateurs mobiles ;
11 écrans mobiles et 9 pages de back-office.

## Constitution Check

*GATE: à valider avant la phase 0 et à revérifier après la phase 1.*

| Principe | Comment le plan le respecte | Statut |
|---|---|---|
| I. Le direct est la promesse produit | Polling 15 s côté mobile (`usePolling`) sur la fiche de match et l'accueil ; le back-office écrit l'événement en une requête et recalcule le score dans la même transaction ; cache serveur invalidé à l'écriture. Marge de 5 s sur la cible de 20 s. | ✅ |
| II. Une seule source de vérité | `match_events` est la seule écriture ; `matches.home_score` / `away_score` sont un cache dérivé recalculé par `Score::recompute()` et jamais saisi à la main ; classement en vue SQL `v_standings` ; statistiques joueurs agrégées à la lecture. | ✅ |
| III. Icônes vectorielles, zéro emoji | Jeu d'icônes SVG maison (`mobile/src/components/Icon.js`) et sprite SVG côté back-office ; charte centralisée dans `mobile/src/theme.js` et les variables CSS de `admin.css` ; contrôle automatisé en porte de fusion. | ✅ |
| IV. Français d'abord, terrain d'abord | Toutes les chaînes en français, y compris erreurs et états vides ; cache AsyncStorage pour le mode dégradé ; saisie live en boutons larges à une main. | ✅ |
| V. Sécurité et intégrité | PDO préparé partout avec `ATTR_EMULATE_PREPARES => false` ; `password_hash`/`password_verify` ; jetons d'API expirants ; CSRF sur chaque formulaire ; échappement systématique en sortie ; contrôle d'extension et de taille à l'upload ; autorisations par rôle sur chaque page et chaque route d'écriture. | ✅ |

**Contraintes techniques de la constitution** : PHP 8.1 + MySQL 8 sans framework ✅ ;
React Native Expo sans bibliothèque UI lourde ✅ ; API `/api` seul point de contact ✅ ;
branches `main` / `mobile` / `web` ✅ ; configuration par `GFC_*` et `expo.extra` ✅.

**Verdict** : aucune violation. La section Complexity Tracking reste vide.

## Project Structure

### Documentation (this feature)

```text
specs/001-plateforme-gfc/
├── plan.md              # ce fichier
├── spec.md              # spécification fonctionnelle
├── research.md          # décisions techniques et alternatives écartées
├── data-model.md        # entités, règles de dérivation, invariants
├── quickstart.md        # démarrage local et scénario de recette
├── contracts/
│   └── api.md           # contrat de l'API REST
└── tasks.md             # produit par /speckit-tasks
```

### Source Code (repository root)

Le dépôt `efoka24-ops/gfc2026` porte trois branches de longue durée :
`main` (documentation, spécification, schéma partagé), `web` (API et back-office),
`mobile` (application React Native). Chaque branche contient l'arborescence
complète ci-dessous ; les branches de fonctionnalité partent de `web` ou `mobile`
selon leur périmètre.

```text
backend/                          # branche web
├── config/
│   └── config.php                # lecture des variables GFC_*, aucun secret commité
├── public/
│   ├── index.php                 # routeur de l'API REST /api
│   ├── .htaccess                 # réécriture Apache
│   ├── uploads/                  # médias envoyés (hors dépôt)
│   └── admin/                    # back-office
│       ├── login.php  index.php  live.php
│       ├── competitions.php  teams.php  players.php
│       ├── matches.php  news.php  media.php  users.php
│       └── assets/               # admin.css, sprite d'icônes SVG, logo
├── src/
│   ├── Database.php              # PDO, ATTR_EMULATE_PREPARES => false
│   ├── Auth.php                  # sessions, rôles, jetons d'API, CSRF
│   ├── Repo.php                  # requêtes de lecture et d'écriture
│   ├── Score.php                 # recalcul du score depuis les événements
│   └── Response.php              # JSON, codes HTTP, erreurs en français
└── sql/
    ├── schema.sql                # 13 tables + vue v_standings
    └── seed.sql                  # données de démonstration uniquement

mobile/                           # branche mobile
├── App.js                        # 5 onglets + pile de navigation
├── app.json                      # expo.extra.apiUrl
├── src/
│   ├── theme.js                  # bordeaux, orange, crème ; Anton + Manrope
│   ├── api.js                    # client REST, cache 60 s, repli AsyncStorage, usePolling 15 s
│   ├── components/               # Icon.js (SVG), Ui.js, Crest.js
│   └── screens/                  # Home, Fixtures, Match, Standings, Teams,
│                                 # Squad, Player, Stats, Media, Competitions, About
└── assets/                       # logo et icônes d'application
```

**Structure Decision**: structure « Mobile + API ». Le back-office n'est pas un
projet séparé : il partage le dossier `public/` et surtout la couche `src/` de
l'API, ce qui garantit qu'un score saisi au back-office et un score lu par l'API
passent par le même `Score::recompute()` — l'invariant du principe II serait
fragilisé par deux chemins d'écriture distincts. Le mobile est isolé sur sa
branche : il ne connaît que l'URL de l'API.

## Phasage de livraison

Le phasage suit les priorités de la spécification, en livrant toujours l'API
avant l'écran qui la consomme.

- **Phase 0 — Socle** : schéma MySQL, configuration, PDO, réponses JSON,
  authentification et rôles, thème et navigation mobile. Rien de visible, tout
  en dépend.
- **Phase 1 — Administration (US3, P1)** : back-office complet — compétitions,
  équipes, joueurs, matchs, utilisateurs. Permet de saisir les données réelles
  de la 6e édition. Testable seul.
- **Phase 2 — Direct (US1, P1)** : saisie live, recalcul du score, routes de
  match et d'événements, écrans Accueil et Match avec polling. C'est le MVP
  démontrable.
- **Phase 3 — Classement et calendrier (US2, P1)** : vue `v_standings`, routes
  classement et calendrier, écrans Classement et Calendrier avec filtres.
- **Phase 4 — Équipes et joueurs (US4, P2)** : routes équipes, effectifs,
  joueurs ; écrans Équipes, Effectif, Joueur.
- **Phase 5 — Statistiques (US5, P2)** : agrégats buteurs, passeurs, discipline,
  statistiques d'équipe ; écran Statistiques.
- **Phase 6 — Contenus (US6, P3)** : actualités, médiathèque, page À propos.
- **Phase 7 — Notifications (US7, P3)** : enregistrement d'appareil, envoi au
  coup d'envoi, au but et à la fin du match.
- **Phase 8 — Mise en service** : données réelles de l'édition, logo haute
  définition, génération des icônes Android, durcissement de la configuration de
  production.

## Points ouverts bloquants pour les phases 3 et suivantes

Deux `[NEEDS CLARIFICATION]` de la spécification (FR-005, FR-006) portent sur le
format des compétitions. Ils n'empêchent ni la phase 1 ni la phase 2 : le modèle
de données porte déjà `matchday`, `round_label` et `group_name`, qui couvrent
aussi bien un championnat que des tours à élimination directe. Ils doivent être
tranchés avant la phase 3 (règles de qualification et de barrages au classement)
et avant la phase 8. Hypothèse de travail retenue en attendant, documentée dans
`research.md` : championnat à aller simple entre les 10 équipes (9 journées), et
Grand Prix Gabriel MBAÏROBÉ en matchs secs à élimination directe avec tirs au but
en cas d'égalité.

## Complexity Tracking

> Sans objet — le Constitution Check ne relève aucune violation.

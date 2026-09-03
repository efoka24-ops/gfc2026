# Garoua Football Challenge — Backend, back office & PWA

Projet PHP **8.1** sans framework (PDO/MySQL), pensé pour un hébergement mutualisé.

## Contenu

    php/
      public/                  racine web (à pointer avec le vhost / DocumentRoot)
        index.php              front controller unique (API + back office + PWA)
        manifest.webmanifest   manifeste PWA
        sw.js                  service worker (cache offline + fallback)
        assets/                css, js, images
      config/config.php        configuration (BD, session, JWT-less tokens)
      database/schema.sql      schéma complet (25 tables + vue classement)
      database/seed.sql        données de la 6e édition (10 équipes, matchs, buteurs…)
      src/Core/                Database, Router, Request, Response, Auth, View, Validator
      src/Repository/          accès données par domaine
      src/Controller/Api/      endpoints JSON consommés par la PWA / React Native
      src/Controller/Admin/    back office (pages PHP rendues serveur)
      templates/               layout + vues du back office
      storage/                 uploads (logos, photos, médias) — à rendre inscriptible

## Installation

1. Créer la base :

       mysql -u root -p -e "CREATE DATABASE gfc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
       mysql -u root -p gfc < database/schema.sql
       mysql -u root -p gfc < database/seed.sql

2. Copier `config/config.php` et renseigner les identifiants MySQL + `app_key`.

3. Pointer le DocumentRoot sur `php/public`. En mutualisé sans possibilité de changer
   la racine, remonter le contenu de `public/` à la racine du domaine et adapter
   `define('BASE_PATH', ...)` dans `public/index.php`.

4. Droits d'écriture :

       chmod -R 775 storage

5. Comptes de démonstration (mot de passe : `gfc2026`) :

   | Rôle | Identifiant |
   |---|---|
   | Administrateur | `+237690000001` |
   | Délégué (Étoile du Nord) | `+237677000002` |
   | Arbitre | `+237696000003` |

## API

Toutes les réponses sont en JSON, préfixe `/api`.

    GET  /api/edition                       édition courante + compteurs
    GET  /api/competitions                  compétitions et phases
    GET  /api/teams                         liste des équipes
    GET  /api/teams/{id}                    fiche équipe + effectif + palmarès
    GET  /api/standings?competition=1       classement (calculé)
    GET  /api/matches?status=live           calendrier / résultats
    GET  /api/matches/{id}                  feuille de match + événements + stats
    GET  /api/players/top-scorers           meilleurs buteurs
    GET  /api/news                          actualités publiées
    GET  /api/media                         galerie
    GET  /api/palmares                      palmarès des éditions
    GET  /api/sponsors                      sponsors actifs par emplacement
    POST /api/registrations                 inscription d'une équipe
    POST /api/auth/otp                      demande de code SMS (supporter)
    POST /api/auth/verify                   vérification du code → token
    GET  /api/me/favorites                  équipes suivies            (token)
    POST /api/me/favorites                  suivre / ne plus suivre    (token)
    POST /api/me/devices                    enregistrer un token push  (token)

Écriture réservée au back office et aux arbitres (session ou token staff) :

    POST /api/matches/{id}/events           ajouter un événement de feuille de match
    DELETE /api/matches/{id}/events/{eid}   supprimer un événement
    POST /api/matches/{id}/status           coup d'envoi, mi-temps, fin de match
    POST /api/matches/{id}/validate         valider la feuille (admin)

## Back office

    /admin/login
    /admin                      tableau de bord
    /admin/live                 saisie en direct (feuille de match numérique)
    /admin/competitions         compétitions & phases
    /admin/calendar             calendrier
    /admin/standings            classements
    /admin/sanctions            sanctions
    /admin/teams                équipes & effectifs
    /admin/players              joueurs & statistiques
    /admin/users                utilisateurs & rôles
    /admin/news                 actualités & médias
    /admin/tickets              billetterie
    /admin/sponsors             sponsors

Les permissions sont centralisées dans `src/Core/Auth.php::can()` :
l'arbitre n'accède qu'à la saisie et aux sanctions, le délégué à son équipe.

## PWA

`public/sw.js` met en cache la coquille de l'app et les dernières réponses
`/api/*` (stratégie *network-first*, repli sur le cache hors ligne). Les scores
en direct sont rafraîchis par *polling* (`/api/matches?status=live`, 15 s) ;
remplacer par SSE (`/api/stream`) si le serveur autorise les connexions longues.

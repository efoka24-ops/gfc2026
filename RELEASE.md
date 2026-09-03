# Garoua Football Challenge — Release v1.0.0

Première version en production de la plateforme (site public + API + back-office),
sur le socle PHP 8.1 sans framework.

## Accès en ligne

| Élément | URL |
|---|---|
| Site public | http://gfc.trugroup.cm/ |
| API JSON | http://gfc.trugroup.cm/api/… |
| Back-office | http://gfc.trugroup.cm/admin/login |

> À faire avant l'ouverture au public : émettre le certificat **AutoSSL** pour
> `gfc.trugroup.cm` dans cPanel (le serveur présente encore un certificat d'un
> autre domaine), puis basculer les URL en `https://`.

## Comptes de démonstration

Mot de passe commun : `gfc2026` (à changer en production via **Mon compte**).

| Rôle | Identifiant (téléphone) |
|---|---|
| Super Admin | `+237690000001` |
| Délégué d'équipe | `+237677000002` |
| Arbitre | `+237696000003` |

## Hébergement

- **Hôte** : Camoo — `ftp-12.camoo.net`, répertoire `/home/trugro9159/gfc`.
- **Base de données** (MySQL 8) : base `trugro9159_gfc`, hôte `localhost`, port `3306`.
- **phpMyAdmin** : https://pma-12.camoo.net

Les identifiants (mot de passe BD, FTP, `app_key`) **ne sont pas versionnés** :
ils vivent dans `config/config.local.php` créé **sur le serveur** (voir
`config/config.php` qui le fusionne). Le dépôt ne contient que les valeurs de
développement par défaut.

## Contenu de la version

**Site public** — accueil, calendrier, résultats, classement calculé, équipes &
effectifs, meilleurs buteurs, galerie, actualités, sponsors, palmarès.

**API JSON** (`/api`) — `edition, competitions, teams, teams/{id}, standings,
matches, matches/{id}, players/top-scorers, news, media, palmares, sponsors`
et écriture de la feuille de match (staff).

**Back-office** (`/admin`) — tableau de bord, **saisie live** (feuille de match
en temps réel), **Mon compte**, et **CRUD complet** (créer / modifier /
supprimer + upload d'images) sur :
Compétitions & phases, Calendrier (avec désignation d'arbitre), Équipes,
Joueurs, Utilisateurs & rôles, Sanctions, Actualités & médias, Sponsors.

## Notes techniques

- Stack : PHP 8.1 (PDO/MySQL), sans framework ; front controller `public/index.php`.
- Écritures CRUD via `POST` + champ `_action` (l'hébergement bloque `PUT`/`DELETE`).
- Uploads d'images dans `public/uploads/`, servis via `/uploads/…`.
- Aucune icône emoji dans l'interface (charte GFC).
- DocumentRoot = racine du projet : `.htaccess` racine redirige vers `public/`
  et protège `config/`, `src/`, `database/`, `templates/`, `storage/`.

## Base de code

- Dépôt : https://github.com/efoka24-ops/gfc2026
- Branche : `webapp_gfc`
- Données de démonstration de la 6e édition : `database/seed.sql`
  (10 équipes, 37 joueurs, matchs, buteurs — à remplacer par les données réelles).

## Reste à faire

- Certificat SSL (AutoSSL) pour `gfc.trugroup.cm`.
- Saisie des données réelles de la 6e édition via le back-office.
- Régénérer les mots de passe des comptes de démonstration.

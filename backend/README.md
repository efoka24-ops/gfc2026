# Backend & back-office — Garoua Football Challenge

PHP 8.1+ / MySQL 8. Deux parties dans le même dossier `public/` :
l'API REST consommée par l'app mobile et le back-office `public/admin/`.

## Installation

```bash
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/seed.sql          # données d'exemple, à remplacer
cd backend && php -S localhost:8000 -t public
```

Variables d'environnement en développement : `GFC_DB_HOST`, `GFC_DB_NAME`,
`GFC_DB_USER`, `GFC_DB_PASS`, `GFC_BASE_URL`.

Compte de démonstration : `admin@gfc.cm` / `gfc2026`.
**Régénérez le hash en production** :
`php -r "echo password_hash('votre-mot-de-passe', PASSWORD_DEFAULT);"`

## Production — gfc.trugroup.cm

L'installation de production vit sur `/home/trugro9159/gfc` et répond sur
<https://gfc.trugroup.cm> : API sur `/api`, back-office sur `/admin/login.php`.
C'est cette installation que consomme l'application mobile.

Le compte FTP y étant confiné au dossier qui sert aussi de racine web, `public/`
ne peut pas être la racine : l'arborescence `backend/` est déployée telle quelle
et le `.htaccess` racine redirige tout le trafic vers `public/`, tandis que
`config/`, `src/` et `sql/` sont refusés par HTTP via leur propre `.htaccess`.

Les identifiants de production se placent dans `config/config.local.php` (copie
de `config/config.local.example.php`), créé **sur le serveur** et ignoré par git.
Ce fichier prime sur les variables d'environnement, que les hébergements
mutualisés n'exposent pas de manière fiable au processus PHP.

Procédure complète, script de transfert et vérifications d'après-déploiement :
[`deploy/DEPLOIEMENT.md`](../deploy/DEPLOIEMENT.md).

Sur un hébergement où `public/` peut être la racine web, servez-le directement
(Apache : le `.htaccess` de `public/` suffit ; Nginx :
`try_files $uri /index.php?$query_string;`) et le `.htaccess` racine devient
inutile.

## API — lecture publique

| Méthode | Route | Description |
|---|---|---|
| GET | `/api/competitions` | les compétitions de la saison |
| GET | `/api/matches?scope=upcoming\|results&competition=slug` | calendrier / résultats |
| GET | `/api/matches/{id}` | fiche match : événements, compos, statistiques |
| GET | `/api/standings?competition=championnat` | classement (vue SQL `v_standings`) |
| GET | `/api/teams`, `/api/teams/{id}` | équipes, effectif, position |
| GET | `/api/players/{id}` | fiche joueur et statistiques |
| GET | `/api/stats/players?metric=goals\|assists\|cards` | buteurs, passeurs, discipline |
| GET | `/api/stats/teams` | attaque, défense, possession, affluence |
| GET | `/api/news`, `/api/media?type=photo\|video` | actualités, photos et vidéos |
| POST | `/api/devices` | enregistrement d'un appareil (notifications) |

## API — écriture (token requis)

`POST /api/auth/login` renvoie un token ; puis en-tête `Authorization: Bearer <token>`.

| Méthode | Route |
|---|---|
| POST | `/api/matches/{id}/events` — ajoute un événement, recalcule le score |
| PATCH | `/api/matches/{id}` — statut, minute, affluence, arbitre, stade |

## Back-office `/admin`

Connexion par session PHP + jeton CSRF sur chaque formulaire.

- **Tableau de bord** — indicateurs, prochains matchs, classement en direct
- **Saisie live** — sélection du match, boutons d'événement (but, penalty, cartons, changement…),
  minute, équipe, joueur, passeur ; le score et le classement se recalculent automatiquement
- **Compétitions** — création, engagement des équipes
- **Équipes / Joueurs** — fiches, effectifs, licences
- **Matchs & calendrier** — programmation
- **Actualités** — publication immédiate ou brouillon
- **Photos & vidéos** — upload (jpg, png, webp, mp4 ≤ 25 Mo) ou URL externe
- **Utilisateurs & rôles** — admin (accès complet), secrétaire (contenus), arbitre (saisie live seule)

## Modèle de données

13 tables + la vue `v_standings` qui calcule points, différence de buts et classement
à partir des seuls matchs terminés — aucun classement stocké, donc jamais désynchronisé.
Les statistiques joueurs sont dérivées de `match_events`, ce qui rend chaque chiffre traçable
jusqu'à l'événement saisi.

## Sécurité

- requêtes préparées PDO partout, `ATTR_EMULATE_PREPARES => false`
- `password_hash` / `password_verify`, tokens API à durée de vie limitée
- CSRF sur les formulaires du back-office, échappement systématique en sortie (`e()`)
- contrôle des extensions et de la taille des fichiers à l'upload

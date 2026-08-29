# Déploiement de la partie web — gfc.trugroup.cm

Cible : le sous-domaine **gfc.trugroup.cm**, servi depuis
`/home/trugro9159/gfc` sur l'hébergement Camoo (`ftp-12.camoo.net`).
C'est cette installation que consomme l'application mobile.

## Contrainte d'hébergement et parade retenue

L'installation prévue par le plan sert `backend/public/` comme racine web, avec
`src/` et `config/` **au-dessus** d'elle, hors d'atteinte du navigateur. Ici, le
compte FTP est confiné à `/home/trugro9159/gfc`, qui est aussi la racine web du
sous-domaine : il n'existe aucun dossier au-dessus où déposer le code sensible.

La parade — la disposition classique sur cPanel — est de déployer
l'arborescence `backend/` telle quelle et de rediriger tout le trafic vers
`public/` :

```text
/home/trugro9159/gfc/          <- racine web de gfc.trugroup.cm
├── .htaccess                  <- force HTTPS et redirige tout vers public/
├── config/
│   ├── .htaccess              <- Require all denied
│   ├── config.php
│   └── config.local.php       <- créé sur le serveur, jamais commité
├── src/
│   └── .htaccess              <- Require all denied
├── sql/
│   └── .htaccess              <- Require all denied
└── public/
    ├── .htaccess              <- réécriture vers index.php
    ├── index.php              <- API REST
    ├── admin/                 <- back-office
    └── uploads/               <- médias envoyés
```

Les chemins relatifs du code (`__DIR__ . '/../src/'`) restent valides : rien à
modifier entre développement et production.

Après déploiement :

- API : `https://gfc.trugroup.cm/api/competitions`
- Back-office : `https://gfc.trugroup.cm/admin/login.php`

Deux protections se superposent — les `.htaccess` `Require all denied` sur
`config/`, `src/` et `sql/`, et la redirection systématique vers `public/`. Si
l'hébergeur venait à ignorer `AllowOverride`, la première barrière tomberait :
**vérifier après le premier déploiement** que `https://gfc.trugroup.cm/config/config.local.php`
et `https://gfc.trugroup.cm/src/Database.php` renvoient bien 403 ou 404, et
jamais du contenu.

## 1. Base de données

Dans cPanel → **Bases de données MySQL** : créer la base et l'utilisateur, puis
associer l'utilisateur à la base avec tous les privilèges. Sur cet hébergement,
les noms sont préfixés par le compte, soit `trugro9159_gfc`.

Importer ensuite le schéma via phpMyAdmin :

```text
backend/sql/schema.sql      -> obligatoire
backend/sql/seed.sql        -> NE PAS importer en production
```

`seed.sql` ne contient que des données de démonstration. Les données réelles de
la 6e édition se saisissent depuis le back-office (tâche T085).

## 2. Configuration

Sur le serveur uniquement, jamais dans le dépôt :

```bash
cp config/config.local.example.php config/config.local.php
# puis renseigner db_name, db_user, db_pass et base_url
```

`config.local.php` est ignoré par git et refusé par HTTP.

## 3. Envoi des fichiers

Le script `deploy/deploy-ftp.sh` synchronise `backend/` vers le serveur. Il ne
contient aucun identifiant : il les lit dans l'environnement.

```bash
cp deploy/.env.deploy.example deploy/.env.deploy   # ignoré par git
# renseigner GFC_FTP_PASS, puis :
set -a && . deploy/.env.deploy && set +a
bash deploy/deploy-ftp.sh --dry-run    # liste ce qui serait transféré
bash deploy/deploy-ftp.sh              # transfère
```

Le script préserve `config/config.local.php` et le contenu de `public/uploads/`
côté serveur, et n'envoie jamais `sql/seed.sql`.

Sans `lftp` installé, le transfert peut se faire à la main avec FileZilla : y
recopier l'arborescence ci-dessus, en excluant `sql/seed.sql` et sans écraser
`config.local.php` ni `uploads/`.

## 4. Vérifications après déploiement

```bash
curl -sS https://gfc.trugroup.cm/api/competitions            # 200 et du JSON
curl -sS -o /dev/null -w '%{http_code}\n' https://gfc.trugroup.cm/config/config.local.php   # 403 ou 404
curl -sS -o /dev/null -w '%{http_code}\n' https://gfc.trugroup.cm/src/Database.php          # 403 ou 404
curl -sS -o /dev/null -w '%{http_code}\n' http://gfc.trugroup.cm/api/competitions           # 301 vers https
```

Puis dans un navigateur : `https://gfc.trugroup.cm/admin/login.php`.

**Avant la mise en service**, régénérer le mot de passe du compte
administrateur — le compte de démonstration `admin@gfc.cm` / `gfc2026` ne doit
pas survivre au déploiement :

```bash
php -r "echo password_hash('votre-mot-de-passe', PASSWORD_DEFAULT), PHP_EOL;"
```

et reporter le condensat obtenu dans la table `users`.

## 5. Côté mobile

`mobile/app.json` → `expo.extra.apiUrl` pointe sur `https://gfc.trugroup.cm/api`.
En développement local, le remplacer par `http://10.0.2.2:8000/api` (émulateur
Android) sans commiter la modification.

## Sécurité des accès

Les identifiants FTP et de base de données ne figurent dans aucun fichier
versionné. Ils vivent dans `deploy/.env.deploy` et `config/config.local.php`,
tous deux ignorés par git.

Le mot de passe FTP fourni à la mise en place a transité en clair par un canal
de discussion : il doit être régénéré depuis cPanel avant l'ouverture au public.
Si un identifiant devait malgré tout apparaître dans un commit, le régénérer est
la seule réponse valable — retirer le fichier ne suffit pas, l'historique le
conserve.

Note : le mot de passe contient le caractère `%`, que la plupart des clients FTP
interprètent dans une URL. Le renseigner comme variable
(`GFC_FTP_PASS='...'`, en guillemets simples) et non dans une URL de la forme
`ftp://user:pass@hôte` — sinon l'encoder en `%25`.

## Certificat SSL — à régler avant tout

Vérification faite le 2026-08-29 : `gfc.trugroup.cm` résout bien
(185.215.180.170) et le serveur répond, mais **le certificat présenté ne couvre
pas ce sous-domaine** :

```text
$ curl https://gfc.trugroup.cm/
SEC_E_WRONG_PRINCIPAL — le nom de la cible n'est pas correct
$ curl -k https://gfc.trugroup.cm/     # en ignorant le certificat
200
```

C'est bloquant, et pas seulement cosmétique :

- le `.htaccess` racine force HTTPS ; sans certificat valide, toute requête
  échoue au lieu d'être servie ;
- Android refuse par défaut les connexions HTTP en clair comme les certificats
  invalides : l'application mobile ne pourra pas joindre l'API ;
- les jetons d'écriture de la saisie live transiteraient en clair.

**À faire dans cPanel** : émettre un certificat pour `gfc.trugroup.cm` via
AutoSSL ou Let's Encrypt (section « SSL/TLS » ou « SSL/TLS Status »), puis
revérifier :

```bash
curl -sS -o /dev/null -w '%{http_code}\n' https://gfc.trugroup.cm/
```

Tant que cette commande n'aboutit pas sans `-k`, ne pas publier l'application
mobile.

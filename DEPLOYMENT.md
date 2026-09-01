# 📋 GFC 2026 - Guide de Déploiement FTP

## ✅ Corrections Effectuées

### 1. Logo Backoffice
- ✅ Logo GFC ajouté (remplace le logo orange)
- ✅ Fichier : `web/public/logo.png`
- ✅ Interface mise à jour

### 2. Base de Données
- ✅ Admin user vérifié : `admin@gfc.local` / `GFC@admin2026!`
- ✅ 10 équipes créées
- ✅ 3 compétitions configurées

## 🚀 Déploiement Manuel FTP

### Informations Serveur
- **Serveur** : ftp-12.camoo.net
- **Port** : 21
- **Utilisateur** : trugro9159_gfc
- **Mot de passe** : (à saisir lors de l'exécution)
- **Répertoire** : /public_html

### Étapes de Déploiement

#### 1. Build Laravel API
```powershell
cd C:\Projects\gfc\api
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2. Build Dashboard Web
```powershell
cd C:\Projects\gfc\web
npm run build
# Résultat dans : web/dist/
```

#### 3. Upload FTP
Exécutez le script de déploiement :
```powershell
C:\Projects\gfc\deploy-ftp.ps1
```

### Structure Serveur Camoo
```
/public_html/
├── api/              # Laravel backend
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/       # Point d'entrée index.php
│   ├── routes/
│   ├── vendor/
│   └── .env          # Configuration production
│
├── dashboard/        # React web (build)
│   ├── assets/
│   ├── index.html
│   └── ...
│
└── .htaccess         # Redirections Apache
```

### Configuration .env Production
```env
APP_NAME="GFC 2026"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gfc.camoo.hosting

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=trugro9159_gfc
DB_USERNAME=trugro9159_gfc
DB_PASSWORD=fxTach7b#C?s

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
```

### Déploiement via FileZilla (Alternative)
1. Ouvrir FileZilla
2. Hôte : `ftp-12.camoo.net`
3. Utilisateur : `trugro9159_gfc`
4. Mot de passe : (demander au client)
5. Port : 21

**Upload Laravel** :
- Local : `C:\Projects\gfc\api\`
- Distant : `/public_html/api/`
- Exclure : `node_modules`, `vendor` (réinstaller sur serveur)

**Upload Web** :
- Local : `C:\Projects\gfc\web\dist\`
- Distant : `/public_html/dashboard/`

### Commandes Post-Déploiement
```bash
# SSH vers serveur (si disponible)
ssh trugro9159_gfc@ftp-12.camoo.net

# Installation dépendances
cd /public_html/api
composer install --no-dev

# Migrations
php artisan migrate --force

# Seeder (première fois uniquement)
php artisan db:seed --force
```

### URLs Finales
- **API** : https://gfc.camoo.hosting/api
- **Dashboard** : https://gfc.camoo.hosting/dashboard
- **Site Public** : https://gfc.trugroup.cm

### Vérification
```bash
# Test API
curl https://gfc.camoo.hosting/api/teams

# Test Auth
curl -X POST https://gfc.camoo.hosting/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@gfc.local","password":"GFC@admin2026!","device_name":"web"}'
```

## 🐛 Dépannage

### Erreur 404 Not Found
- Vérifier `.htaccess` dans `/public_html`
- S'assurer que `mod_rewrite` est activé

### Erreur 500 Internal Server Error
- Vérifier permissions : `chmod 755` sur dossiers, `644` sur fichiers
- Vérifier logs : `/public_html/api/storage/logs/laravel.log`

### Base de données connexion refusée
- Vérifier `.env` : DB_HOST doit être `localhost`
- Vérifier credentials MySQL avec phpMyAdmin

### CORS Errors
- Ajouter dans `api/config/cors.php` :
```php
'allowed_origins' => ['https://gfc.camoo.hosting', 'https://gfc.trugroup.cm'],
```

## 📝 Notes
- Le script `deploy-ftp.ps1` est un point de départ
- Pour production complète, utiliser un CI/CD (GitHub Actions déjà configuré)
- Sauvegarder la BD avant chaque déploiement : `php artisan db:backup`

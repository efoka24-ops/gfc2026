# 📦 Guide de Déploiement Rapide - GFC 2026

## ✅ Fichiers Prêts pour le Déploiement

### 🎯 Backend API Laravel
- **Local**: `C:\Projects\gfc\api`
- **Destination FTP**: `/public_html/` (racine)

### 🎯 Frontend Dashboard  
- **Local**: `C:\Projects\gfc\web\dist` (DÉJÀ BUILDÉ ✅)
- **Destination FTP**: `/public_html/dashboard/`

---

## 🚀 Option 1: Déploiement Automatique PowerShell

```powershell
# Lancer le script de déploiement
C:\Projects\gfc\deploy-to-camoo.ps1
```

**Le script va**:
1. Créer un package de tous les fichiers nécessaires
2. Uploader automatiquement vers ftp-12.camoo.net
3. Afficher les commandes post-déploiement à exécuter

**Durée estimée**: 5-15 minutes (selon la connexion)

---

## 🚀 Option 2: Déploiement Manuel FileZilla (RECOMMANDÉ)

### Étape 1: Installer FileZilla Client
- Télécharger: https://filezilla-project.org/download.php?type=client
- Installer et lancer

### Étape 2: Connexion FTP
```
Hôte:       ftp-12.camoo.net
Nom d'utilisateur: trugro9159_gfc  
Mot de passe:      [VOTRE MOT DE PASSE]
Port:       21
```

### Étape 3: Upload des Fichiers

#### A. Backend Laravel API
**Local (gauche)**: `C:\Projects\gfc\api`  
**Distant (droite)**: `/public_html/`

**Fichiers à uploader**:
- `app/` → `/public_html/app/`
- `bootstrap/` → `/public_html/bootstrap/`
- `config/` → `/public_html/config/`
- `database/` → `/public_html/database/`
- `public/` → `/public_html/public/`
- `resources/` → `/public_html/resources/`
- `routes/` → `/public_html/routes/`
- `storage/` → `/public_html/storage/`
- `vendor/` → `/public_html/vendor/`
- `artisan` → `/public_html/artisan`
- `composer.json` → `/public_html/composer.json`
- `composer.lock` → `/public_html/composer.lock`
- `.env.production` → `/public_html/.env` (⚠️ RENOMMER en .env)

#### B. Frontend Dashboard Web
**Local (gauche)**: `C:\Projects\gfc\web\dist`  
**Distant (droite)**: `/public_html/dashboard/`

**Upload tout le contenu** de `web/dist/*` vers `/public_html/dashboard/`

### Étape 4: Post-Déploiement (SSH)

```bash
# 1. Connexion SSH au serveur
ssh trugro9159_gfc@ftp-12.camoo.net

# 2. Aller dans le dossier
cd /home/trugro9159_gfc/public_html

# 3. Générer la clé d'application Laravel
php artisan key:generate

# 4. Lancer les migrations
php artisan migrate --force

# 5. Lancer les seeders (créer admin + équipes + compétitions)
php artisan db:seed --force

# 6. Configurer les permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🔗 URLs de Production

Une fois déployé:

- **API Backend**: https://gfc.camoo.hosting/api
- **Dashboard Admin**: https://gfc.camoo.hosting/dashboard
- **Test API**: https://gfc.camoo.hosting/api/teams

### Tester l'API
```bash
curl https://gfc.camoo.hosting/api/teams
```

### Login Admin (Dashboard)
```
Email:    admin@gfc.local
Mot de passe: GFC@admin2026!
```

---

## 📱 Configuration Mobile App

Après déploiement, mettre à jour `mobile/app.json`:

```json
{
  "extra": {
    "apiUrl": "https://gfc.camoo.hosting/api"
  }
}
```

---

## 🐛 Dépannage

### Erreur 500 après déploiement
```bash
# Vérifier les logs Laravel
tail -f /home/trugro9159_gfc/public_html/storage/logs/laravel.log

# Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Base de données vide
```bash
# Re-lancer les seeders
php artisan db:seed --class=GfcSeeder --force
php artisan db:seed --class=CompetitionSeeder --force
```

### Permissions incorrectes
```bash
chmod -R 755 /home/trugro9159_gfc/public_html
chmod -R 775 /home/trugro9159_gfc/public_html/storage
chmod -R 775 /home/trugro9159_gfc/public_html/bootstrap/cache
```

---

## 🎯 Checklist de Déploiement

- [ ] Build frontend terminé (`npm run build`)
- [ ] Fichier `.env.production` préparé
- [ ] Upload API Laravel via FTP
- [ ] Upload Dashboard web via FTP
- [ ] Connexion SSH au serveur
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --force`
- [ ] Test de l'URL: https://gfc.camoo.hosting/api/teams
- [ ] Login dashboard: https://gfc.camoo.hosting/dashboard
- [ ] Mise à jour `mobile/app.json` avec URL production

---

## ⏱️ Temps Estimé

- **Préparation**: 5 min
- **Upload FTP (FileZilla)**: 10-20 min
- **Configuration serveur**: 5 min
- **TOTAL**: ~20-30 minutes

---

## 💡 Astuces

1. **Upload en arrière-plan**: FileZilla permet de continuer à travailler pendant l'upload
2. **Compression**: Compresser `vendor/` en ZIP avant upload puis décompresser sur le serveur (plus rapide)
3. **Synchronisation**: FileZilla peut synchroniser automatiquement les fichiers modifiés
4. **Vérification**: Toujours tester l'API avec `/api/teams` avant de tester le dashboard

---

**Besoin d'aide ?** Consultez le fichier [DEPLOYMENT.md](DEPLOYMENT.md) pour plus de détails.

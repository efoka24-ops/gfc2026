# 🚀 Guide de Déploiement GFC 2026 - CAMOO avec CSHIELD

## ⚠️ Environnement CAMOO CSHIELD

CAMOO CSHIELD est un **shell sécurisé restreint** qui ne permet pas :
- ❌ Commandes SSH
- ❌ `php artisan` direct
- ❌ Certaines commandes système standard

## ✅ Solution Alternative : Déploiement via cPanel + Script Web

### 📦 Étape 1 : Préparer le Package

Le package ZIP est déjà créé : `C:\Projects\gfc\gfc-deploy.zip`

### 🌐 Étape 2 : Upload via cPanel File Manager

1. **Connectez-vous à cPanel CAMOO**
   - URL : Généralement https://cpanel.camoo.hosting ou le lien fourni par Camoo
   - User : `trugro9159_gfc`
   - Password : Votre mot de passe cPanel (peut différer du FTP)

2. **Accédez au File Manager**
   - Cliquez sur **File Manager** dans cPanel
   - Naviguez vers `/home/trugro9159/public_html/`

3. **Créez le dossier GFC**
   - Clic droit → **New Folder** → Nom : `gfc`
   - Entrez dans le dossier `gfc`

4. **Uploadez le ZIP**
   - Cliquez sur **Upload**
   - Sélectionnez `C:\Projects\gfc\gfc-deploy.zip`
   - Attendez la fin de l'upload (barre de progression 100%)

5. **Extrayez le ZIP**
   - Retournez dans File Manager
   - Clic droit sur `gfc-deploy.zip` → **Extract**
   - Confirmez l'extraction
   - Une fois terminé, supprimez le fichier ZIP

6. **Uploadez le script d'installation**
   - Uploadez le fichier `C:\Projects\gfc\setup-web.php` dans `/home/trugro9159/public_html/gfc/`

### 🔧 Étape 3 : Exécuter l'Installation Web

1. **Accédez au script d'installation**
   - Ouvrez votre navigateur
   - Allez à : `https://votre-domaine.camoo.hosting/gfc/setup-web.php`
   - Ou : `https://gfc.camoo.hosting/setup-web.php` (selon votre configuration)

2. **Entrez le mot de passe de setup**
   - Mot de passe : `GFC@setup2026!`

3. **Suivez l'installation automatique**
   - Le script va :
     - ✅ Installer les dépendances Composer
     - ✅ Générer la clé APP_KEY Laravel
     - ✅ Exécuter les migrations (créer les tables)
     - ✅ Charger les données initiales (admin + équipes)
     - ✅ Configurer les permissions
     - ✅ Optimiser le cache

4. **Vérifiez les URLs**
   - Le script affichera les URLs de votre application
   - Testez l'API : `https://votre-domaine.com/gfc/api/teams`
   - Accédez au dashboard : `https://votre-domaine.com/gfc/dashboard`

5. **SÉCURITÉ : Supprimez setup-web.php**
   - Retournez dans File Manager
   - Supprimez le fichier `setup-web.php` immédiatement après l'installation

### 🎯 Étape 4 : Configuration du .htaccess (Si nécessaire)

Si l'application Laravel ne fonctionne pas directement, créez un fichier `.htaccess` dans `/home/trugro9159/public_html/gfc/public/` :

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 📱 Étape 5 : Configuration de l'App Mobile

Mettez à jour `mobile/app.json` avec la vraie URL de production :

```json
{
  "extra": {
    "apiUrl": "https://votre-domaine.camoo.hosting/gfc/api"
  }
}
```

Puis rebuilder l'app :
```bash
cd C:\Projects\gfc\mobile
npm run build
```

---

## 🔍 Vérification de l'Installation

### Test 1 : API Teams
```
https://votre-domaine.com/gfc/api/teams
```
Devrait retourner un JSON avec la liste des 10 équipes.

### Test 2 : Dashboard Login
```
https://votre-domaine.com/gfc/dashboard
```
Login :
- Email : `admin@gfc.local`
- Password : `GFC@admin2026!`

### Test 3 : Base de données
Vérifiez dans phpMyAdmin (cPanel) que la base `trugro9159_gfc` contient :
- ✅ 12 tables (migrations, users, teams, players, matches, etc.)
- ✅ 1 user admin
- ✅ 10 teams
- ✅ 3 competitions

---

## 🛠️ Dépannage

### Problème : "500 Internal Server Error"
**Solution** :
1. Vérifiez les permissions : `storage/` et `bootstrap/cache/` doivent être en 775
2. Vérifiez que `.env` existe et contient `APP_KEY=...`
3. Consultez les logs : `/home/trugro9159/public_html/gfc/storage/logs/laravel.log`

### Problème : "composer: command not found"
**Solution** :
- Composer doit être installé sur le serveur Camoo
- Contactez le support Camoo pour vérifier l'installation de Composer
- Alternative : Uploadez le dossier `vendor/` complet (8365 fichiers) via FileZilla

### Problème : "Database connection refused"
**Solution** :
1. Vérifiez le fichier `.env` :
   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=trugro9159_gfc
   DB_USERNAME=trugro9159_gfc
   DB_PASSWORD=fxTach7b#C?s
   ```
2. Assurez-vous que la base de données MySQL est créée dans cPanel → MySQL Databases

---

## 📋 Checklist Complète

- [ ] Package ZIP uploadé et extrait
- [ ] Script setup-web.php uploadé
- [ ] Installation exécutée via navigateur
- [ ] setup-web.php supprimé (SÉCURITÉ)
- [ ] Test API : /api/teams fonctionne
- [ ] Test Dashboard : login réussi
- [ ] Configuration mobile mise à jour
- [ ] App mobile rebuildée

---

## 🌐 URLs Finales

**Production** :
- API : `https://gfc.camoo.hosting/api`
- Dashboard : `https://gfc.camoo.hosting/dashboard`

**Login Admin** :
- Email : `admin@gfc.local`
- Password : `GFC@admin2026!`

---

## 📞 Support

Si des problèmes persistent avec CAMOO CSHIELD :
- Contactez le support technique CAMOO
- Demandez l'accès SSH standard (non-CSHIELD)
- Ou utilisez le script web fourni qui contourne les limitations du shell

---

**Créé le 2026-09-01 pour GFC 2026 MVP**

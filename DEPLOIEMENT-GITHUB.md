# 🚀 Déploiement Automatique GFC 2026 via GitHub Actions

## 📋 Vue d'Ensemble

Ce système permet un déploiement **automatique** de GFC 2026 depuis GitHub vers le serveur CAMOO à chaque push sur la branche `main`.

### ✨ Fonctionnalités

- ✅ Déploiement automatique sur push vers `main`
- ✅ Installation automatique des dépendances (Composer + npm)
- ✅ Build automatique du dashboard React
- ✅ Upload FTP optimisé vers CAMOO
- ✅ Exécution des migrations via webhook
- ✅ Optimisation du cache Laravel
- ✅ Déploiement manuel disponible (workflow_dispatch)

---

## 🔧 Configuration Initiale

### Étape 1 : Configurer les Secrets GitHub

1. **Allez sur GitHub** : https://github.com/efoka24-ops/gfc2026

2. **Accédez aux Settings** :
   - Cliquez sur **Settings** (onglet en haut)
   - Dans le menu latéral gauche : **Secrets and variables** → **Actions**

3. **Ajoutez les secrets suivants** (bouton **New repository secret**) :

| Nom du Secret | Valeur | Description |
|---------------|--------|-------------|
| `FTP_USERNAME` | `trugro9159_gfc` | Nom d'utilisateur FTP |
| `FTP_PASSWORD` | `km5ZdqbD%Mx6` | Mot de passe FTP |
| `DEPLOY_WEBHOOK_SECRET` | `GFC@webhook2026!SECRET` | Token sécurisé pour webhook (changez-le!) |

**⚠️ IMPORTANT** : Pour `DEPLOY_WEBHOOK_SECRET`, générez un token sécurisé unique :
```powershell
# Sur Windows PowerShell
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 32 | % {[char]$_})
```

### Étape 2 : Upload du Webhook sur le Serveur

1. **Via cPanel File Manager** :
   - Connectez-vous à cPanel
   - Naviguez vers `/home/trugro9159/public_html/gfc/`
   - Uploadez le fichier `C:\Projects\gfc\deploy-webhook.php`

2. **Configurez le secret du webhook** :
   - Éditez `deploy-webhook.php` sur le serveur
   - Changez la ligne :
     ```php
     $WEBHOOK_SECRET = getenv('DEPLOY_WEBHOOK_SECRET') ?: 'GFC@webhook2026!SECRET';
     ```
   - Remplacez `GFC@webhook2026!SECRET` par le même token que dans les secrets GitHub

3. **Testez le webhook** :
   ```powershell
   curl -X POST https://gfc.camoo.hosting/gfc/deploy-webhook.php `
     -H "Authorization: Bearer VotreSecretToken" `
     -d "action=cache"
   ```

### Étape 3 : Pousser le Code

```powershell
cd C:\Projects\gfc

# Ajouter les fichiers de workflow
git add .github/workflows/deploy-production.yml
git add deploy-webhook.php
git add DEPLOIEMENT-GITHUB.md

# Commit
git commit -m "feat: Add GitHub Actions auto-deployment workflow"

# Push vers GitHub (déclenche le déploiement)
git push origin main
```

---

## 🎯 Utilisation

### Déploiement Automatique

**Chaque fois que vous faites un `git push origin main`** :

1. 🔄 GitHub Actions se déclenche automatiquement
2. 📦 Installe les dépendances PHP et JavaScript
3. 🏗️ Build le dashboard React
4. 📤 Upload les fichiers vers CAMOO via FTP
5. 🔧 Exécute les migrations et optimise le cache
6. ✅ Votre application est déployée !

### Déploiement Manuel

1. Allez sur GitHub : https://github.com/efoka24-ops/gfc2026/actions
2. Cliquez sur **Deploy GFC 2026 to Production**
3. Cliquez sur **Run workflow** → **Run workflow**
4. Attendez quelques minutes (progression en temps réel)

---

## 📊 Workflow GitHub Actions

Le workflow `.github/workflows/deploy-production.yml` effectue :

```yaml
1. Checkout du code
2. Setup PHP 8.1 + Composer
3. Installation dépendances Laravel (composer install)
4. Setup Node.js 20
5. Installation dépendances React (npm ci)
6. Build production du dashboard (npm run build)
7. Préparation du package de déploiement
8. Upload FTP vers /home/trugro9159/public_html/gfc/
9. Appel du webhook pour migrations et cache
```

---

## 🔗 Webhook de Déploiement

Le fichier `deploy-webhook.php` expose une API sécurisée pour exécuter des commandes post-déploiement.

### Actions Disponibles

| Action | Description | Commande |
|--------|-------------|----------|
| `full` | Déploiement complet (migrations + seed + cache) | `php artisan migrate`, `db:seed`, cache |
| `migrate` | Migrations uniquement | `php artisan migrate --force` |
| `seed` | Seeders uniquement | `php artisan db:seed --force` |
| `cache` | Optimisation du cache | `config:cache`, `route:cache`, `view:cache` |
| `clear` | Vider les caches | `cache:clear`, `config:clear`, etc. |

### Utilisation Manuelle

```powershell
# Migration seule
curl -X POST https://gfc.camoo.hosting/gfc/deploy-webhook.php `
  -H "Authorization: Bearer VotreSecretToken" `
  -d "action=migrate"

# Cache complet
curl -X POST https://gfc.camoo.hosting/gfc/deploy-webhook.php `
  -H "Authorization: Bearer VotreSecretToken" `
  -d "action=cache"

# Déploiement full
curl -X POST https://gfc.camoo.hosting/gfc/deploy-webhook.php `
  -H "Authorization: Bearer VotreSecretToken" `
  -d "action=full"
```

---

## 🔄 Workflow de Développement

### Développement Local

```powershell
# 1. Faire des modifications
cd C:\Projects\gfc
# ... éditer les fichiers ...

# 2. Tester localement
cd api
php artisan serve

cd ..\web
npm run dev

# 3. Commit et push
git add .
git commit -m "feat: Nouvelle fonctionnalité"
git push origin main

# 4. GitHub Actions déploie automatiquement !
```

### Vérifier le Déploiement

1. **GitHub Actions** :
   - https://github.com/efoka24-ops/gfc2026/actions
   - Voir les logs en temps réel
   - Vérifier que toutes les étapes sont ✅

2. **Test Production** :
   - API : https://gfc.camoo.hosting/api/teams
   - Dashboard : https://gfc.camoo.hosting/dashboard

---

## 🛡️ Sécurité

### Bonnes Pratiques

1. **Secrets GitHub** :
   - ✅ Ne JAMAIS commiter les secrets dans le code
   - ✅ Utiliser uniquement les GitHub Secrets
   - ✅ Changer régulièrement les tokens

2. **Webhook** :
   - ✅ Token d'autorisation obligatoire
   - ✅ Validation des actions
   - ✅ Logs des exécutions

3. **FTP** :
   - ✅ Credentials stockés de manière sécurisée
   - ✅ Connexion chiffrée si disponible

### Restrictions CSHIELD

Si le webhook ne fonctionne pas (CSHIELD trop restrictif) :

1. **Option A** : Migrations manuelles après déploiement
   ```powershell
   curl https://gfc.camoo.hosting/gfc/setup-web.php?password=GFC@setup2026!
   ```

2. **Option B** : Déploiement avec vendor/ inclus
   - Modifier `.github/workflows/deploy-production.yml`
   - Ligne 38 : Retirer `--exclude='vendor'`
   - Les dépendances seront uploadées (plus lent mais fonctionne toujours)

---

## 📁 Structure du Déploiement

```
/home/trugro9159/public_html/gfc/
├── app/                    # Code Laravel
├── bootstrap/
├── config/
├── database/
│   ├── migrations/        # Migrations automatiques
│   └── seeders/           # Seeders (admin + équipes)
├── public/
│   ├── index.php         # Point d'entrée API
│   └── dashboard/        # Dashboard React (buildé)
├── resources/
├── routes/
├── storage/              # Logs et cache
├── vendor/               # Dépendances Composer
├── .env                  # Configuration production
├── artisan
├── composer.json
└── deploy-webhook.php    # Webhook de déploiement
```

---

## 🐛 Dépannage

### Erreur : FTP Upload Failed

**Cause** : Credentials FTP incorrects ou serveur inaccessible

**Solution** :
1. Vérifier les secrets GitHub (`FTP_USERNAME`, `FTP_PASSWORD`)
2. Tester la connexion FTP manuellement avec FileZilla
3. Vérifier que le dossier `/home/trugro9159/public_html/gfc/` existe

### Erreur : Webhook 401 Unauthorized

**Cause** : Token webhook incorrect

**Solution** :
1. Vérifier que `DEPLOY_WEBHOOK_SECRET` dans GitHub Secrets est identique à celui dans `deploy-webhook.php`
2. Régénérer un nouveau token si nécessaire

### Erreur : Migrations Failed

**Cause** : Base de données inaccessible ou déjà migrée

**Solution** :
1. Vérifier les credentials MySQL dans `.env` sur le serveur
2. Vérifier que la base `trugro9159_gfc` existe dans cPanel
3. Consulter les logs : `storage/logs/laravel.log`

---

## 📊 Monitoring

### Logs GitHub Actions

- URL : https://github.com/efoka24-ops/gfc2026/actions
- Historique complet des déploiements
- Logs détaillés de chaque étape

### Logs Laravel

- Fichier : `/home/trugro9159/public_html/gfc/storage/logs/laravel.log`
- Accessible via cPanel File Manager

### Webhook Response

Le webhook retourne un JSON avec le détail de chaque action :

```json
{
  "status": "success",
  "timestamp": "2026-09-01 15:30:45",
  "actions": [
    {
      "description": "Database migration",
      "command": "php artisan migrate --force",
      "output": "Migration table created successfully.\nMigrating: 2024_01_01_000000_create_users_table...",
      "success": true,
      "return_code": 0
    }
  ]
}
```

---

## ✅ Checklist de Configuration

- [ ] Secrets GitHub configurés (`FTP_USERNAME`, `FTP_PASSWORD`, `DEPLOY_WEBHOOK_SECRET`)
- [ ] Fichier `deploy-webhook.php` uploadé sur le serveur
- [ ] Token webhook synchronisé (GitHub Secret = deploy-webhook.php)
- [ ] Workflow `.github/workflows/deploy-production.yml` commité
- [ ] Premier déploiement testé (push vers main)
- [ ] API testée : https://gfc.camoo.hosting/api/teams
- [ ] Dashboard testé : https://gfc.camoo.hosting/dashboard
- [ ] Login admin fonctionne

---

## 🎉 Résultat

**Après configuration** :

```powershell
# Déployer en production devient aussi simple que :
git add .
git commit -m "Update feature"
git push origin main

# 🎯 Et c'est tout ! GitHub fait le reste automatiquement.
```

**URLs de Production** :
- 🌐 Dashboard : https://gfc.camoo.hosting/dashboard
- 🔗 API : https://gfc.camoo.hosting/api
- 🏆 Webhook : https://gfc.camoo.hosting/gfc/deploy-webhook.php

**Login Admin** :
- Email : `admin@gfc.local`
- Password : `GFC@admin2026!`

---

**Créé le 2026-09-01 pour GFC 2026 MVP**
**Deployment as Code - Infrastructure as Code**

# ⚡ Configuration Rapide - Déploiement GitHub Actions vers gfc.trugroup.cm

## 🎯 Statut Actuel

✅ Code poussé vers GitHub : https://github.com/efoka24-ops/gfc2026  
✅ Workflow GitHub Actions configuré : `.github/workflows/deploy-production.yml`  
✅ URLs de production configurées : **http://gfc.trugroup.cm**  
⏳ **En attente** : Configuration des secrets GitHub

---

## 🔑 Étape 1 : Configurer les Secrets GitHub (OBLIGATOIRE)

Le workflow ne peut pas démarrer sans ces secrets :

### 1. Allez sur GitHub

https://github.com/efoka24-ops/gfc2026/settings/secrets/actions

### 2. Cliquez sur "New repository secret" pour chaque secret :

| Nom du Secret | Valeur | Description |
|---------------|--------|-------------|
| **FTP_USERNAME** | `trugro9159_gfc` | Nom d'utilisateur FTP |
| **FTP_PASSWORD** | `km5ZdqbD%Mx6` | Mot de passe FTP |
| **DEPLOY_WEBHOOK_SECRET** | `GFC2026SecretWebhook!TruGroup` | Token pour webhook (changez-le!) |

### 3. Sauvegardez chaque secret

Cliquez sur **Add secret** après avoir rempli chaque champ.

---

## 🚀 Étape 2 : Upload Initial du Webhook

**Avant que le déploiement automatique fonctionne**, vous devez uploader manuellement le fichier webhook :

### Via cPanel File Manager :

1. Connectez-vous à **cPanel TruGroup**
2. Ouvrez **File Manager**
3. Naviguez vers le dossier où l'application sera déployée
4. Uploadez le fichier : `C:\Projects\gfc\deploy-webhook.php`
5. **Important** : Éditez le fichier et changez la ligne 9 :
   ```php
   $WEBHOOK_SECRET = 'GFC2026SecretWebhook!TruGroup';
   ```
   (Utilisez le MÊME token que dans GitHub Secret `DEPLOY_WEBHOOK_SECRET`)

---

## ▶️ Étape 3 : Déclencher le Déploiement

### Option A : Déploiement Automatique (Recommandé)

Chaque fois que vous faites un `git push origin main`, GitHub Actions déploie automatiquement.

**Déjà fait !** Le dernier push devrait avoir déclenché le workflow.

Vérifiez ici : https://github.com/efoka24-ops/gfc2026/actions

### Option B : Déploiement Manuel

1. Allez sur : https://github.com/efoka24-ops/gfc2026/actions
2. Cliquez sur **Deploy GFC 2026 to Production** (à gauche)
3. Cliquez sur **Run workflow** (bouton à droite)
4. Sélectionnez la branche **main**
5. Cliquez sur **Run workflow** (bouton vert)

---

## 🔍 Étape 4 : Vérifier le Déploiement

### 1. Logs GitHub Actions

https://github.com/efoka24-ops/gfc2026/actions

- ✅ Toutes les étapes doivent être vertes
- ⏱️ Durée : ~5-10 minutes

### 2. Test de l'API

```bash
curl http://gfc.trugroup.cm/api/teams
```

Devrait retourner un JSON avec 10 équipes.

### 3. Test du Dashboard

Ouvrez : http://gfc.trugroup.cm/dashboard

Login :
- Email : `admin@gfc.local`
- Password : `GFC@admin2026!`

---

## 📁 Structure sur le Serveur

Le workflow déploie vers :
```
/home/trugro9159/public_html/gfc/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── index.php          # API Laravel
│   └── dashboard/         # Dashboard React
├── vendor/                # Dépendances Composer
├── .env                   # Configuration production
├── deploy-webhook.php     # Webhook de déploiement
└── ...
```

---

## 🐛 Dépannage

### Erreur : "FTP connection failed"

**Cause** : Secrets GitHub pas configurés ou incorrects

**Solution** :
1. Vérifiez les secrets : https://github.com/efoka24-ops/gfc2026/settings/secrets/actions
2. Testez la connexion FTP manuellement avec FileZilla

### Erreur : "Webhook returned 401"

**Cause** : Token webhook différent entre GitHub et le serveur

**Solution** :
1. Le token dans GitHub Secret `DEPLOY_WEBHOOK_SECRET` doit être identique à celui dans `deploy-webhook.php` (ligne 9)

### Erreur : "Database connection refused"

**Cause** : Base de données MySQL pas créée sur le serveur

**Solution** :
1. Dans cPanel → **MySQL Databases**
2. Créez la base : `trugro9159_gfc`
3. Créez l'utilisateur : `trugro9159_gfc` avec mot de passe `fxTach7b#C?s`
4. Donnez tous les privilèges à l'utilisateur sur cette base

---

## 📊 Que fait le Workflow ?

1. ✅ Checkout du code depuis GitHub
2. ✅ Installation de PHP 8.1 + Composer
3. ✅ Installation des dépendances Laravel (`composer install`)
4. ✅ Setup Node.js 20
5. ✅ Installation des dépendances React (`npm ci`)
6. ✅ Build du dashboard avec URL production (`npm run build`)
7. ✅ Préparation du package (sans tests, sans node_modules)
8. ✅ Upload FTP vers `/home/trugro9159/public_html/gfc/`
9. ✅ Appel du webhook pour :
   - Exécuter les migrations (`php artisan migrate`)
   - Charger les données initiales (`php artisan db:seed`)
   - Optimiser le cache (`php artisan config:cache`, etc.)
10. ✅ Déploiement terminé !

---

## 🎉 Résultat Final

Après configuration et déploiement :

🌐 **Dashboard** : http://gfc.trugroup.cm/dashboard  
🔗 **API** : http://gfc.trugroup.cm/api  
👤 **Login** : admin@gfc.local / GFC@admin2026!

---

## 🔄 Workflow de Développement

```powershell
# 1. Faire des modifications localement
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
# Vérifiez : https://github.com/efoka24-ops/gfc2026/actions
```

---

## ✅ Checklist de Configuration

- [ ] Secrets GitHub configurés (FTP_USERNAME, FTP_PASSWORD, DEPLOY_WEBHOOK_SECRET)
- [ ] Fichier deploy-webhook.php uploadé sur le serveur
- [ ] Token webhook synchronisé (GitHub = deploy-webhook.php)
- [ ] Base de données MySQL créée dans cPanel (trugro9159_gfc)
- [ ] Workflow exécuté avec succès (toutes étapes vertes)
- [ ] API testée : http://gfc.trugroup.cm/api/teams
- [ ] Dashboard testé : http://gfc.trugroup.cm/dashboard
- [ ] Login admin fonctionne

---

**Configuration créée le 2026-09-01**  
**Production URL : http://gfc.trugroup.cm**

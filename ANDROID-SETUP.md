# 📱 Guide Lancement Émulateur Android pour GFC Mobile

## État Actuel
✅ **Expo Metro Bundler** : Actif sur http://127.0.0.1:8082  
✅ **QR Code** : Disponible dans le terminal  
⏳ **Émulateur Android** : Attente du lancement

## Méthodes de Test

### Option 1 : Expo Go sur Téléphone Réel (RECOMMANDÉ)
**Le plus simple et le plus rapide !**

1. Installez **Expo Go** sur votre téléphone Android :
   - [Google Play Store - Expo Go](https://play.google.com/store/apps/details?id=host.exp.exponent)

2. Scannez le QR code affiché dans le terminal Expo

3. L'application GFC s'ouvrira automatiquement !

4. Pour se connecter, utilisez :
   - Email : `admin@gfc.local`
   - Mot de passe : `GFC@admin2026!`
   - (Note : Le login doit fonctionner sur mobile même si le web a un problème)

### Option 2 : Émulateur Android Studio

**Prérequis :** Android Studio doit être installé avec un AVD (Android Virtual Device) configuré.

#### A. Vérifier si Android Studio est installé
```powershell
# Chercher adb.exe
Get-Command adb -ErrorAction SilentlyContinue
```

Si rien ne s'affiche, Android Studio n'est pas installé ou pas dans le PATH.

#### B. Installer Android Studio (si nécessaire)
1. Téléchargez : https://developer.android.com/studio
2. Installez avec SDK Platform 33 (Android 13) minimum
3. Créez un AVD via AVD Manager :
   - Device: Pixel 5
   - System Image: Android 13 (API 33)

#### C. Lancer l'émulateur
**Méthode 1 : Via Android Studio**
```
Android Studio > Tools > Device Manager > Play button sur votre AVD
```

**Méthode 2 : Via ligne de commande**
```powershell
# Liste les AVD disponibles
emulator -list-avds

# Lance un AVD (remplacer Pixel_5_API_33 par votre nom)
emulator -avd Pixel_5_API_33
```

**Méthode 3 : Laisser Expo lancer l'émulateur**
Une fois l'émulateur démarré manuellement, dans le terminal Expo :
```
Press 'a' pour ouvrir l'app dans l'émulateur
```

### Option 3 : Expo Go dans l'émulateur

Si vous avez un émulateur Android mais pas Expo Go installé dedans :

1. Démarrez votre émulateur Android
2. Dans le terminal Expo, appuyez sur `a`
3. Expo installera automatiquement Expo Go dans l'émulateur
4. L'app GFC s'ouvrira

### Option 4 : Expo Web (test rapide)
Pour tester rapidement sans émulateur :

Dans le terminal Expo, appuyez sur `w` pour ouvrir dans le navigateur web.

**Note :** Certaines fonctionnalités natives ne fonctionneront pas.

## Configuration API

L'application mobile est configurée pour se connecter à :
```
http://10.0.2.2:8000/api
```

**Explication :**
- `10.0.2.2` = adresse spéciale Android qui pointe vers `localhost` de votre PC
- Fonctionne uniquement dans l'émulateur Android
- Pour téléphone réel, utilisez l'IP locale de votre PC (ex: `http://192.168.1.100:8000/api`)

## Dépannage

### Problème : "Could not connect to development server"
**Solution :**
```powershell
# Vérifiez que l'API Laravel tourne
# Terminal 1
C:\Projects\gfc\start-api.ps1

# Vérifiez le pare-feu Windows
# Autorisez PHP et Expo sur le réseau local
```

### Problème : "No Android devices connected"
**Solutions :**
1. Vérifiez que l'émulateur est bien démarré (`adb devices`)
2. Redémarrez ADB : `adb kill-server && adb start-server`
3. Relancez Expo : `npx expo start --clear`

### Problème : "Metro Bundler timeout"
**Solution :**
```powershell
# Nettoyez le cache et redémarrez
cd C:\Projects\gfc\mobile
npx expo start --clear --reset-cache
```

## Commandes Utiles

### Terminal Expo
- `a` : Ouvrir sur Android
- `i` : Ouvrir sur iOS (Mac uniquement)
- `w` : Ouvrir sur Web
- `r` : Recharger l'app
- `m` : Toggle menu développeur
- `j` : Ouvrir debugger
- `c` : Clear console
- `?` : Aide complète

### Vérifier les appareils connectés
```powershell
adb devices
```

### Logs Android en temps réel
```powershell
adb logcat *:S ReactNative:V ReactNativeJS:V
```

## Prochaines Étapes

1. **Choisissez votre méthode** (téléphone réel recommandé)
2. **Testez la connexion** avec admin@gfc.local
3. **Naviguez dans l'app** :
   - Accueil : Vue d'ensemble
   - Matchs : Programme et résultats
   - Classement : Positions des équipes
   - Profil : Informations utilisateur

## URL et Terminaux Actifs

📡 **API Backend** : http://localhost:8000/api  
🌐 **Dashboard Web** : http://localhost:5173  
📱 **Metro Bundler** : http://127.0.0.1:8082  
🔍 **QR Code** : Visible dans le terminal Expo

---

**Besoin d'aide ?** Consultez la documentation Expo : https://docs.expo.dev/get-started/create-a-project/

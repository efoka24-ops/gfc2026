# 🐛 Résolution Problème Login GFC

## Symptômes
- Formulaire de login ne répond pas (champs admin@gfc.local / GFC@admin2026!)
- Erreur HTTP 401 "Identifiants invalides"
- Logo GFC ✅ (corrigé)

## Tests Effectués

### ✅ Test 1: Utilisateur Existe
```bash
php check-user.php
# Résultat: User found! Password: CORRECT
```

### ✅ Test 2: API Accessible
```bash
curl http://localhost:8000/api/teams
# Résultat: 200 OK (API fonctionne)
```

### ❌ Test 3: Login API
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@gfc.local","password":"GFC@admin2026!","device_name":"web"}'
# Résultat: 401 {"error":{"code":"non_authentifie","message":"Identifiants invalides."}}
```

## Diagnostic

Le format de l'erreur `{"error": {"code": "...", "message": "..."}}` ne correspond PAS au format standard de Laravel ValidationException.

**Hypothèse**: Il existe un Handler d'exceptions personnalisé qui transforme TOUTES les réponses d'erreur.

## Solutions

### Solution 1: Vérifier Handler (URGENT)
```bash
cat api/app/Exceptions/Handler.php
```

Cherchez une méthode `render()` qui pourrait transformer les exceptions.

### Solution 2: Tester Sans Handler
Commentez temporairement tout code personnalisé dans `Handler.php` et retestez.

### Solution 3: Vérifier Middleware Auth
```bash
# Vérifier si un middleware bloque les requêtes
cat api/app/Http/Middleware/Authenticate.php
```

### Solution 4: Logs Laravel
```bash
# Voir les erreurs détaillées
tail -f api/storage/logs/laravel.log
```

Puis retentez la connexion pour voir l'erreur complète.

### Solution 5: Test Direct PHP
Créez `api/test-direct-login.php`:
```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@gfc.local')->first();
if ($user && Hash::check('GFC@admin2026!', $user->password)) {
    $token = $user->createToken('test', ['*'])->plainTextToken;
    echo "SUCCESS! Token: $token\n";
} else {
    echo "FAIL: Invalid credentials\n";
}
```

Puis: `php api/test-direct-login.php`

## Actions Immédiates

1. **Vérifier `api/app/Exceptions/Handler.php`** pour code personnalisé
2. **Lire logs** : `api/storage/logs/laravel.log`
3. **Tester token manual** avec le script PHP ci-dessus
4. **Vérifier routes** : `php artisan route:list | grep login`

## Workaround Temporaire

Si vous avez besoin de tester le dashboard MAINTENANT:

1. Créez un token manuellement:
```bash
php api/test-direct-login.php
```

2. Copiez le token obtenu

3. Dans le navigateur (console), exécutez:
```javascript
localStorage.setItem('gfc_token', 'VOTRE_TOKEN_ICI');
window.location.href = '/';
```

4. Vous serez connecté et pourrez tester le dashboard.

## État Émulateur Android

📱 **Expo Metro Bundler** : En cours de démarrage...
⏳ Veuillez patienter que le QR code s'affiche.

Une fois affiché, appuyez sur **`a`** pour lancer l'émulateur Android.

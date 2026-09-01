<?php
/**
 * Script d'installation GFC 2026 pour environnement web
 * Uploadez ce fichier dans /home/trugro9159/public_html/gfc/
 * Puis accédez à: https://votre-domaine.com/gfc/setup-web.php
 */

// Sécurité basique
$SETUP_PASSWORD = 'GFC@setup2026!';
$providedPassword = $_GET['password'] ?? '';

if ($providedPassword !== $SETUP_PASSWORD) {
    die('<!DOCTYPE html><html><body><h1>GFC 2026 - Installation</h1><form><input type="password" name="password" placeholder="Mot de passe setup"><button>Continuer</button></form></body></html>');
}

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>GFC Setup</title><style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#00ff00}h1{color:#ff6b00}pre{background:#000;padding:10px;border-left:3px solid #ff6b00}.success{color:#00ff00}.error{color:#ff0000}.step{margin:20px 0;padding:15px;border:1px solid #333}</style></head><body>";
echo "<h1>🏆 GFC 2026 - Installation Automatique</h1>";

$baseDir = __DIR__;
echo "<div class='step'><strong>📁 Répertoire:</strong> $baseDir</div>";

// Vérifier si on est dans le bon dossier
if (!file_exists($baseDir . '/artisan')) {
    echo "<div class='error'>❌ Erreur: fichier 'artisan' non trouvé. Assurez-vous que ce script est dans le dossier racine Laravel.</div>";
    echo "</body></html>";
    exit;
}

// Fonction pour exécuter des commandes et afficher le résultat
function runCommand($command, $description) {
    global $baseDir;
    echo "<div class='step'>";
    echo "<strong>⚡ $description</strong><br>";
    echo "<code>$ $command</code><br>";
    
    $output = [];
    $returnCode = 0;
    
    // Changer le répertoire de travail
    $currentDir = getcwd();
    chdir($baseDir);
    
    exec($command . ' 2>&1', $output, $returnCode);
    
    chdir($currentDir);
    
    echo "<pre>";
    echo htmlspecialchars(implode("\n", $output));
    echo "</pre>";
    
    if ($returnCode === 0) {
        echo "<span class='success'>✅ Succès</span>";
    } else {
        echo "<span class='error'>⚠️ Code retour: $returnCode</span>";
    }
    echo "</div>";
    
    return $returnCode === 0;
}

// 1. Installer les dépendances Composer
echo "<h2>📦 Étape 1: Installation des dépendances</h2>";
runCommand('composer install --no-dev --optimize-autoloader --no-interaction', 'Installation Composer');

// 2. Vérifier le fichier .env
echo "<h2>🔧 Étape 2: Configuration .env</h2>";
if (!file_exists($baseDir . '/.env')) {
    if (file_exists($baseDir . '/.env.production')) {
        copy($baseDir . '/.env.production', $baseDir . '/.env');
        echo "<div class='success'>✅ .env créé depuis .env.production</div>";
    } else {
        echo "<div class='error'>❌ Aucun fichier .env trouvé</div>";
    }
} else {
    echo "<div class='success'>✅ .env existe déjà</div>";
}

// 3. Générer la clé d'application
echo "<h2>🔑 Étape 3: Génération de la clé Laravel</h2>";
runCommand('php artisan key:generate --force', 'Génération APP_KEY');

// 4. Créer le lien de stockage
echo "<h2>🔗 Étape 4: Lien de stockage</h2>";
runCommand('php artisan storage:link', 'Création du lien symbolique');

// 5. Migrations
echo "<h2>🗄️ Étape 5: Migration de la base de données</h2>";
runCommand('php artisan migrate --force', 'Exécution des migrations');

// 6. Seeders
echo "<h2>🌱 Étape 6: Chargement des données initiales</h2>";
runCommand('php artisan db:seed --force', 'Exécution des seeders');

// 7. Cache
echo "<h2>⚡ Étape 7: Optimisation du cache</h2>";
runCommand('php artisan config:cache', 'Cache de configuration');
runCommand('php artisan route:cache', 'Cache des routes');
runCommand('php artisan view:cache', 'Cache des vues');

// 8. Permissions (via PHP)
echo "<h2>🔐 Étape 8: Permissions des fichiers</h2>";
echo "<div class='step'>";
echo "<strong>⚡ Ajustement des permissions</strong><br>";

$storageDir = $baseDir . '/storage';
$bootstrapCacheDir = $baseDir . '/bootstrap/cache';

function setPermissionsRecursive($path, $fileMode = 0664, $dirMode = 0775) {
    if (is_dir($path)) {
        chmod($path, $dirMode);
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                setPermissionsRecursive($path . '/' . $item, $fileMode, $dirMode);
            }
        }
    } else {
        chmod($path, $fileMode);
    }
}

try {
    setPermissionsRecursive($storageDir);
    setPermissionsRecursive($bootstrapCacheDir);
    echo "<span class='success'>✅ Permissions ajustées</span>";
} catch (Exception $e) {
    echo "<span class='error'>⚠️ Erreur: " . $e->getMessage() . "</span>";
}

echo "</div>";

// Résumé final
echo "<h2>🎉 Installation Terminée !</h2>";
echo "<div class='step'>";
echo "<h3>📋 Informations de connexion:</h3>";
echo "<ul>";
echo "<li><strong>Email:</strong> admin@gfc.local</li>";
echo "<li><strong>Mot de passe:</strong> GFC@admin2026!</li>";
echo "</ul>";

// Détecter le domaine actuel
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $domain;

echo "<h3>🌐 URLs de l'application:</h3>";
echo "<ul>";
echo "<li><strong>API:</strong> <a href='$baseUrl/api' target='_blank'>$baseUrl/api</a></li>";
echo "<li><strong>Dashboard:</strong> <a href='$baseUrl/dashboard' target='_blank'>$baseUrl/dashboard</a></li>";
echo "<li><strong>API Teams:</strong> <a href='$baseUrl/api/teams' target='_blank'>$baseUrl/api/teams</a></li>";
echo "</ul>";

echo "<h3>⚠️ SÉCURITÉ:</h3>";
echo "<p class='error'><strong>IMPORTANT: Supprimez ce fichier setup-web.php immédiatement après l'installation !</strong></p>";
echo "</div>";

echo "<div style='margin-top:30px;padding:15px;background:#ff6b00;color:#000'>";
echo "<strong>✅ L'application GFC 2026 est maintenant opérationnelle !</strong>";
echo "</div>";

echo "</body></html>";
?>

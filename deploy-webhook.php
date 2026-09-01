<?php
/**
 * Webhook de déploiement GFC 2026
 * À placer dans /home/trugro9159/public_html/gfc/deploy-webhook.php
 * 
 * Permet d'exécuter les commandes post-déploiement de manière sécurisée
 */

// Configuration
$WEBHOOK_SECRET = getenv('DEPLOY_WEBHOOK_SECRET') ?: 'GFC@webhook2026!ChangeMe';
$BASE_DIR = __DIR__;

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

// Gestion OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Vérification du token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$providedToken = str_replace('Bearer ', '', $authHeader);

if ($providedToken !== $WEBHOOK_SECRET) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Récupérer l'action
$action = $_POST['action'] ?? $_GET['action'] ?? 'full';

$response = [
    'status' => 'success',
    'timestamp' => date('Y-m-d H:i:s'),
    'actions' => []
];

// Fonction helper pour exécuter une commande
function executeCommand($command, $description) {
    global $BASE_DIR, $response;
    
    $output = [];
    $returnCode = 0;
    
    chdir($BASE_DIR);
    exec($command . ' 2>&1', $output, $returnCode);
    
    $response['actions'][] = [
        'description' => $description,
        'command' => $command,
        'output' => implode("\n", $output),
        'success' => $returnCode === 0,
        'return_code' => $returnCode
    ];
    
    return $returnCode === 0;
}

// Exécuter les actions selon le type
switch ($action) {
    case 'migrate':
        // Migrations uniquement
        executeCommand('php artisan migrate --force', 'Database migration');
        break;
        
    case 'seed':
        // Seeders uniquement
        executeCommand('php artisan db:seed --force', 'Database seeding');
        break;
        
    case 'cache':
        // Optimisation du cache
        executeCommand('php artisan config:cache', 'Config cache');
        executeCommand('php artisan route:cache', 'Route cache');
        executeCommand('php artisan view:cache', 'View cache');
        break;
        
    case 'clear':
        // Clear cache
        executeCommand('php artisan cache:clear', 'Clear cache');
        executeCommand('php artisan config:clear', 'Clear config');
        executeCommand('php artisan route:clear', 'Clear routes');
        executeCommand('php artisan view:clear', 'Clear views');
        break;
        
    case 'full':
        // Déploiement complet
        executeCommand('php artisan config:clear', 'Clear old config');
        executeCommand('php artisan migrate --force', 'Run migrations');
        
        // Vérifier si on doit seed (seulement si la table users est vide)
        $shouldSeed = true;
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=trugro9159_gfc',
                'trugro9159_gfc',
                'fxTach7b#C?s'
            );
            $stmt = $pdo->query('SELECT COUNT(*) FROM users');
            $count = $stmt->fetchColumn();
            $shouldSeed = ($count == 0);
        } catch (Exception $e) {
            $response['actions'][] = [
                'description' => 'Check if seeding needed',
                'output' => 'Could not check, will seed anyway: ' . $e->getMessage(),
                'success' => false
            ];
        }
        
        if ($shouldSeed) {
            executeCommand('php artisan db:seed --force', 'Seed database');
        } else {
            $response['actions'][] = [
                'description' => 'Database seeding',
                'output' => 'Skipped - data already exists',
                'success' => true
            ];
        }
        
        executeCommand('php artisan config:cache', 'Cache config');
        executeCommand('php artisan route:cache', 'Cache routes');
        executeCommand('php artisan view:cache', 'Cache views');
        
        // Permissions
        chmod($BASE_DIR . '/storage', 0775);
        chmod($BASE_DIR . '/bootstrap/cache', 0775);
        
        $response['actions'][] = [
            'description' => 'Set permissions',
            'output' => 'storage/ and bootstrap/cache/ set to 775',
            'success' => true
        ];
        break;
        
    default:
        http_response_code(400);
        $response['status'] = 'error';
        $response['error'] = 'Invalid action. Use: migrate, seed, cache, clear, or full';
}

// Déterminer le statut global
$allSuccess = true;
foreach ($response['actions'] as $action) {
    if (!$action['success']) {
        $allSuccess = false;
        break;
    }
}

if (!$allSuccess) {
    $response['status'] = 'partial';
    http_response_code(207); // Multi-Status
}

echo json_encode($response, JSON_PRETTY_PRINT);

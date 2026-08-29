<?php
/**
 * Configuration de l'application.
 *
 * Aucun secret ne figure dans ce fichier : il est versionne.
 * Deux sources de valeurs, dans cet ordre de priorite :
 *   1. config/config.local.php  — hors depot, utilise en production
 *      (les hebergements mutualises n'exposent pas de variables d'environnement
 *      au processus PHP de maniere fiable)
 *   2. les variables d'environnement GFC_*  — utilisees en developpement
 *   3. les valeurs de repli ci-dessous, valables en developpement uniquement
 */
declare(strict_types=1);

$local = [];
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
}

$value = static function (string $key, string $env, string $fallback) use ($local): string {
    if (isset($local[$key]) && $local[$key] !== '') {
        return (string) $local[$key];
    }
    $fromEnv = getenv($env);
    return ($fromEnv !== false && $fromEnv !== '') ? $fromEnv : $fallback;
};

return [
    'db' => [
        'host'     => $value('db_host', 'GFC_DB_HOST', '127.0.0.1'),
        'name'     => $value('db_name', 'GFC_DB_NAME', 'gfc'),
        'user'     => $value('db_user', 'GFC_DB_USER', 'root'),
        'password' => $value('db_pass', 'GFC_DB_PASS', ''),
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'name'            => 'Garoua Football Challenge',
        'current_season'  => 1,
        'token_ttl_hours' => 12,
        // Nombre de places qualificatives au classement du championnat.
        // 8 pour la 6e edition : les quarts de finale du Grand Prix Gabriel
        // MBAIROBE se jouent a 8 equipes sur les 10 engagees (decision D11).
        // Valeur de configuration, jamais codee en dur dans une requete : une
        // edition a 12 equipes ne doit pas demander de toucher au code.
        'qualification_places' => 8,
        'upload_dir'      => __DIR__ . '/../public/uploads',
        'base_url'        => $value('base_url', 'GFC_BASE_URL', 'http://localhost:8000'),
    ],
];

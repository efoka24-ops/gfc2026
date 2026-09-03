<?php
declare(strict_types=1);

/*
 * Configuration de l'application.
 * Les valeurs par defaut ci-dessous conviennent au developpement. En production
 * (et en local reel), creer config/config.local.php qui retourne un tableau
 * partiel fusionne par-dessus — il porte les secrets et n'est PAS versionne.
 */
$defaults = [
    'app' => [
        'name'     => 'Garoua Football Challenge',
        'env'      => 'production',
        'key'      => 'changez-cette-cle-32-caracteres-min',
        'base_url' => 'http://gfc.trugroup.cm',
        'timezone' => 'Africa/Douala',
    ],
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'gfc',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'session' => [
        'name'     => 'gfc_admin',
        'lifetime' => 60 * 60 * 8,
        'secure'   => false,
        'samesite' => 'Lax',
    ],
    'uploads' => [
        'path'      => __DIR__ . '/../storage/uploads',
        'url'       => '/storage/uploads',
        'max_bytes' => 10 * 1024 * 1024,
        'mimes'     => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
    ],
    'sms'  => ['driver' => 'log', 'url' => '', 'token' => '', 'sender' => 'GFC'],
    'push' => ['driver' => 'fcm', 'fcm_key' => ''],
];

$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
    foreach ($local as $section => $values) {
        $defaults[$section] = array_merge($defaults[$section] ?? [], is_array($values) ? $values : []);
    }
}

return $defaults;

<?php
return [
    'db' => [
        'host'     => getenv('GFC_DB_HOST') ?: '127.0.0.1',
        'name'     => getenv('GFC_DB_NAME') ?: 'gfc',
        'user'     => getenv('GFC_DB_USER') ?: 'root',
        'password' => getenv('GFC_DB_PASS') ?: '',
        'charset'  => 'utf8mb4',
    ],
    'app' => [
        'name'            => 'Garoua Football Challenge',
        'current_season'  => 1,
        'token_ttl_hours' => 12,
        'upload_dir'      => __DIR__ . '/../public/uploads',
        'base_url'        => getenv('GFC_BASE_URL') ?: 'http://localhost:8000',
    ],
];

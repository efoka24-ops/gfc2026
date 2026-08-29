<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $path = __DIR__ . '/../../src/' . str_replace(['Gfc\\', '\\'], ['', '/'], $class) . '.php';
    if (is_file($path)) { require $path; }
});

$config = require __DIR__ . '/../../config/config.php';

function e(?string $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string
{
    \Gfc\Auth::startSession();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_check(): void
{
    \Gfc\Auth::startSession();
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Jeton CSRF invalide');
    }
}

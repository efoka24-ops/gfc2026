<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $path = __DIR__ . '/../../src/' . str_replace(['Gfc\\', '\\'], ['', '/'], $class) . '.php';
    if (is_file($path)) { require $path; }
});

$config = require __DIR__ . '/../../config/config.php';

function e(?string $v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

/**
 * Jeton anti-falsification, a poser dans chaque formulaire (FR-027).
 *
 * Ces deux fonctions ne sont qu'un raccourci de gabarit : la seule
 * implementation du CSRF vit dans Gfc\Auth, pour que le back-office et tout
 * autre point d'entree partagent le meme jeton et la meme verification.
 */
function csrf_token(): string
{
    return \Gfc\Auth::csrfToken();
}

function csrf_check(): void
{
    \Gfc\Auth::requireCsrf();
}

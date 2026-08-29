<?php
/**
 * Modele de configuration de production.
 *
 * A copier en `config/config.local.php` DIRECTEMENT SUR LE SERVEUR, jamais dans
 * le depot : `config.local.php` est ignore par git et refuse par HTTP
 * (voir config/.htaccess).
 *
 *   cp config/config.local.example.php config/config.local.php
 *
 * Les identifiants de base de donnees se creent dans cPanel, section
 * « Bases de donnees MySQL ». Sur cet hebergement, le nom de la base et celui
 * de l'utilisateur sont prefixes par le compte : trugro9159_gfc.
 */
declare(strict_types=1);

return [
    'db_host'  => 'localhost',
    'db_name'  => 'trugro9159_gfc',
    'db_user'  => 'trugro9159_gfc',
    'db_pass'  => 'A_RENSEIGNER',
    'base_url' => 'https://gfc.trugroup.cm',
];

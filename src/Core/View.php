<?php
declare(strict_types=1);

namespace Gfc\Core;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $file = BASE_PATH . '/templates/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Vue introuvable : $template");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    public static function e(?string $v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function money(int $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }

    public static function date(?string $sql, string $format = 'D j M · H:i'): string
    {
        if ($sql === null) {
            return '—';
        }
        static $days   = ['Mon' => 'Lun', 'Tue' => 'Mar', 'Wed' => 'Mer', 'Thu' => 'Jeu', 'Fri' => 'Ven', 'Sat' => 'Sam', 'Sun' => 'Dim'];
        static $months = ['Jan' => 'jan', 'Feb' => 'fév', 'Mar' => 'mars', 'Apr' => 'avr', 'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil', 'Aug' => 'août', 'Sep' => 'sept', 'Oct' => 'oct', 'Nov' => 'nov', 'Dec' => 'déc'];
        $out = (new \DateTimeImmutable($sql))->format($format);
        return str_replace(array_keys($days + $months), array_values($days + $months), $out);
    }
}

<?php
declare(strict_types=1);

namespace Gfc\Service;

final class SmsSender
{
    public function __construct(private array $cfg)
    {
    }

    public function send(string $phone, string $message): bool
    {
        if (($this->cfg['driver'] ?? 'log') === 'log') {
            error_log('[SMS] ' . $phone . ' : ' . $message);
            return true;
        }

        $ch = curl_init($this->cfg['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->cfg['token'],
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'to'     => $phone,
                'from'   => $this->cfg['sender'],
                'text'   => $message,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $ok = curl_exec($ch) !== false && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) < 300;
        curl_close($ch);
        return $ok;
    }
}

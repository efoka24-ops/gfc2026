<?php
declare(strict_types=1);

namespace Gfc\Service;

use Gfc\Core\Database;

/**
 * Notifications push aux supporters qui suivent l'une des deux équipes.
 */
final class Notifier
{
    public function __construct(private Database $db, private array $config)
    {
    }

    public function matchEvent(int $matchId, string $type, int $minute): void
    {
        $m = $this->context($matchId);
        if ($m === null) {
            return;
        }

        $label = match ($type) {
            'goal', 'penalty' => 'But !',
            'own_goal'        => 'But contre son camp',
            'red'             => 'Carton rouge',
            default           => 'Fait de match',
        };

        $this->push(
            $matchId,
            $label . ' ' . $minute . "'",
            $m['home'] . ' ' . (int) $m['home_score'] . ' – ' . (int) $m['away_score'] . ' ' . $m['away']
        );
    }

    public function matchFinished(int $matchId): void
    {
        $m = $this->context($matchId);
        if ($m === null) {
            return;
        }

        $this->push(
            $matchId,
            'Coup de sifflet final',
            $m['home'] . ' ' . (int) $m['home_score'] . ' – ' . (int) $m['away_score'] . ' ' . $m['away']
        );
    }

    private function context(int $matchId): ?array
    {
        return $this->db->one(
            'SELECT m.home_score, m.away_score, h.name AS home, a.name AS away,
                    m.home_team_id, m.away_team_id
               FROM matches m
               JOIN teams h ON h.id = m.home_team_id
               JOIN teams a ON a.id = m.away_team_id
              WHERE m.id = ?',
            [$matchId]
        );
    }

    /** @return string[] tokens destinataires */
    private function recipients(int $matchId): array
    {
        return array_column($this->db->all(
            'SELECT DISTINCT d.push_token
               FROM matches m
               JOIN favorites f ON f.team_id IN (m.home_team_id, m.away_team_id)
               JOIN devices d ON d.app_user_id = f.app_user_id
              WHERE m.id = ?',
            [$matchId]
        ), 'push_token');
    }

    private function push(int $matchId, string $title, string $body): void
    {
        $tokens = $this->recipients($matchId);
        $key    = $this->config['push']['fcm_key'] ?? '';

        if ($tokens === [] || $key === '') {
            error_log(sprintf('[PUSH] %s — %s (%d destinataires)', $title, $body, count($tokens)));
            return;
        }

        foreach (array_chunk($tokens, 500) as $chunk) {
            $ch = curl_init('https://fcm.googleapis.com/fcm/send');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: key=' . $key],
                CURLOPT_POSTFIELDS     => json_encode([
                    'registration_ids' => $chunk,
                    'notification'     => ['title' => $title, 'body' => $body],
                    'data'             => ['match_id' => $matchId],
                ], JSON_UNESCAPED_UNICODE),
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}

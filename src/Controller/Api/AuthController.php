<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Service\SmsSender;

final class AuthController extends Controller
{
    public function requestOtp(Request $req, array $args): never
    {
        $phone = preg_replace('/\s+/', '', $req->str('phone'));
        if (!preg_match('/^\+?[0-9]{8,15}$/', $phone)) {
            Response::error('invalid_phone', 'Numéro de téléphone invalide.', 422);
        }

        $recent = (int) $this->db->value(
            'SELECT COUNT(*) FROM otp_codes WHERE phone = ? AND expires_at > NOW()',
            [$phone]
        );
        if ($recent >= 3) {
            Response::error('too_many', 'Trop de demandes. Réessayez dans quelques minutes.', 429);
        }

        $code = (string) random_int(100000, 999999);
        $this->db->insert('otp_codes', [
            'phone'      => $phone,
            'code_hash'  => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => (new \DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s'),
        ]);

        (new SmsSender($this->config['sms']))->send(
            $phone,
            'GFC : votre code de connexion est ' . $code . '. Valable 10 minutes.'
        );

        Response::json(['sent' => true, 'expires_in' => 600]);
    }

    public function verifyOtp(Request $req, array $args): never
    {
        $phone = preg_replace('/\s+/', '', $req->str('phone'));
        $code  = $req->str('code');

        $row = $this->db->one(
            'SELECT * FROM otp_codes WHERE phone = ? AND used_at IS NULL AND expires_at > NOW()
              ORDER BY id DESC LIMIT 1',
            [$phone]
        );

        if ($row === null || !password_verify($code, $row['code_hash'])) {
            Response::error('invalid_code', 'Code incorrect ou expiré.', 422);
        }

        $this->db->run('UPDATE otp_codes SET used_at = NOW() WHERE id = ?', [$row['id']]);

        $appUserId = (int) ($this->db->value('SELECT id FROM app_users WHERE phone = ?', [$phone])
            ?? $this->db->insert('app_users', ['phone' => $phone]));

        Response::json([
            'token' => $this->auth->issueToken(null, $appUserId),
            'user'  => ['id' => $appUserId, 'phone' => $phone],
        ]);
    }
}

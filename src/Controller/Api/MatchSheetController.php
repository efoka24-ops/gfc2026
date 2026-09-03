<?php
declare(strict_types=1);

namespace Gfc\Controller\Api;

use Gfc\Core\Controller;
use Gfc\Core\Request;
use Gfc\Core\Response;
use Gfc\Repository\MatchRepository;
use Gfc\Service\Notifier;
use Gfc\Service\SanctionEngine;

final class MatchSheetController extends Controller
{
    private const TYPES = ['goal','own_goal','penalty','miss','yellow','red','sub','shot','period','note'];

    public function addEvent(Request $req, array $args): never
    {
        $matchId = (int) $args['id'];
        $user    = $this->auth->requireStaff($req, 'sheet.write', null, $matchId);

        $type = $req->str('type');
        if (!in_array($type, self::TYPES, true)) {
            Response::error('invalid_type', 'Type d\'événement inconnu.', 422);
        }

        $minute = $req->int('minute', 0);
        if ($minute < 0 || $minute > 130) {
            Response::error('invalid_minute', 'Minute hors limites.', 422);
        }

        $repo = new MatchRepository($this->db);

        $eventId = $this->db->transaction(function () use ($req, $matchId, $type, $minute, $user, $repo): int {
            $id = $this->db->insert('match_events', [
                'match_id'     => $matchId,
                'minute'       => $minute,
                'type'         => $type,
                'team_id'      => $req->int('team_id'),
                'player_id'    => $req->int('player_id'),
                'player_in_id' => $req->int('player_in_id'),
                'note'         => $req->str('note') ?: null,
                'created_by'   => (int) $user['id'],
            ]);

            $repo->recomputeScore($matchId);
            $this->db->run('UPDATE matches SET minute = GREATEST(COALESCE(minute,0), ?) WHERE id = ?', [$minute, $matchId]);
            return $id;
        });

        $this->auth->log((int) $user['id'], 'sheet.event.add', 'match_events', $eventId, ['type' => $type, 'minute' => $minute]);

        // Notification push aux supporters des deux équipes.
        if (in_array($type, ['goal', 'penalty', 'own_goal', 'red'], true)) {
            (new Notifier($this->db, $this->config))->matchEvent($matchId, $type, $minute);
        }

        Response::json(['id' => $eventId, 'match' => $repo->find($matchId)], 201);
    }

    public function deleteEvent(Request $req, array $args): never
    {
        $matchId = (int) $args['id'];
        $user    = $this->auth->requireStaff($req, 'sheet.write', null, $matchId);

        $this->db->run('DELETE FROM match_events WHERE id = ? AND match_id = ?', [(int) $args['eventId'], $matchId]);
        (new MatchRepository($this->db))->recomputeScore($matchId);
        $this->auth->log((int) $user['id'], 'sheet.event.delete', 'match_events', (int) $args['eventId']);

        Response::json(['deleted' => true]);
    }

    public function setStatus(Request $req, array $args): never
    {
        $matchId = (int) $args['id'];
        $user    = $this->auth->requireStaff($req, 'sheet.write', null, $matchId);

        $status = $req->str('status');
        if (!in_array($status, ['scheduled','live','halftime','finished','postponed'], true)) {
            Response::error('invalid_status', 'Statut inconnu.', 422);
        }

        $this->db->run(
            'UPDATE matches SET status = ?, minute = COALESCE(?, minute) WHERE id = ?',
            [$status, $req->int('minute'), $matchId]
        );
        $this->auth->log((int) $user['id'], 'match.status', 'matches', $matchId, ['status' => $status]);

        if ($status === 'finished') {
            (new SanctionEngine($this->db))->applyForMatch($matchId);
            (new Notifier($this->db, $this->config))->matchFinished($matchId);
        }

        Response::json(['status' => $status]);
    }

    public function validateSheet(Request $req, array $args): never
    {
        $matchId = (int) $args['id'];
        $user    = $this->auth->requireStaff($req, 'sheet.validate');

        $this->db->run('UPDATE matches SET sheet_status = "validated" WHERE id = ?', [$matchId]);
        (new MatchRepository($this->db))->recomputeScore($matchId);
        (new SanctionEngine($this->db))->applyForMatch($matchId);
        $this->auth->log((int) $user['id'], 'sheet.validate', 'matches', $matchId);

        Response::json(['sheet_status' => 'validated']);
    }
}

<?php
declare(strict_types=1);

namespace Gfc\Repository;

use Gfc\Core\Database;

final class CompetitionRepository
{
    public function __construct(private Database $db)
    {
    }

    public function forEdition(int $editionId): array
    {
        $comps = $this->db->all(
            'SELECT id, name, slug, type, format, color, qualify_slots
               FROM competitions WHERE edition_id = ? ORDER BY id',
            [$editionId]
        );

        foreach ($comps as &$c) {
            $c['phases'] = $this->db->all(
                'SELECT id, name, ord, status, starts_on FROM phases
                  WHERE competition_id = ? ORDER BY ord',
                [$c['id']]
            );
        }
        return $comps;
    }

    public function find(int $id): ?array
    {
        return $this->db->one('SELECT * FROM competitions WHERE id = ?', [$id]);
    }
}

<?php
use Gfc\Core\View;

$title    = 'Calendrier';
$kicker   = 'Compétition';
$subtitle = 'Programmation des rencontres et désignation des arbitres';
$action   = 'Programmer';
$exportUrl = '/admin/calendar?export=csv';

ob_start();
?>
<?php
$cols = [
    ['label' => 'Date', 'w' => '150px'], ['label' => 'Rencontre'],
    ['label' => 'Compétition', 'w' => '160px'], ['label' => 'Stade', 'w' => '160px'],
    ['label' => 'Arbitre', 'w' => '140px'], ['label' => 'Statut', 'w' => '130px'],
];

$rows = array_map(static function (array $m): array {
    $status = match ($m['status']) {
        'live', 'halftime' => ['Live ' . (int) $m['minute'] . "'", 'bad'],
        'finished'         => ['Terminé ' . (int) $m['home_score'] . '–' . (int) $m['away_score'], 'neutral'],
        'postponed'        => ['Reporté', 'bad'],
        default            => ['Programmé', 'wait'],
    };
    return [
        ['v' => View::date($m['kickoff_at'])],
        ['v' => $m['home'] . ' – ' . $m['away'], 'strong' => true],
        ['v' => $m['competition']],
        ['v' => $m['venue'] ?? '—'],
        ['v' => $m['referee'] ?? 'Non assigné', 'pill' => $m['referee'] === null ? 'wait' : null],
        ['v' => $status[0], 'pill' => $status[1]],
    ];
}, $matches);

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

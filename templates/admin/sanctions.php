<?php
use Gfc\Core\View;

$title    = 'Sanctions';
$kicker   = 'Discipline';
$subtitle = 'Cartons, suspensions et amendes';
$action   = 'Nouvelle sanction';

ob_start();
?>
<?php
$cols = [
    ['label' => 'Joueur'], ['label' => 'Équipe'], ['label' => 'Motif'],
    ['label' => 'Match', 'w' => '190px'], ['label' => 'Décision', 'w' => '170px'],
    ['label' => 'Statut', 'w' => '110px'],
];

$rows = array_map(static function (array $s): array {
    $decision = $s['games_banned'] > 0
        ? (int) $s['games_banned'] . ' match' . ($s['games_banned'] > 1 ? 's' : '') . ' de suspension'
        : ((int) $s['fine_amount'] > 0 ? View::money((int) $s['fine_amount']) : '—');

    return [
        ['v' => $s['player'] ?? $s['team'], 'strong' => true],
        ['v' => $s['team']],
        ['v' => $s['reason']],
        ['v' => $s['home'] === null ? '—' : $s['home'] . ' – ' . $s['away']],
        ['v' => $decision, 'pill' => (int) $s['games_banned'] > 0 ? 'bad' : 'neutral'],
        ['v' => match ($s['status']) { 'applied' => 'Appliquée', 'appealed' => 'Contestée', 'cancelled' => 'Annulée', default => 'Ouverte' },
         'pill' => match ($s['status']) { 'applied' => 'ok', 'cancelled' => 'neutral', default => 'wait' }],
    ];
}, $sanctions);

$count = count($sanctions) . ' dossiers';

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

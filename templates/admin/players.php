<?php
use Gfc\Core\View;

$title    = 'Joueurs & statistiques';
$kicker   = 'Acteurs';
$subtitle = 'Statistiques issues des feuilles de match validées';
$action   = 'Ajouter un joueur';
$exportUrl = '/admin/players?export=csv';

ob_start();
?>
<?php
$cols = [
    ['label' => 'Joueur'], ['label' => 'Équipe'], ['label' => 'Poste', 'w' => '80px'],
    ['label' => 'Buts', 'w' => '64px', 'align' => 'center'],
    ['label' => 'Jaunes', 'w' => '72px', 'align' => 'center'],
    ['label' => 'Rouges', 'w' => '72px', 'align' => 'center'],
];

$rows = array_map(static fn (array $p): array => [
    ['v' => $p['player_name'], 'strong' => true],
    ['v' => $p['team']],
    ['v' => $p['position'], 'pill' => 'neutral'],
    ['v' => (int) $p['goals'], 'num' => true],
    ['v' => (int) $p['yellows']],
    ['v' => (int) $p['reds']],
], $players);

$count = count($players) . ' licenciés';

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

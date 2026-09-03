<?php
use Gfc\Core\View;

$title    = 'Classements';
$kicker   = 'Compétition';
$subtitle = 'Calculé automatiquement après validation de chaque feuille de match';
$action   = 'Recalculer';
$exportUrl = '/admin/standings?export=csv';

ob_start();
?>
<?php
$cols = [
    ['label' => '#', 'w' => '46px', 'align' => 'center'], ['label' => 'Équipe'],
    ['label' => 'J', 'w' => '48px', 'align' => 'center'], ['label' => 'G', 'w' => '48px', 'align' => 'center'],
    ['label' => 'N', 'w' => '48px', 'align' => 'center'], ['label' => 'P', 'w' => '48px', 'align' => 'center'],
    ['label' => 'BP', 'w' => '52px', 'align' => 'center'], ['label' => 'BC', 'w' => '52px', 'align' => 'center'],
    ['label' => '+/-', 'w' => '56px', 'align' => 'center'], ['label' => 'Pts', 'w' => '58px', 'align' => 'center'],
];

$rows = array_map(static fn (array $r): array => [
    ['v' => $r['rank'], 'num' => true],
    ['v' => $r['team_name'], 'strong' => true],
    ['v' => $r['played']], ['v' => $r['won']], ['v' => $r['drawn']], ['v' => $r['lost']],
    ['v' => $r['goals_for']], ['v' => $r['goals_against']],
    ['v' => ((int) $r['goal_diff'] > 0 ? '+' : '') . (int) $r['goal_diff']],
    ['v' => $r['points'], 'num' => true],
], $standings);

$search  = 'Filtrer une équipe…';
$filters = array_map(static fn (array $c): string => $c['name'], $competitions);
$count   = 'Classement recalculé à chaque validation de feuille de match';

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

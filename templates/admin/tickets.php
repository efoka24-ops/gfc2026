<?php
use Gfc\Core\View;

$title    = 'Billetterie';
$kicker   = 'Communication';
$subtitle = 'Tarifs, quotas et recettes par rencontre';
$action   = 'Nouveau tarif';
$exportUrl = '/admin/tickets?export=csv';

ob_start();
?>
<?php
$cols = [
    ['label' => 'Rencontre'], ['label' => 'Tarif', 'w' => '150px'],
    ['label' => 'Quota', 'w' => '80px', 'align' => 'center'],
    ['label' => 'Vendus', 'w' => '80px', 'align' => 'center'],
    ['label' => 'Recette', 'w' => '150px', 'align' => 'right'],
    ['label' => 'Guichet', 'w' => '110px'],
];

$totalSold    = 0;
$totalRevenue = 0;

$rows = array_map(static function (array $t) use (&$totalSold, &$totalRevenue): array {
    $totalSold    += (int) $t['sold'];
    $totalRevenue += (int) $t['revenue'];
    return [
        ['v' => $t['home'] . ' – ' . $t['away'] . ' · ' . View::date($t['kickoff_at'], 'j M'), 'strong' => true],
        ['v' => $t['label'] . ' · ' . View::money((int) $t['price'])],
        ['v' => (int) $t['quota']],
        ['v' => (int) $t['sold'], 'num' => true],
        ['v' => View::money((int) $t['revenue'])],
        ['v' => $t['status'] === 'open' ? 'Ouvert' : 'Clôturé', 'pill' => $t['status'] === 'open' ? 'ok' : 'neutral'],
    ];
}, $tickets);

$count = number_format($totalSold, 0, ',', ' ') . ' billets vendus · ' . View::money($totalRevenue);

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

<?php
use Gfc\Core\View;

$title    = 'Équipes & effectifs';
$kicker   = 'Acteurs';
$subtitle = "Équipes engagées dans l'édition courante";
$action   = 'Ajouter une équipe';
$exportUrl = '/admin/teams?export=csv';

ob_start();
?>
<?php
$cols = [
    ['label' => 'Équipe'], ['label' => 'Ville'], ['label' => 'Entraîneur'],
    ['label' => 'Effectif', 'w' => '80px', 'align' => 'center'],
    ['label' => 'Licences', 'w' => '110px', 'align' => 'center'],
    ['label' => 'Dossier', 'w' => '150px'],
];

$rows = array_map(static function (array $t): array {
    $pending = (int) $t['licenses_pending'];
    return [
        ['v' => $t['name'], 'strong' => true],
        ['v' => $t['city']],
        ['v' => $t['coach'] ?? '—'],
        ['v' => $t['squad_size'], 'num' => true],
        ['v' => $pending === 0 ? 'Complètes' : $pending . ' manquante' . ($pending > 1 ? 's' : ''), 'pill' => $pending === 0 ? 'ok' : 'wait'],
        ['v' => match ($t['status']) { 'validated' => 'Validé', 'rejected' => 'Rejeté', default => 'En attente' },
         'pill' => match ($t['status']) { 'validated' => 'ok', 'rejected' => 'bad', default => 'wait' }],
    ];
}, $teams);

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

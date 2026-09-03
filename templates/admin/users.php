<?php
use Gfc\Core\View;

$title    = 'Utilisateurs & rôles';
$kicker   = 'Administration';
$subtitle = "Administrateurs, délégués d'équipe et arbitres";
$action   = 'Inviter';

ob_start();
?>
<?php
$roleLabels = ['admin' => 'Administrateur', 'delegate' => 'Délégué équipe', 'referee' => 'Arbitre', 'editor' => 'Éditeur'];

$permissions = [
    "Créer / modifier une équipe"      => ['admin' => 'oui', 'delegate' => 'partiel', 'referee' => 'non'],
    "Gérer l'effectif et les licences" => ['admin' => 'oui', 'delegate' => 'oui',     'referee' => 'non'],
    'Programmer une rencontre'         => ['admin' => 'oui', 'delegate' => 'non',     'referee' => 'non'],
    'Saisir la feuille de match'       => ['admin' => 'oui', 'delegate' => 'non',     'referee' => 'oui'],
    'Valider la feuille de match'      => ['admin' => 'oui', 'delegate' => 'non',     'referee' => 'non'],
    'Publier une actualité'            => ['admin' => 'oui', 'delegate' => 'non',     'referee' => 'non'],
    'Gérer la billetterie'             => ['admin' => 'oui', 'delegate' => 'non',     'referee' => 'non'],
    'Prononcer une sanction'           => ['admin' => 'oui', 'delegate' => 'non',     'referee' => 'non'],
];
$marks = ['oui' => ['✓', 'yes'], 'partiel' => ['~', 'partial'], 'non' => ['✕', 'no']];
?>
<div class="card card--pad">
  <p class="card__title">Matrice des permissions</p>
  <div class="table__scroll">
    <table class="table table--matrix">
      <thead><tr><th>Action</th><th>Admin</th><th>Délégué</th><th>Arbitre</th></tr></thead>
      <tbody>
        <?php foreach ($permissions as $label => $cells): ?>
          <tr>
            <td><?= View::e($label) ?></td>
            <?php foreach (['admin', 'delegate', 'referee'] as $role): ?>
              <?php [$glyph, $cls] = $marks[$cells[$role]]; ?>
              <td class="mark mark--<?= $cls ?>"><?= $glyph ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$cols = [
    ['label' => 'Nom'], ['label' => 'Rôle', 'w' => '150px'], ['label' => 'Périmètre', 'w' => '190px'],
    ['label' => 'Téléphone', 'w' => '150px'], ['label' => 'Statut', 'w' => '150px'],
];

$rows = array_map(static function (array $u) use ($roleLabels): array {
    return [
        ['v' => $u['name'], 'strong' => true],
        ['v' => $roleLabels[$u['role']] ?? $u['role'], 'pill' => 'neutral'],
        ['v' => $u['team'] ?? 'Toute la compétition'],
        ['v' => $u['phone']],
        ['v' => match ($u['status']) { 'active' => 'Actif', 'invited' => 'Invitation envoyée', default => 'Désactivé' },
         'pill' => match ($u['status']) { 'active' => 'ok', 'invited' => 'wait', default => 'neutral' }],
    ];
}, $users);

$count = count($users) . ' comptes';

require __DIR__ . '/_table.php';
?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

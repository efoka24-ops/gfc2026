<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin', 'secretaire']);
$title  = 'Équipes';
$active = 'teams.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    Database::run(
        'INSERT INTO teams (name, abbr, quarter, founded_year, coach, color) VALUES (?,?,?,?,?,?)',
        [$_POST['name'], strtoupper($_POST['abbr']), $_POST['quarter'] ?: null,
         $_POST['founded_year'] !== '' ? (int) $_POST['founded_year'] : null,
         $_POST['coach'] ?: null, $_POST['color'] ?: '#7A1F30']
    );
    header('Location: teams.php');
    exit;
}

$teams = Database::all(
    'SELECT t.*, (SELECT COUNT(*) FROM players p WHERE p.team_id = t.id AND p.is_active = 1) AS squad_size
     FROM teams t ORDER BY t.name'
);

ob_start(); ?>
<div class="card">
  <h2>Nouvelle équipe</h2>
  <form method="post" class="grid c4" style="align-items:end">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <label>Nom<input type="text" name="name" required></label>
    <label>Abréviation<input type="text" name="abbr" maxlength="5" required></label>
    <label>Quartier<input type="text" name="quarter"></label>
    <label>Année de création<input type="number" name="founded_year" min="1950" max="2030"></label>
    <label>Entraîneur<input type="text" name="coach"></label>
    <label>Couleur<input type="color" name="color" value="#7A1F30"></label>
    <div><button class="btn" type="submit">Enregistrer</button></div>
  </form>
</div>

<div class="card">
  <h2><?= count($teams) ?> équipes</h2>
  <table>
    <thead><tr><th>Équipe</th><th>Abrév.</th><th>Quartier</th><th>Créée</th><th>Entraîneur</th><th>Effectif</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($teams as $t): ?>
      <tr>
        <td><span class="pos" style="background:<?= e($t['color']) ?>;color:#fff"><?= e(mb_substr($t['abbr'], 0, 1)) ?></span> <strong><?= e($t['name']) ?></strong></td>
        <td><?= e($t['abbr']) ?></td>
        <td><?= e($t['quarter']) ?></td>
        <td class="num"><?= (int) $t['founded_year'] ?: '—' ?></td>
        <td><?= e($t['coach']) ?: '—' ?></td>
        <td class="num"><?= (int) $t['squad_size'] ?></td>
        <td><a class="btn ghost sm" href="players.php?team=<?= (int) $t['id'] ?>">Effectif</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

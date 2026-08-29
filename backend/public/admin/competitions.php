<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin', 'secretaire']);
$title  = 'Compétitions';
$active = 'competitions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'create') {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name']), '-'));
        Database::run(
            'INSERT INTO competitions (season_id, name, slug, type, description, start_date, end_date, sort_order)
             VALUES (?,?,?,?,?,?,?,?)',
            [(int) $config['app']['current_season'], $_POST['name'], $slug, $_POST['type'],
             $_POST['description'] ?: null, $_POST['start_date'] ?: null, $_POST['end_date'] ?: null,
             (int) ($_POST['sort_order'] ?: 0)]
        );
    }
    if (($_POST['action'] ?? '') === 'engage') {
        Database::run(
            'INSERT IGNORE INTO competition_team (competition_id, team_id) VALUES (?,?)',
            [(int) $_POST['competition_id'], (int) $_POST['team_id']]
        );
    }
    header('Location: competitions.php');
    exit;
}

$competitions = Database::all(
    'SELECT c.*, (SELECT COUNT(*) FROM competition_team ct WHERE ct.competition_id = c.id) AS teams,
            (SELECT COUNT(*) FROM matches m WHERE m.competition_id = c.id) AS matches
     FROM competitions c WHERE c.season_id = ? ORDER BY c.sort_order',
    [(int) $config['app']['current_season']]
);
$teams = Database::all('SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name');
$TYPES = ['league' => 'Championnat', 'cup' => 'Coupe (élimination directe)', 'supercup' => 'Super Coupe (match unique)'];

ob_start(); ?>
<div class="card">
  <h2>Nouvelle compétition</h2>
  <form method="post" class="grid" style="gap:14px">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="create">
    <div class="grid c4">
      <label>Nom<input type="text" name="name" required></label>
      <label>Format<select name="type"><?php foreach ($TYPES as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?></select></label>
      <label>Début<input type="date" name="start_date"></label>
      <label>Fin<input type="date" name="end_date"></label>
    </div>
    <label>Description<textarea name="description" rows="3"></textarea></label>
    <div><button class="btn" type="submit">Créer</button></div>
  </form>
</div>

<div class="card">
  <h2>Compétitions de la saison</h2>
  <table>
    <thead><tr><th>Nom</th><th>Format</th><th>Période</th><th>Équipes</th><th>Matchs</th></tr></thead>
    <tbody>
    <?php foreach ($competitions as $c): ?>
      <tr>
        <td><strong><?= e($c['name']) ?></strong><div class="hint"><?= e($c['description']) ?></div></td>
        <td><?= e($TYPES[$c['type']] ?? $c['type']) ?></td>
        <td><?= $c['start_date'] ? e(date('d/m', strtotime($c['start_date']))) . ' → ' . e(date('d/m/Y', strtotime($c['end_date'] ?: $c['start_date']))) : '—' ?></td>
        <td class="num"><?= (int) $c['teams'] ?></td>
        <td class="num"><?= (int) $c['matches'] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h2>Engager une équipe</h2>
  <form method="post" class="grid c4" style="align-items:end">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="action" value="engage">
    <label>Compétition
      <select name="competition_id"><?php foreach ($competitions as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
    </label>
    <label>Équipe
      <select name="team_id"><?php foreach ($teams as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select>
    </label>
    <div><button class="btn" type="submit">Engager</button></div>
  </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

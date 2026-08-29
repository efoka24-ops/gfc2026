<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin', 'secretaire']);
$title  = 'Joueurs';
$active = 'players.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    Database::run(
        'INSERT INTO players (team_id, jersey_number, first_name, last_name, position, position_label, birth_date, height_cm, licence_no)
         VALUES (?,?,?,?,?,?,?,?,?)',
        [(int) $_POST['team_id'], $_POST['jersey_number'] !== '' ? (int) $_POST['jersey_number'] : null,
         $_POST['first_name'], $_POST['last_name'], $_POST['position'], $_POST['position_label'] ?: null,
         $_POST['birth_date'] ?: null, $_POST['height_cm'] !== '' ? (int) $_POST['height_cm'] : null,
         $_POST['licence_no'] ?: null]
    );
    header('Location: players.php?team=' . (int) $_POST['team_id']);
    exit;
}

$teamId  = (int) ($_GET['team'] ?? 0);
$teams   = Database::all('SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name');
$where   = $teamId ? 'WHERE p.team_id = ?' : '';
$params  = $teamId ? [$teamId] : [];
$players = Database::all(
    "SELECT p.*, t.name AS team, TIMESTAMPDIFF(YEAR, p.birth_date, CURDATE()) AS age
     FROM players p JOIN teams t ON t.id = p.team_id $where
     ORDER BY t.name, FIELD(p.position,'GB','DEF','MIL','ATT'), p.jersey_number",
    $params
);
$POS = ['GB' => 'Gardien', 'DEF' => 'Défenseur', 'MIL' => 'Milieu', 'ATT' => 'Attaquant'];

ob_start(); ?>
<div class="card">
  <h2>Nouveau joueur</h2>
  <form method="post" class="grid c4" style="align-items:end">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <label>Équipe
      <select name="team_id" required>
        <?php foreach ($teams as $t): ?><option value="<?= (int) $t['id'] ?>" <?= $teamId === (int) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Numéro<input type="number" name="jersey_number" min="1" max="99"></label>
    <label>Prénom<input type="text" name="first_name" required></label>
    <label>Nom<input type="text" name="last_name" required></label>
    <label>Poste
      <select name="position" required>
        <?php foreach ($POS as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Poste précis<input type="text" name="position_label" placeholder="Avant-centre"></label>
    <label>Date de naissance<input type="date" name="birth_date"></label>
    <label>Taille (cm)<input type="number" name="height_cm" min="140" max="220"></label>
    <label>N° de licence<input type="text" name="licence_no"></label>
    <div><button class="btn" type="submit">Enregistrer</button></div>
  </form>
</div>

<div class="card">
  <div class="card-head">
    <h2><?= count($players) ?> joueurs</h2>
    <div class="right">
      <form method="get">
        <select name="team" onchange="this.form.submit()">
          <option value="0">Toutes les équipes</option>
          <?php foreach ($teams as $t): ?><option value="<?= (int) $t['id'] ?>" <?= $teamId === (int) $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
  <table>
    <thead><tr><th>N°</th><th>Joueur</th><th>Poste</th><th>Âge</th><th>Équipe</th><th>Licence</th></tr></thead>
    <tbody>
    <?php foreach ($players as $p): ?>
      <tr>
        <td class="num"><strong><?= (int) $p['jersey_number'] ?></strong></td>
        <td><strong><?= e($p['first_name'] . ' ' . $p['last_name']) ?></strong></td>
        <td><?= e($p['position_label'] ?: ($POS[$p['position']] ?? '')) ?></td>
        <td class="num"><?= (int) $p['age'] ?: '—' ?></td>
        <td><?= e($p['team']) ?></td>
        <td><?= e($p['licence_no']) ?: '—' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

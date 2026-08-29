<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin', 'secretaire']);
$title  = 'Matchs & calendrier';
$active = 'matches.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    Database::run(
        'INSERT INTO matches (competition_id, matchday, round_label, home_team_id, away_team_id, kickoff_at, venue, referee)
         VALUES (?,?,?,?,?,?,?,?)',
        [
            (int) $_POST['competition_id'],
            $_POST['matchday'] !== '' ? (int) $_POST['matchday'] : null,
            $_POST['round_label'] ?: null,
            (int) $_POST['home_team_id'],
            (int) $_POST['away_team_id'],
            $_POST['kickoff_at'],
            $_POST['venue'] ?: null,
            $_POST['referee'] ?: null,
        ]
    );
    header('Location: matches.php');
    exit;
}

$competitions = Database::all('SELECT id, name FROM competitions ORDER BY sort_order');
$teams        = Database::all('SELECT id, name FROM teams WHERE is_active = 1 ORDER BY name');
$matches      = Database::all(
    "SELECT m.*, c.name comp, h.name h, a.name a
     FROM matches m JOIN competitions c ON c.id = m.competition_id
     JOIN teams h ON h.id = m.home_team_id JOIN teams a ON a.id = m.away_team_id
     ORDER BY m.kickoff_at DESC LIMIT 60"
);

ob_start(); ?>
<div class="card">
  <h2>Programmer un match</h2>
  <form method="post" class="grid" style="gap:14px">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="grid c4">
      <label>Compétition
        <select name="competition_id" required>
          <?php foreach ($competitions as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Journée<input type="number" name="matchday" min="1" max="30" placeholder="championnat"></label>
      <label>Tour<input type="text" name="round_label" placeholder="Demi-finale"></label>
      <label>Coup d'envoi<input type="datetime-local" name="kickoff_at" required></label>
    </div>
    <div class="grid c4">
      <label>Équipe à domicile
        <select name="home_team_id" required>
          <?php foreach ($teams as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Équipe à l'extérieur
        <select name="away_team_id" required>
          <?php foreach ($teams as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Stade<input type="text" name="venue" placeholder="Stade Roumdé Adjia"></label>
      <label>Arbitre<input type="text" name="referee"></label>
    </div>
    <div><button class="btn" type="submit">Ajouter au calendrier</button></div>
  </form>
</div>

<div class="card">
  <h2>Derniers matchs</h2>
  <table>
    <thead><tr><th>Date</th><th>Compétition</th><th>Affiche</th><th>Score</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($matches as $m): ?>
      <tr>
        <td><?= e(date('d/m/Y H\\hi', strtotime($m['kickoff_at']))) ?></td>
        <td><?= e($m['comp']) ?><?= $m['matchday'] ? ' · J' . (int) $m['matchday'] : ($m['round_label'] ? ' · ' . e($m['round_label']) : '') ?></td>
        <td><?= e($m['h']) ?> vs <?= e($m['a']) ?></td>
        <td class="num"><?= $m['home_score'] === null ? '—' : (int) $m['home_score'] . ' – ' . (int) $m['away_score'] ?></td>
        <td><span class="badge <?= $m['status'] === 'finished' ? 'ok' : ($m['status'] === 'live' ? 'live' : 'grey') ?>"><?= e($m['status']) ?></span></td>
        <td><a class="btn ghost sm" href="live.php?match=<?= (int) $m['id'] ?>">Saisie</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

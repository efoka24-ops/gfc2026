<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;
use Gfc\Repo;
use Gfc\Score;

require __DIR__ . '/../../src/Score.php';

$user   = Auth::requireSession(['admin', 'secretaire', 'arbitre']);
$title  = 'Saisie live';
$active = 'live.php';

$matchId = (int) ($_GET['match'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $matchId = (int) $_POST['match_id'];

    if (($_POST['action'] ?? '') === 'event') {
        Database::run(
            'INSERT INTO match_events (match_id, team_id, player_id, related_player_id, minute, type, detail, is_published, created_by)
             VALUES (?,?,?,?,?,?,?,1,?)',
            [
                $matchId,
                $_POST['team_id'] ?: null,
                $_POST['player_id'] ?: null,
                $_POST['related_player_id'] ?: null,
                (int) $_POST['minute'],
                $_POST['type'],
                $_POST['detail'] ?: null,
                $user['id'],
            ]
        );
        Score::recompute($matchId);
    }

    if (($_POST['action'] ?? '') === 'status') {
        Database::run('UPDATE matches SET status = ?, minute = ? WHERE id = ?',
            [$_POST['status'], (int) $_POST['minute'], $matchId]);
    }

    if (($_POST['action'] ?? '') === 'delete_event') {
        Database::run('DELETE FROM match_events WHERE id = ? AND match_id = ?', [(int) $_POST['event_id'], $matchId]);
        Score::recompute($matchId);
    }

    header('Location: live.php?match=' . $matchId);
    exit;
}

$openMatches = Database::all(
    "SELECT m.id, m.status, m.minute, m.kickoff_at, h.name h, a.name a, c.name c
     FROM matches m JOIN teams h ON h.id = m.home_team_id JOIN teams a ON a.id = m.away_team_id
     JOIN competitions c ON c.id = m.competition_id
     WHERE m.status <> 'finished' ORDER BY m.kickoff_at LIMIT 20"
);
$match  = $matchId ? Repo::match($matchId) : null;
$squads = [];
if ($match) {
    foreach ([[$match['home_id'], 'home'], [$match['away_id'], 'away']] as [$tid, $side]) {
        $squads[$side] = Database::all(
            "SELECT id, jersey_number, CONCAT(first_name,' ',last_name) AS name
             FROM players WHERE team_id = ? AND is_active = 1 ORDER BY jersey_number",
            [$tid]
        );
    }
}

$EVENTS = [
    'goal'   => 'But', 'penalty' => 'Penalty', 'own_goal' => 'But contre son camp',
    'yellow' => 'Carton jaune', 'red' => 'Carton rouge', 'sub' => 'Changement',
    'penalty_missed' => 'Penalty manqué', 'var' => 'Décision arbitrale',
];

ob_start(); ?>
<?php if (!$match): ?>
  <div class="card">
    <h2>Choisir un match</h2>
    <table>
      <thead><tr><th>Date</th><th>Compétition</th><th>Affiche</th><th>Statut</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($openMatches as $m): ?>
        <tr>
          <td><?= e(date('d/m H\\hi', strtotime($m['kickoff_at']))) ?></td>
          <td><?= e($m['c']) ?></td>
          <td><strong><?= e($m['h']) ?></strong> vs <strong><?= e($m['a']) ?></strong></td>
          <td><span class="badge <?= $m['status'] === 'live' ? 'live' : 'grey' ?>"><?= e($m['status']) ?></span></td>
          <td><a class="btn or sm" href="live.php?match=<?= (int) $m['id'] ?>">Ouvrir</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>

  <div class="card">
    <div class="card-head">
      <h2><?php if (in_array($match['status'], ['live','halftime'], true)): ?><span class="dot"></span><?php endif; ?>
        <?= e($match['home_name']) ?> <?= (int) $match['home_score'] ?> – <?= (int) $match['away_score'] ?> <?= e($match['away_name']) ?></h2>
      <div class="right">
        <form method="post" class="grid c4" style="align-items:end;grid-template-columns:130px 90px auto">
          <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="status">
          <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">
          <label>Statut
            <select name="status">
              <?php foreach (['scheduled' => 'Programmé', 'live' => 'En cours', 'halftime' => 'Mi-temps', 'finished' => 'Terminé', 'postponed' => 'Reporté'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= $match['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>Minute<input type="number" name="minute" min="0" max="130" value="<?= (int) $match['minute'] ?>"></label>
          <button class="btn ghost sm" type="submit">Mettre à jour</button>
        </form>
      </div>
    </div>
    <div class="hint"><?= e($match['competition']) ?> · <?= e($match['round_label'] ?: 'Journée ' . (int) $match['matchday']) ?> · <?= e($match['venue']) ?> · arbitre <?= e($match['referee']) ?></div>
  </div>

  <div class="card">
    <h2>Ajouter un événement</h2>
    <form method="post" class="grid" style="gap:16px">
      <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="action" value="event">
      <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">

      <div class="event-grid">
        <?php foreach ($EVENTS as $k => $label): ?>
          <button type="button" data-type="<?= $k ?>" aria-pressed="<?= $k === 'goal' ? 'true' : 'false' ?>"><?= $label ?></button>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="type" id="type" value="goal">

      <div class="grid c4">
        <label>Minute<input type="number" name="minute" min="0" max="130" value="<?= (int) $match['minute'] ?>" required></label>
        <label>Équipe
          <select name="team_id" id="team" required>
            <option value="<?= (int) $match['home_id'] ?>"><?= e($match['home_name']) ?></option>
            <option value="<?= (int) $match['away_id'] ?>"><?= e($match['away_name']) ?></option>
          </select>
        </label>
        <label>Joueur
          <select name="player_id" id="player">
            <?php foreach ($squads['home'] as $p): ?>
              <option value="<?= (int) $p['id'] ?>" data-team="<?= (int) $match['home_id'] ?>">#<?= (int) $p['jersey_number'] ?> <?= e($p['name']) ?></option>
            <?php endforeach; ?>
            <?php foreach ($squads['away'] as $p): ?>
              <option value="<?= (int) $p['id'] ?>" data-team="<?= (int) $match['away_id'] ?>">#<?= (int) $p['jersey_number'] ?> <?= e($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Passeur / joueur sortant
          <select name="related_player_id">
            <option value="">—</option>
            <?php foreach (array_merge($squads['home'], $squads['away']) as $p): ?>
              <option value="<?= (int) $p['id'] ?>">#<?= (int) $p['jersey_number'] ?> <?= e($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <label>Précision (optionnel)<input type="text" name="detail" maxlength="180" placeholder="Contre-attaque, 2e poteau…"></label>
      <div><button class="btn or" type="submit">Publier l'événement</button></div>
      <p class="hint">L'événement publié est immédiatement visible dans l'application, met à jour le score, le classement et les statistiques des joueurs.</p>
    </form>
  </div>

  <div class="card">
    <h2>Fil du match</h2>
    <table>
      <thead><tr><th>Min</th><th>Événement</th><th>Joueur</th><th>Équipe</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($match['events'] as $ev): ?>
        <tr>
          <td class="num"><strong><?= (int) $ev['minute'] ?>'</strong></td>
          <td><?= e($EVENTS[$ev['type']] ?? $ev['type']) ?><?= $ev['detail'] ? ' · <span class="hint">' . e($ev['detail']) . '</span>' : '' ?></td>
          <td><?= e($ev['player']) ?><?= $ev['related_player'] ? ' <span class="hint">(' . e($ev['related_player']) . ')</span>' : '' ?></td>
          <td><?= e($ev['team_abbr']) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Supprimer cet événement ?')">
              <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
              <input type="hidden" name="action" value="delete_event">
              <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">
              <input type="hidden" name="event_id" value="<?= (int) $ev['id'] ?>">
              <button class="btn ghost sm" type="submit">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
  document.querySelectorAll('.event-grid button').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.event-grid button').forEach(function (x) { x.setAttribute('aria-pressed', 'false'); });
      b.setAttribute('aria-pressed', 'true');
      document.getElementById('type').value = b.dataset.type;
    });
  });
  var team = document.getElementById('team'), player = document.getElementById('player');
  function filterPlayers() {
    Array.from(player.options).forEach(function (o) { o.hidden = o.dataset.team !== team.value; });
    var first = Array.from(player.options).find(function (o) { return !o.hidden; });
    if (first) { player.value = first.value; }
  }
  team.addEventListener('change', filterPlayers);
  filterPlayers();
  </script>
<?php endif;
$content = ob_get_clean();
require __DIR__ . '/layout.php';

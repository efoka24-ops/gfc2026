<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;
use Gfc\Repo;

$user   = Auth::requireSession();
$title  = 'Tableau de bord';
$active = 'index.php';
$season = (int) $config['app']['current_season'];

$kpis = [
    'Équipes'           => Database::one('SELECT COUNT(*) n FROM teams WHERE is_active = 1')['n'],
    'Joueurs licenciés' => Database::one('SELECT COUNT(*) n FROM players WHERE is_active = 1')['n'],
    'Matchs joués'      => Database::one("SELECT COUNT(*) n FROM matches WHERE status = 'finished'")['n'],
    'Matchs en direct'  => Database::one("SELECT COUNT(*) n FROM matches WHERE status IN ('live','halftime')")['n'],
];
$standings = Repo::standings('championnat');
$live      = Repo::matches(['season' => $season, 'scope' => 'upcoming', 'limit' => 6]);

ob_start(); ?>
<div class="kpis">
  <?php foreach ($kpis as $label => $value): ?>
    <div class="kpi"><div class="l"><?= e($label) ?></div><div class="v"><?= (int) $value ?></div></div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-head">
    <h2>Prochains matchs</h2>
    <div class="right"><a class="btn ghost sm" href="matches.php">Gérer le calendrier</a></div>
  </div>
  <table>
    <thead><tr><th>Date</th><th>Compétition</th><th>Affiche</th><th>Stade</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($live as $m): ?>
      <tr>
        <td><?= e(date('d/m à H\\hi', strtotime($m['kickoff_at']))) ?></td>
        <td><?= e($m['competition']) ?><?= $m['matchday'] ? ' · J' . (int) $m['matchday'] : '' ?></td>
        <td><strong><?= e($m['home_name']) ?></strong> vs <strong><?= e($m['away_name']) ?></strong></td>
        <td><?= e($m['venue']) ?></td>
        <td>
          <?php if (in_array($m['status'], ['live', 'halftime'], true)): ?>
            <span class="badge live"><span class="dot"></span><?= (int) $m['minute'] ?>'</span>
          <?php else: ?>
            <span class="badge grey">Programmé</span>
          <?php endif; ?>
        </td>
        <td><a class="btn or sm" href="live.php?match=<?= (int) $m['id'] ?>">Saisie live</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <div class="card-head"><h2>Classement du championnat</h2><div class="right hint">Calculé automatiquement depuis les matchs terminés</div></div>
  <table>
    <thead><tr><th></th><th>Équipe</th><th>J</th><th>G</th><th>N</th><th>P</th><th>BP</th><th>BC</th><th>Diff</th><th>Pts</th></tr></thead>
    <tbody>
    <?php foreach ($standings as $i => $r):
      $pos = $i + 1;
      $cls = $pos <= 4 ? 'qual' : ($pos >= 9 ? 'out' : ''); ?>
      <tr>
        <td><span class="pos <?= $cls ?>"><?= $pos ?></span></td>
        <td><strong><?= e($r['name']) ?></strong></td>
        <td class="num"><?= (int) $r['played'] ?></td>
        <td class="num"><?= (int) $r['won'] ?></td>
        <td class="num"><?= (int) $r['drawn'] ?></td>
        <td class="num"><?= (int) $r['lost'] ?></td>
        <td class="num"><?= (int) $r['goals_for'] ?></td>
        <td class="num"><?= (int) $r['goals_against'] ?></td>
        <td class="num"><?= ((int) $r['goal_diff'] > 0 ? '+' : '') . (int) $r['goal_diff'] ?></td>
        <td class="num"><strong><?= (int) $r['points'] ?></strong></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

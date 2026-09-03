<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<div class="wrap" style="padding-top:6px">
  <nav class="subnav">
    <a href="/classement"<?= ($tab ?? '') === 'championnat' ? ' class="on"' : '' ?>>Championnat</a>
    <a href="/competitions?c=grand-prix"<?= ($tab ?? '') === 'grand-prix' ? ' class="on"' : '' ?>>Grand Prix Mbaïrobé</a>
    <a href="/competitions?c=super-coupe"<?= ($tab ?? '') === 'super-coupe' ? ' class="on"' : '' ?>>Super Coupe</a>
  </nav>
</div>

<?php if (($tab ?? 'championnat') === 'grand-prix'): ?>
  <section class="pagehead"><p class="pagehead__kicker">Tournoi principal</p><h1><?= View::e($cup['name'] ?? 'Grand Prix Gabriel Mbaïrobé') ?></h1>
    <p class="pagehead__sub"><?= View::e($cup['format'] ?? "Élimination directe entre les meilleures formations de l'édition.") ?></p></section>
  <?php $phases = []; foreach (($bracket ?? []) as $b) { $phases[$b['phase']][] = $b; } ?>
  <div class="bracket">
    <?php if ($phases === []): ?><div class="card card--pad">Tableau à venir.</div><?php endif; ?>
    <?php foreach ($phases as $name => $ties): ?>
      <div class="bracket__col">
        <div class="bracket__head"><?= View::e($name) ?></div>
        <?php foreach ($ties as $t): $done = in_array($t['status'], ['finished','live','halftime'], true); ?>
          <div class="bracket__tie">
            <div class="bracket__line"><span><?= View::e($t['home']) ?></span><b><?= $done ? (int) $t['home_score'] : '–' ?></b></div>
            <div class="bracket__line"><span><?= View::e($t['away']) ?></span><b><?= $done ? (int) $t['away_score'] : '–' ?></b></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

<?php elseif (($tab ?? 'championnat') === 'super-coupe'): ?>
  <section class="pagehead"><p class="pagehead__kicker">Le choc des champions</p><h1><?= View::e($super['name'] ?? 'Super Coupe') ?></h1></section>
  <?php if (!empty($superMatch)): $sm = $superMatch; $played = in_array($sm['status'], ['finished','live','halftime'], true); ?>
    <div class="supercup">
      <p class="supercup__meta">Finale<?= !empty($sm['kickoff_at']) ? ' · ' . View::date($sm['kickoff_at'], 'j F Y') : '' ?><?= !empty($sm['venue']) ? ' · ' . View::e($sm['venue']) : '' ?></p>
      <div class="supercup__teams">
        <div class="supercup__team"><?= View::e($sm['home']) ?><small>Champion du Championnat</small></div>
        <div class="supercup__vs"><?= $played ? (int) $sm['home_score'] . ' – ' . (int) $sm['away_score'] : 'VS' ?></div>
        <div class="supercup__team"><?= View::e($sm['away']) ?><small>Vainqueur du Grand Prix Mbaïrobé</small></div>
      </div>
    </div>
  <?php else: ?><div class="card card--pad">La finale de la Super Coupe sera programmée à l'issue des deux compétitions.</div><?php endif; ?>

<?php else: ?>
  <section class="pagehead"><h1>Classement</h1><p class="pagehead__sub">Championnat de vacances · calculé après chaque feuille de match validée</p></section>
  <table class="standtable">
    <thead><tr><th>#</th><th>Équipe</th><th>J</th><th>G</th><th>N</th><th>P</th><th>BP</th><th>BC</th><th>+/-</th><th>Pts</th></tr></thead>
    <tbody>
    <?php $qual = (int) ($league['qualify_slots'] ?? 0); ?>
    <?php foreach (($standings ?? []) as $r): $rk = (int) $r['rank']; ?>
      <tr class="<?= $rk <= max(3, $qual) ? 'top' : '' ?>">
        <td class="rk"><?= $rk ?></td>
        <td><a class="teamcell" href="/equipes/<?= (int) $r['team_id'] ?>" style="color:inherit;text-decoration:none">
          <span class="crest" style="background:<?= View::e($r['color_primary'] ?? '#7a1c2a') ?>"><?= View::e($r['short_name'] ?? '') ?></span>
          <span><span class="tn"><?= View::e($r['team_name']) ?></span><br><span class="tc"><?= View::e($r['city'] ?? '') ?><?= !empty($r['coach']) ? ' · ' . View::e($r['coach']) : '' ?></span></span>
        </a></td>
        <td><?= (int) ($r['played'] ?? 0) ?></td><td><?= (int) ($r['won'] ?? 0) ?></td><td><?= (int) ($r['drawn'] ?? 0) ?></td><td><?= (int) ($r['lost'] ?? 0) ?></td>
        <td><?= (int) ($r['goals_for'] ?? 0) ?></td><td><?= (int) ($r['goals_against'] ?? 0) ?></td>
        <td><?= ((int) ($r['goal_diff'] ?? 0)) > 0 ? '+' : '' ?><?= (int) ($r['goal_diff'] ?? 0) ?></td>
        <td class="pts"><?= (int) ($r['points'] ?? 0) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php require __DIR__ . '/_foot.php'; ?>

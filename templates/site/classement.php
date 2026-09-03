<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
  <div class="page__head"><p class="page__kicker">Compétition</p><h1 class="page__title">Classement</h1><p class="page__sub">Calculé automatiquement après validation des feuilles de match</p></div>
  <div class="gcard" style="padding:0;overflow:hidden">
    <table class="gtable">
      <thead><tr><th>#</th><th>Équipe</th><th>J</th><th>G</th><th>N</th><th>P</th><th>Diff</th><th>Pts</th></tr></thead>
      <tbody>
      <?php if (($standings ?? []) === []): ?><tr><td colspan="8">Classement indisponible.</td></tr><?php endif; ?>
      <?php foreach (($standings ?? []) as $i => $r): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td style="font-weight:600"><a href="/equipes/<?= (int) $r['team_id'] ?>" style="color:inherit;text-decoration:none"><?= View::e($r['team_name']) ?></a></td>
          <td><?= (int) ($r['played'] ?? 0) ?></td><td><?= (int) ($r['won'] ?? 0) ?></td>
          <td><?= (int) ($r['drawn'] ?? 0) ?></td><td><?= (int) ($r['lost'] ?? 0) ?></td>
          <td><?= (int) ($r['goal_diff'] ?? 0) ?></td>
          <td style="font-weight:700;color:#e8720c"><?= (int) ($r['points'] ?? 0) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

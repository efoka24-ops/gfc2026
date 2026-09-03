<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
  <div class="page__head"><p class="page__kicker">Statistiques</p><h1 class="page__title">Meilleurs buteurs</h1><p class="page__sub">Issu des feuilles de match validées</p></div>
  <div class="gcard" style="padding:0;overflow:hidden">
    <table class="gtable">
      <thead><tr><th>#</th><th>Joueur</th><th>Équipe</th><th>Buts</th></tr></thead>
      <tbody>
      <?php if (($scorers ?? []) === []): ?><tr><td colspan="4">Aucun but enregistré pour le moment.</td></tr><?php endif; ?>
      <?php foreach (($scorers ?? []) as $i => $s): ?>
        <tr><td><?= $i + 1 ?></td><td style="font-weight:600"><?= View::e($s['player_name']) ?></td>
        <td><?= View::e($s['team_name']) ?></td>
        <td style="font-weight:700;color:#e8720c"><?= (int) $s['goals'] ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

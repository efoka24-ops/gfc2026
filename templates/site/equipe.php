<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
<?php if (empty($team)): ?>
  <div class="gcard">Équipe introuvable.</div>
<?php else: ?>
  <div class="page__head" style="display:flex;align-items:center;gap:16px">
    <span class="gbadge" style="width:60px;height:60px;background:<?= View::e($team['color_primary'] ?? '#7a1c2a') ?>">
      <?php if (!empty($team['logo_path'])): ?><img src="<?= View::e($team['logo_path']) ?>" alt=""><?php else: ?><?= View::e($team['short_name'] ?? '') ?><?php endif; ?>
    </span>
    <div><h1 class="page__title"><?= View::e($team['name']) ?></h1>
    <p class="page__sub"><?= View::e($team['city'] ?? '') ?><?= !empty($team['coach']) ? ' · Coach : ' . View::e($team['coach']) : '' ?></p></div>
  </div>
  <div class="gcard" style="padding:0;overflow:hidden">
    <table class="gtable">
      <thead><tr><th>#</th><th>Joueur</th><th>Poste</th></tr></thead>
      <tbody>
      <?php $pos = ['GB'=>'Gardien','DEF'=>'Défenseur','MIL'=>'Milieu','ATT'=>'Attaquant']; ?>
      <?php foreach (($squad ?? []) as $p): ?>
        <tr><td><?= $p['shirt_no'] !== null ? (int) $p['shirt_no'] : '—' ?></td>
        <td style="font-weight:600"><?= View::e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></td>
        <td><?= View::e($pos[$p['position'] ?? ''] ?? ($p['position'] ?? '')) ?></td></tr>
      <?php endforeach; ?>
      <?php if (($squad ?? []) === []): ?><tr><td colspan="3">Effectif non renseigné.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

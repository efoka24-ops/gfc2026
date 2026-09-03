<?php use Gfc\Core\View; require __DIR__ . '/_head.php';
$pos = ['GB'=>'Gardien','DEF'=>'Défenseur','MIL'=>'Milieu','ATT'=>'Attaquant']; ?>
<?php if (empty($team)): ?>
  <section class="pagehead"><h1>Équipe introuvable</h1></section>
<?php else: ?>
  <section class="pagehead" style="display:flex;align-items:center;gap:16px">
    <span class="teamcard__crest" style="width:60px;height:60px;font-size:16px;background:<?= View::e($team['color_primary'] ?? '#7a1c2a') ?>">
      <?php if (!empty($team['logo_path'])): ?><img src="<?= View::e($team['logo_path']) ?>" alt=""><?php else: ?><?= View::e($team['short_name'] ?? '') ?><?php endif; ?>
    </span>
    <div><h1 style="margin:0"><?= View::e($team['name']) ?></h1>
      <p class="pagehead__sub"><?= View::e($team['city'] ?? '') ?><?= !empty($team['coach']) ? ' · Coach : ' . View::e($team['coach']) : '' ?></p></div>
  </section>
  <table class="standtable">
    <thead><tr><th>#</th><th>Joueur</th><th>Poste</th></tr></thead>
    <tbody>
    <?php foreach (($squad ?? []) as $p): ?>
      <tr><td class="rk"><?= $p['shirt_no'] !== null ? (int) $p['shirt_no'] : '—' ?></td>
        <td class="teamcell" style="text-align:left"><span class="tn"><?= View::e(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></span></td>
        <td><?= View::e($pos[$p['position'] ?? ''] ?? ($p['position'] ?? '')) ?></td></tr>
    <?php endforeach; ?>
    <?php if (($squad ?? []) === []): ?><tr><td colspan="3">Effectif non renseigné.</td></tr><?php endif; ?>
    </tbody>
  </table>
<?php endif; ?>
<?php require __DIR__ . '/_foot.php'; ?>

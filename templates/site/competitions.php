<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
  <div class="page__head"><p class="page__kicker">Structure</p><h1 class="page__title">Compétitions</h1><p class="page__sub">Les épreuves de l'édition en cours</p></div>
  <div class="ggrid">
    <?php foreach (($competitions ?? []) as $c): $t = ['league'=>'Championnat','cup'=>'Coupe','supercup'=>'Super Coupe'][$c['type']] ?? $c['type']; ?>
      <div class="gitem">
        <span class="gbadge" style="background:<?= View::e($c['color'] ?? '#7a1c2a') ?>"><?= View::e(mb_substr($c['name'],0,2)) ?></span>
        <span><strong style="display:block"><?= View::e($c['name']) ?></strong>
        <small style="color:#7a5a60"><?= View::e($t) ?><?= !empty($c['format']) ? ' · ' . View::e($c['format']) : '' ?></small></span>
      </div>
    <?php endforeach; ?>
    <?php if (($competitions ?? []) === []): ?><div class="gcard">Aucune compétition.</div><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

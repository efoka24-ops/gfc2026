<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
  <div class="page__head"><p class="page__kicker">Acteurs</p><h1 class="page__title">Équipes</h1><p class="page__sub"><?= count($teams ?? []) ?> équipes engagées</p></div>
  <div class="ggrid">
    <?php foreach (($teams ?? []) as $t): ?>
      <a class="gitem" href="/equipes/<?= (int) $t['id'] ?>" style="text-decoration:none;color:inherit">
        <span class="gbadge" style="background:<?= View::e($t['color_primary'] ?? '#7a1c2a') ?>">
          <?php if (!empty($t['logo_path'])): ?><img src="<?= View::e($t['logo_path']) ?>" alt=""><?php else: ?><?= View::e($t['short_name']) ?><?php endif; ?>
        </span>
        <span style="min-width:0"><strong style="display:block"><?= View::e($t['name']) ?></strong>
        <small style="color:#7a5a60"><?= View::e($t['city']) ?> · <?= (int) ($t['squad_size'] ?? 0) ?> joueurs</small></span>
      </a>
    <?php endforeach; ?>
    <?php if (($teams ?? []) === []): ?><div class="gcard">Aucune équipe.</div><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

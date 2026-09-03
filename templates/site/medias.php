<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
  <div class="page__head"><p class="page__kicker">Communication</p><h1 class="page__title">Médias</h1><p class="page__sub">Photos et vidéos de la compétition</p></div>
  <div class="gmedia">
    <?php foreach (($media ?? []) as $md): ?>
      <figure>
        <?php if (($md['type'] ?? 'photo') === 'video'): ?>
          <a href="<?= View::e($md['path']) ?>" target="_blank" rel="noopener"><img src="/assets/img/gallery/gfc-1.jpg" alt=""></a>
        <?php else: ?>
          <img src="<?= View::e($md['path']) ?>" alt="<?= View::e($md['caption'] ?? '') ?>">
        <?php endif; ?>
        <figcaption><?= View::e($md['caption'] ?? '') ?></figcaption>
      </figure>
    <?php endforeach; ?>
    <?php if (($media ?? []) === []): ?><div class="gcard">Aucun média.</div><?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

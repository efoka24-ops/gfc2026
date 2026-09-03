<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; $tab = $tab ?? 'actualites'; ?>
<div class="wrap" style="padding-top:6px">
  <nav class="subnav">
    <a href="/medias"<?= $tab === 'actualites' ? ' class="on"' : '' ?>>Actualités</a>
    <a href="/medias?tab=galerie"<?= $tab === 'galerie' ? ' class="on"' : '' ?>>Galerie</a>
    <a href="/medias?tab=palmares"<?= $tab === 'palmares' ? ' class="on"' : '' ?>>Palmarès</a>
  </nav>
</div>

<?php if ($tab === 'galerie'): ?>
  <section class="pagehead"><p class="pagehead__kicker">Communication</p><h1>Galerie</h1></section>
  <div class="mediagrid">
    <?php foreach (($media ?? []) as $md): ?>
      <figure>
        <?php if (($md['type'] ?? 'photo') === 'video'): ?>
          <a href="<?= View::e($md['path']) ?>" target="_blank" rel="noopener"><img src="/assets/img/gallery/gfc-1.jpg" alt=""></a>
        <?php else: ?><img src="<?= View::e($md['path']) ?>" alt="<?= View::e($md['caption'] ?? '') ?>"><?php endif; ?>
        <figcaption><?= View::e($md['caption'] ?? '') ?></figcaption>
      </figure>
    <?php endforeach; ?>
    <?php if (($media ?? []) === []): ?><div class="card card--pad">Aucun média.</div><?php endif; ?>
  </div>

<?php elseif ($tab === 'palmares'): ?>
  <section class="pagehead"><p class="pagehead__kicker">Histoire</p><h1>Palmarès</h1></section>
  <table class="standtable">
    <thead><tr><th>Édition</th><th style="text-align:left">Compétition</th><th style="text-align:left">Vainqueur</th></tr></thead>
    <tbody>
    <?php foreach (($honours ?? []) as $h): ?>
      <tr><td><?= View::e((string) ($h['year'] ?? $h['edition'] ?? '')) ?></td>
      <td style="text-align:left"><?= View::e((string) ($h['competition'] ?? $h['title'] ?? '')) ?></td>
      <td style="text-align:left"><?= View::e((string) ($h['winner'] ?? $h['team'] ?? $h['champion'] ?? '')) ?></td></tr>
    <?php endforeach; ?>
    <?php if (($honours ?? []) === []): ?><tr><td colspan="3">Palmarès à venir.</td></tr><?php endif; ?>
    </tbody>
  </table>

<?php else: ?>
  <section class="pagehead"><p class="pagehead__kicker">Communication</p><h1>Actualités</h1></section>
  <div class="cards">
    <?php foreach (($news ?? []) as $n): ?>
      <article class="newscard">
        <div class="newscard__cover">
          <?php if (!empty($n['cover_path'])): ?><img src="<?= View::e($n['cover_path']) ?>" alt="" loading="lazy">
          <?php else: ?><img src="/assets/img/logo.png" alt="" class="newscard__fallback"><?php endif; ?>
        </div>
        <div class="newscard__body">
          <p class="newscard__meta"><?= View::e($n['category']) ?><?= !empty($n['published_at']) ? ' · ' . View::date($n['published_at'], 'j M') : '' ?></p>
          <h3><?= View::e($n['title']) ?></h3>
          <p><?= View::e((string) ($n['excerpt'] ?? '')) ?></p>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if (($news ?? []) === []): ?><div class="card card--pad">Aucune actualité publiée.</div><?php endif; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/_foot.php'; ?>

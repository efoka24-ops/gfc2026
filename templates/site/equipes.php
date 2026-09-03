<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="pagehead"><p class="pagehead__kicker">Acteurs</p><h1>Les dix équipes</h1>
  <p class="pagehead__sub"><?= count($teams ?? []) ?> équipes engagées dans l'édition</p></section>
<div class="teamgrid">
  <?php foreach (($teams ?? []) as $t): $c = $t['color_primary'] ?? '#7a1c2a'; ?>
    <div class="teamcard">
      <div class="teamcard__bar" style="background:linear-gradient(90deg,<?= View::e($c) ?>,var(--orange))"></div>
      <div class="teamcard__body">
        <div class="teamcard__top">
          <span class="teamcard__crest" style="background:<?= View::e($c) ?>">
            <?php if (!empty($t['logo_path'])): ?><img src="<?= View::e($t['logo_path']) ?>" alt=""><?php else: ?><?= View::e($t['short_name'] ?? '') ?><?php endif; ?>
          </span>
          <div><div class="teamcard__name"><?= View::e($t['team_name']) ?></div>
            <div class="teamcard__sub"><?= View::e($t['city'] ?? '') ?><?= !empty($t['coach']) ? ' · ' . View::e($t['coach']) : '' ?></div></div>
        </div>
        <div class="teamcard__stats">
          <div class="teamcard__stat"><b><?= (int) ($t['points'] ?? 0) ?></b><span>Points</span></div>
          <div class="teamcard__stat bord"><b><?= (int) $t['rank'] ?></b><span>Rang</span></div>
          <div class="teamcard__stat"><b><?= ((int) ($t['goal_diff'] ?? 0)) > 0 ? '+' : '' ?><?= (int) ($t['goal_diff'] ?? 0) ?></b><span>Diff.</span></div>
          <a class="teamcard__more" href="/equipes/<?= (int) $t['team_id'] ?>">Voir la fiche →</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>

<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<?php if (empty($match)): ?>
  <section class="pagehead"><h1>Match introuvable</h1></section>
<?php else: $st = $match['status']; $played = in_array($st, ['live','halftime','finished'], true); ?>
  <section class="pagehead">
    <p class="pagehead__kicker"><?= View::e($match['competition']) ?><?= !empty($match['matchday']) ? ' · Journée ' . (int) $match['matchday'] : '' ?></p>
    <h1><?= View::e($match['home']) ?> <?= $played ? (int) $match['home_score'] . ' – ' . (int) $match['away_score'] : 'vs' ?> <?= View::e($match['away']) ?></h1>
    <p class="pagehead__sub"><?= View::date($match['kickoff_at'], 'l j F Y · H:i') ?><?= !empty($match['venue']) ? ' · ' . View::e($match['venue']) : '' ?><?= !empty($match['referee']) ? ' · Arbitre : ' . View::e($match['referee']) : '' ?></p>
  </section>
  <div class="card card--pad">
    <p class="card__title">Feuille de match</p>
    <?php if (($events ?? []) === []): ?><p style="color:var(--muted)">Aucun événement pour le moment.</p><?php endif; ?>
    <?php foreach (($events ?? []) as $e): ?>
      <div class="matchrow" style="grid-template-columns:44px 1fr">
        <span class="matchrow__date" style="font-family:var(--hero);color:var(--bord);font-size:16px"><?= (int) $e['minute'] ?>'</span>
        <span><?= View::e($e['type'] ?? '') ?><?= !empty($e['player_name']) ? ' — ' . View::e($e['player_name']) : '' ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/_foot.php'; ?>

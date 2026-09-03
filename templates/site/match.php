<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
<?php if (empty($match)): ?>
  <div class="gcard">Match introuvable.</div>
<?php else: $st = $match['status']; $played = in_array($st, ['live','halftime','finished'], true); ?>
  <div class="page__head"><p class="page__kicker"><?= View::e($match['competition']) ?><?= !empty($match['matchday']) ? ' · J' . (int) $match['matchday'] : '' ?></p>
    <h1 class="page__title"><?= View::e($match['home']) ?> <?= $played ? (int) $match['home_score'] . ' – ' . (int) $match['away_score'] : 'vs' ?> <?= View::e($match['away']) ?></h1>
    <p class="page__sub"><?= View::date($match['kickoff_at'], 'l d F Y · H:i') ?><?= !empty($match['venue']) ? ' · ' . View::e($match['venue']) : '' ?><?= !empty($match['referee']) ? ' · Arbitre : ' . View::e($match['referee']) : '' ?></p>
  </div>
  <div class="gcard">
    <h2 class="page__title" style="font-size:18px;margin-bottom:10px">Feuille de match</h2>
    <?php if (($events ?? []) === []): ?><p style="color:#7a5a60">Aucun événement pour le moment.</p><?php endif; ?>
    <?php foreach (($events ?? []) as $e): ?>
      <div style="display:flex;gap:12px;padding:8px 0;border-top:1px solid rgba(122,28,42,.08)">
        <span style="font-family:'Oswald',sans-serif;font-weight:700;color:#7a1c2a;width:44px"><?= (int) $e['minute'] ?>'</span>
        <span style="flex:1"><?= View::e($e['type'] ?? '') ?><?= !empty($e['player_name']) ? ' — ' . View::e($e['player_name']) : '' ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="pagehead">
  <p class="pagehead__kicker">Compétition</p>
  <h1>Calendrier &amp; résultats</h1>
  <p class="pagehead__sub"><?= count($fixtures ?? []) ?> rencontres · toutes compétitions</p>
</section>
<?php $f = $filter ?? 'tous'; foreach ([['tous','Tous'],['direct','En direct'],['avenir','À venir'],['resultats','Résultats']] as [$k,$l]): ?>
<?php endforeach; ?>
<div class="filters">
  <?php foreach ([['tous','Tous'],['direct','En direct'],['avenir','À venir'],['resultats','Résultats']] as [$k,$l]): ?>
    <a href="/matchs<?= $k === 'tous' ? '' : '?f=' . $k ?>"<?= $f === $k ? ' class="on"' : '' ?>><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div class="fixtures">
  <?php if (($fixtures ?? []) === []): ?><div class="card card--pad">Aucun match pour ce filtre.</div><?php endif; ?>
  <?php foreach (($fixtures ?? []) as $m):
      $st = $m['status']; $played = in_array($st, ['live','halftime','finished'], true);
      [$cls, $lbl] = $st === 'finished' ? ['st--fin','Terminé']
          : (in_array($st, ['live','halftime'], true) ? ['st--live','Live ' . (int) $m['minute'] . "'"] : ['st--prog','Programmé']);
  ?>
    <a class="fixture<?= in_array($st, ['live','halftime'], true) ? ' fixture--live' : '' ?>" href="/matchs/<?= (int) $m['id'] ?>" data-match-id="<?= (int) $m['id'] ?>">
      <span class="fixture__meta"><strong><?= View::date($m['kickoff_at'], 'D j M · H:i') ?></strong><?= View::e($m['competition']) ?></span>
      <span class="fixture__home"><?= View::e($m['home']) ?></span>
      <span class="fixture__score<?= $played ? '' : ' vs' ?>" <?= $played ? 'data-score' : '' ?>><?= $played ? (int) $m['home_score'] . ' – ' . (int) $m['away_score'] : 'VS' ?></span>
      <span class="fixture__away"><?= View::e($m['away']) ?></span>
      <span class="fixture__venue"><?= View::e((string) ($m['venue'] ?? '')) ?></span>
      <span class="st <?= $cls ?>"><?= View::e($lbl) ?></span>
    </a>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>

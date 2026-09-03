<?php use Gfc\Core\View; require __DIR__ . '/_head.php';
$pos = ['GB'=>'Gardien','DEF'=>'Défenseur','MIL'=>'Milieu','ATT'=>'Attaquant'];
$ini = fn($n) => strtoupper(mb_substr(strtok($n,' '),0,1) . mb_substr(strstr($n,' ') ?: ' ',1,1)); ?>
<section class="pagehead"><p class="pagehead__kicker">Statistiques</p><h1>Joueurs &amp; buteurs</h1>
  <p class="pagehead__sub">Statistiques calculées à partir des feuilles de match validées</p></section>
<div class="scoretable">
  <div class="scorehead"><span>#</span><span>Joueur</span><span>Buts</span><span>Passes</span></div>
  <?php if (($scorers ?? []) === []): ?><div class="scorerow"><span></span><span class="scorerow__meta">Aucun but enregistré pour le moment.</span><span></span><span></span></div><?php endif; ?>
  <?php foreach (($scorers ?? []) as $i => $s): ?>
    <div class="scorerow">
      <span class="scorerow__rk"><?= $i + 1 ?></span>
      <span class="scorerow__who"><span class="scorerow__av"><?= View::e($ini($s['player_name'])) ?></span>
        <span><span class="scorerow__nm"><?= View::e($s['player_name']) ?></span><br>
        <span class="scorerow__meta"><?= View::e($s['team_name']) ?><?= !empty($s['position']) ? ' · ' . View::e($pos[$s['position']] ?? $s['position']) : '' ?></span></span></span>
      <span class="scorerow__g"><?= (int) $s['goals'] ?></span>
      <span class="scorerow__a"><?= (int) ($s['subs'] ?? 0) ?></span>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/_foot.php'; ?>

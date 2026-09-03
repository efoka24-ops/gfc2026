<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="page">
  <div class="page__head"><p class="page__kicker">Compétition</p><h1 class="page__title">Matchs</h1><p class="page__sub">Calendrier, rencontres en direct et résultats</p></div>
  <?php
  $block = function(string $title, array $list) {
      if ($list === []) return;
      echo '<h2 class="page__title" style="font-size:20px;margin:18px 0 10px">' . View::e($title) . '</h2>';
      foreach ($list as $m) {
          $st = $m['status'];
          $pill = $st === 'live' ? 'live' : ($st === 'finished' ? 'fin' : 'prog');
          $lbl  = $st === 'live' ? ((int) $m['minute']) . "'" : ($st === 'finished' ? 'Terminé' : View::date($m['kickoff_at'], 'd/m H:i'));
          $sc   = in_array($st, ['live','halftime','finished'], true) ? ((int) $m['home_score']) . ' – ' . ((int) $m['away_score']) : 'vs';
          echo '<a class="gcard" style="display:flex;align-items:center;gap:14px;text-decoration:none;color:inherit" href="/matchs/' . (int) $m['id'] . '" data-match-id="' . (int) $m['id'] . '">';
          echo '<span style="flex:1;text-align:right;font-weight:600">' . View::e($m['home']) . '</span>';
          echo '<span data-score style="font-family:\'Oswald\',sans-serif;font-weight:700;font-size:18px;color:#7a1c2a;min-width:70px;text-align:center">' . View::e($sc) . '</span>';
          echo '<span style="flex:1;font-weight:600">' . View::e($m['away']) . '</span>';
          echo '<span class="gpill gpill--' . $pill . '">' . View::e($lbl) . '</span></a>';
      }
  };
  $block('En direct', $liveAll ?? []);
  $block('À venir', $upcomingAll ?? []);
  $block('Résultats', $resultsAll ?? []);
  if (($liveAll ?? []) === [] && ($upcomingAll ?? []) === [] && ($resultsAll ?? []) === []) echo '<div class="gcard">Aucun match programmé pour le moment.</div>';
  ?>
</section>
<?php require __DIR__ . '/_foot.php'; ?>

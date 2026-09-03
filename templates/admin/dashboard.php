<?php
use Gfc\Core\View;

$title    = 'Tableau de bord';
$kicker   = 'Pilotage';
$subtitle = 'Édition 2026 · journée en cours';
$action   = 'Nouveau match';

$maxChart = max(1, ...array_map(static fn ($r) => (int) $r['n'], $ticketChart ?: [['n' => 1]]));

ob_start();
?>
<section class="kpis">
  <?php foreach ($kpis as $kpi): ?>
    <div class="kpi">
      <p class="kpi__label"><?= View::e($kpi['l']) ?></p>
      <p class="kpi__value"><?= number_format((int) $kpi['v'], 0, ',', ' ') ?></p>
    </div>
  <?php endforeach; ?>
</section>

<section class="grid grid--2">
  <div class="panel panel--live">
    <?php if ($live === []): ?>
      <p class="panel__title">Aucune rencontre en direct</p>
      <p class="panel__note">La saisie s'ouvre automatiquement au coup d'envoi du prochain match.</p>
    <?php else: $m = $live[0]; ?>
      <div class="live__head">
        <span class="badge badge--live"><span class="dot"></span>Live</span>
        <span class="live__meta"><?= View::e($m['competition']) ?> · <?= (int) $m['minute'] ?>' · <?= View::e((string) $m['venue']) ?></span>
      </div>
      <div class="live__score">
        <span class="live__team live__team--right"><?= View::e($m['home']) ?></span>
        <span class="live__digits"><?= (int) $m['home_score'] ?> – <?= (int) $m['away_score'] ?></span>
        <span class="live__team"><?= View::e($m['away']) ?></span>
      </div>
      <a class="btn btn--primary btn--block" href="/admin/live?match=<?= (int) $m['id'] ?>">Ouvrir la saisie en direct</a>
    <?php endif; ?>
  </div>

  <div class="card card--pad">
    <p class="card__title">À valider</p>
    <?php
    $todoRows = [];
    foreach ($todos['dossiers'] as $t)  { $todoRows[] = ['Dossier ' . $t['name'], 'Statut : ' . $t['status'], '/admin/teams', 'Ouvrir']; }
    foreach ($todos['noReferee'] as $t) { $todoRows[] = ['Arbitre à assigner', $t['home'] . ' – ' . $t['away'] . ' · ' . View::date($t['kickoff_at']), '/admin/calendar', 'Assigner']; }
    foreach ($todos['sheets'] as $t)    { $todoRows[] = ['Feuille non validée', $t['home'] . ' – ' . $t['away'], '/admin/live?match=' . $t['id'], 'Valider']; }
    foreach ($todos['drafts'] as $t)    { $todoRows[] = ['Article en brouillon', $t['title'], '/admin/news', 'Relire']; }
    ?>
    <?php if ($todoRows === []): ?>
      <p class="panel__note">Rien à valider. Tout est à jour.</p>
    <?php endif; ?>
    <?php foreach (array_slice($todoRows, 0, 6) as [$label, $meta, $href, $cta]): ?>
      <div class="todo">
        <span class="todo__dot"></span>
        <div class="todo__body">
          <p class="todo__title"><?= View::e($label) ?></p>
          <p class="todo__meta"><?= View::e($meta) ?></p>
        </div>
        <a class="btn btn--mini" href="<?= View::e($href) ?>"><?= View::e($cta) ?></a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="grid grid--2">
  <div class="card card--pad">
    <p class="card__title">Billetterie — 7 derniers jours</p>
    <div class="chart">
      <?php foreach ($ticketChart as $bar): ?>
        <div class="chart__col">
          <span class="chart__bar" style="height:<?= (int) round(((int) $bar['n'] / $maxChart) * 100) ?>%"></span>
          <span class="chart__label"><?= View::date($bar['d'], 'D') ?></span>
        </div>
      <?php endforeach; ?>
      <?php if ($ticketChart === []): ?><p class="panel__note">Aucune vente enregistrée cette semaine.</p><?php endif; ?>
    </div>
  </div>

  <div class="card card--pad">
    <p class="card__title">Journal d'activité</p>
    <?php foreach ($activity as $a): ?>
      <div class="log">
        <span class="log__time"><?= View::date($a['created_at'], 'H:i') ?></span>
        <p class="log__text"><span class="cell--strong"><?= View::e((string) ($a['who'] ?? 'Système')) ?></span> <?= View::e($a['action']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

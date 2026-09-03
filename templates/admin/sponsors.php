<?php
use Gfc\Core\View;

$title    = 'Sponsors';
$kicker   = 'Communication';
$subtitle = "Partenaires et emplacements dans l'application";
$action   = 'Ajouter un partenaire';

ob_start();
?>
<div class="sponsors">
  <?php foreach ($sponsors as $s): ?>
    <div class="sponsor">
      <div class="sponsor__logo">
        <?php if (!empty($s['logo_path'])): ?>
          <img src="<?= View::e($s['logo_path']) ?>" alt="<?= View::e($s['name']) ?>" />
        <?php else: ?>
          <span>Logo · <?= View::e($s['name']) ?></span>
        <?php endif; ?>
      </div>
      <p class="sponsor__name"><?= View::e($s['name']) ?></p>
      <p class="sponsor__meta"><?= View::e($s['tier']) ?><?= $s['placement'] ? ' · ' . View::e($s['placement']) : '' ?></p>
      <span class="pill pill--<?= $s['status'] === 'active' ? 'ok' : ($s['status'] === 'expiring' ? 'wait' : 'neutral') ?>">
        <?= match ($s['status']) { 'active' => 'Actif', 'expiring' => 'À renouveler', default => 'Inactif' } ?>
      </span>
    </div>
  <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

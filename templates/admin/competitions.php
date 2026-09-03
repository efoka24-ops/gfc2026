<?php
use Gfc\Core\View;

$title    = 'Compétitions & phases';
$kicker   = 'Structure';
$subtitle = "Structure de l'édition courante";
$action   = 'Nouvelle phase';

ob_start();
?>
<div class="comps">
  <?php foreach ($competitions as $c): ?>
    <div class="comp">
      <div class="comp__bar" style="background:<?= View::e($c['color']) ?>"></div>
      <div class="card--pad">
        <p class="comp__name"><?= View::e($c['name']) ?></p>
        <p class="comp__format"><?= View::e((string) $c['format']) ?></p>
        <?php foreach ($c['phases'] as $p): ?>
          <div class="phase">
            <span><?= View::e($p['name']) ?></span>
            <span class="pill pill--<?= $p['status'] === 'done' ? 'ok' : ($p['status'] === 'running' ? 'wait' : 'neutral') ?>">
              <?= match ($p['status']) { 'done' => 'Terminé', 'running' => 'En cours', default => 'Programmé' } ?>
            </span>
          </div>
        <?php endforeach; ?>
        <?php if ($c['phases'] === []): ?><p class="panel__note">Aucune phase définie.</p><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

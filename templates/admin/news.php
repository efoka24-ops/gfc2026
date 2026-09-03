<?php
use Gfc\Core\View;

$title    = 'Actualités & médias';
$kicker   = 'Communication';
$subtitle = "Articles publiés sur l'application web";
$action   = 'Nouvel article';

ob_start();
?>
<?php
$cols = [
    ['label' => 'Titre'], ['label' => 'Rubrique', 'w' => '140px'], ['label' => 'Auteur', 'w' => '150px'],
    ['label' => 'Date', 'w' => '150px'], ['label' => 'Statut', 'w' => '120px'],
];

$rows = array_map(static fn (array $n): array => [
    ['v' => $n['title'], 'strong' => true],
    ['v' => $n['category'], 'pill' => 'neutral'],
    ['v' => $n['author'] ?? '—'],
    ['v' => $n['published_at'] === null ? '—' : View::date($n['published_at'], 'j M Y')],
    ['v' => match ($n['status']) { 'published' => 'Publié', 'scheduled' => 'Programmé', default => 'Brouillon' },
     'pill' => match ($n['status']) { 'published' => 'ok', 'scheduled' => 'wait', default => 'neutral' }],
], $news);

$count = count($news) . ' articles';

require __DIR__ . '/_table.php';
?>

<p class="section__title">Médiathèque</p>
<div class="media">
  <?php foreach ($media as $m): ?>
    <figure class="media__item">
      <img src="<?= View::e($m['path']) ?>" alt="<?= View::e((string) $m['caption']) ?>" loading="lazy" />
      <figcaption><?= View::e((string) $m['caption']) ?></figcaption>
    </figure>
  <?php endforeach; ?>
  <button class="media__add">+ Importer</button>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

<?php
/** @var array $user @var string $module @var string $content */
use Gfc\Core\View;

$nav = [
    'Pilotage' => [
        ['dash',  'Tableau de bord',      '/admin',              ['admin','editor']],
        ['live',  'Saisie en direct',     '/admin/live',         ['admin','delegate','referee']],
    ],
    'Compétition' => [
        ['comp',      'Compétitions & phases', '/admin/competitions', ['admin']],
        ['cal',       'Calendrier',            '/admin/calendar',     ['admin','delegate']],
        ['stand',     'Classements',           '/admin/standings',    ['admin','delegate']],
        ['sanctions', 'Sanctions',             '/admin/sanctions',    ['admin','referee']],
    ],
    'Acteurs' => [
        ['teams',   'Équipes',              '/admin/teams',   ['admin','delegate']],
        ['players', 'Joueurs & stats',      '/admin/players', ['admin','delegate']],
        ['users',   'Utilisateurs & rôles', '/admin/users',   ['admin']],
    ],
    'Communication' => [
        ['news',     'Actualités & médias', '/admin/news',     ['admin','editor']],
        ['tickets',  'Billetterie',         '/admin/tickets',  ['admin']],
        ['sponsors', 'Sponsors',            '/admin/sponsors', ['admin']],
    ],
];

$roleLabels = ['admin' => 'Administrateur', 'delegate' => 'Délégué équipe', 'referee' => 'Arbitre', 'editor' => 'Éditeur'];
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= View::e($title ?? 'Back office') ?> · GFC Admin</title>
<link rel="icon" href="/assets/img/logo.png" />
<link rel="stylesheet" href="/assets/css/admin.css" />
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="sidebar__brand">
      <img src="/assets/img/logo.png" alt="GFC" />
      <div>
        <p class="brand__title">GFC ADMIN</p>
        <p class="brand__sub">Édition 2026</p>
      </div>
    </div>
    <nav class="sidebar__nav">
      <?php foreach ($nav as $group => $items): ?>
        <?php $visible = array_filter($items, fn ($i) => in_array($user['role'], $i[3], true)); ?>
        <?php if ($visible === []) continue; ?>
        <p class="nav__group"><?= View::e($group) ?></p>
        <?php foreach ($visible as [$key, $label, $href, $roles]): ?>
          <a class="nav__item<?= $module === $key ? ' is-active' : '' ?>" href="<?= $href ?>">
            <span class="nav__dot"></span><?= View::e($label) ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar__foot">
      <p class="nav__group">Connecté en tant que</p>
      <p class="user__name"><?= View::e($user['name']) ?></p>
      <p class="user__role"><?= View::e($roleLabels[$user['role']] ?? $user['role']) ?></p>
      <form method="post" action="/admin/logout"><button class="btn btn--ghost btn--block">Se déconnecter</button></form>
    </div>
  </aside>

  <div class="main">
    <header class="modbar">
      <div>
        <p class="modbar__kicker"><?= View::e($kicker ?? '') ?></p>
        <h1 class="modbar__title"><?= View::e($title ?? '') ?></h1>
        <p class="modbar__sub"><?= View::e($subtitle ?? '') ?></p>
      </div>
      <div class="modbar__actions">
        <?php if (!empty($exportUrl)): ?><a class="btn btn--light" href="<?= View::e($exportUrl) ?>">Exporter CSV</a><?php endif; ?>
        <?php if (!empty($action)): ?><button class="btn btn--primary"><?= View::e($action) ?></button><?php endif; ?>
      </div>
    </header>
    <main class="content"><?= $content ?></main>
  </div>
</div>
<script src="/assets/js/admin.js" defer></script>
</body>
</html>

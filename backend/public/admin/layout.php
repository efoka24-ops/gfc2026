<?php
/** @var array $user  @var string $title  @var string $active */
/**
 * Navigation filtrée par rôle.
 *
 * Chaque page vérifie déjà ses propres droits ; ce filtre existe pour que la
 * barre ne propose pas des liens qui finiront en « accès refusé ». Un arbitre
 * ne doit voir que ce qu'il peut faire : la saisie live.
 */
$navComplete = [
    'index.php'        => ['Tableau de bord',      ['admin', 'secretaire']],
    'competitions.php' => ['Compétitions',         ['admin', 'secretaire']],
    'teams.php'        => ['Équipes',              ['admin', 'secretaire']],
    'players.php'      => ['Joueurs',              ['admin', 'secretaire']],
    'matches.php'      => ['Matchs & calendrier',  ['admin', 'secretaire']],
    'live.php'         => ['Saisie live',          ['admin', 'secretaire', 'arbitre']],
    'news.php'         => ['Actualités',           ['admin', 'secretaire']],
    'media.php'        => ['Photos & vidéos',      ['admin', 'secretaire']],
    'users.php'        => ['Utilisateurs & rôles', ['admin']],
];

$nav = [];
foreach ($navComplete as $href => [$label, $roles]) {
    if (in_array($user['role'], $roles, true)) {
        $nav[$href] = $label;
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · GFC Admin</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<aside class="sidebar">
  <div class="brand"><img src="assets/logo.png" alt=""><span>GFC Admin</span></div>
  <nav>
    <?php foreach ($nav as $href => $label): ?>
      <a href="<?= $href ?>" class="<?= $active === $href ? 'on' : '' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-foot">
    <div><?= e($user['name']) ?></div>
    <div class="role"><?= e($user['role']) ?></div>
    <a class="logout" href="logout.php">Se déconnecter</a>
  </div>
</aside>
<main class="main">
  <header class="topbar">
    <h1><?= e($title) ?></h1>
    <?php if (!empty($subtitle)): ?><span class="sub"><?= e($subtitle) ?></span><?php endif; ?>
  </header>
  <div class="content"><?= $content ?></div>
</main>
</body>
</html>

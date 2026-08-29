<?php
/** @var array $user  @var string $title  @var string $active */
$nav = [
    'index.php'        => 'Tableau de bord',
    'competitions.php' => 'Compétitions',
    'teams.php'        => 'Équipes',
    'players.php'      => 'Joueurs',
    'matches.php'      => 'Matchs & calendrier',
    'live.php'         => 'Saisie live',
    'news.php'         => 'Actualités',
    'media.php'        => 'Photos & vidéos',
    'users.php'        => 'Utilisateurs & rôles',
];
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

<?php
/** Application web publique — rendu serveur de l'entrée, puis navigation JS sur l'API. */
use Gfc\Core\View;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Garoua Football Challenge · <?= View::e($edition['label'] ?? '') ?></title>
<meta name="description" content="Championnat de vacances de Garoua : calendrier, résultats en direct, classement, équipes et buteurs du Garoua Football Challenge." />
<link rel="icon" href="/assets/img/logo.png" />
<link rel="stylesheet" href="/assets/css/site.css" />
</head>
<body>

<div class="ticker">
  <div class="wrap ticker__inner">
    <span class="ticker__tag"><span class="dot"></span>En ce moment</span>
    <div class="ticker__list">
      <?php foreach (array_merge($live, $upcoming, $results) as $m): ?>
        <a class="ticker__item" href="/matchs/<?= (int) $m['id'] ?>">
          <span class="ticker__teams"><?= View::e($m['home_short']) ?> – <?= View::e($m['away_short']) ?></span>
          <span class="ticker__score<?= $m['status'] === 'live' ? ' is-live' : '' ?>">
            <?= in_array($m['status'], ['live','halftime','finished'], true)
                ? (int) $m['home_score'] . '-' . (int) $m['away_score']
                : View::date($m['kickoff_at'], 'H:i') ?>
          </span>
          <span class="ticker__state"><?= $m['status'] === 'live' ? (int) $m['minute'] . "'" : ($m['status'] === 'finished' ? 'FT' : View::date($m['kickoff_at'], 'D')) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <a class="ticker__link" href="/mon-espace">Mon espace</a>
  </div>
</div>

<header class="head">
  <div class="wrap head__inner">
    <a class="brand" href="/">
      <img src="/assets/img/logo.png" alt="Garoua Football Challenge" />
      <span>
        <strong>Garoua Football Challenge</strong>
        <em><?= View::e($edition['label'] ?? '') ?> · since 2020</em>
      </span>
    </a>
    <nav class="head__nav">
      <?php foreach ([
        ['/', 'Accueil'], ['/matchs', 'Matchs'], ['/classement', 'Classement'],
        ['/equipes', 'Équipes'], ['/buteurs', 'Buteurs'],
        ['/competitions', 'Compétitions'], ['/medias', 'Médias'],
      ] as [$href, $label]): ?>
        <a href="<?= $href ?>"<?= $path === $href ? ' class="is-active"' : '' ?>><?= View::e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <a class="btn btn--primary" href="/inscription">Inscrire une équipe</a>
  </div>
</header>

<main class="wrap" id="app" data-path="<?= View::e($path) ?>">

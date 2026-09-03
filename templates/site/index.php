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

  <section class="hero">
    <div class="hero__text">
      <p class="kicker">Championnat de vacances · Garoua</p>
      <h1>La <?= View::e($edition['label'] ?? '') ?><br />est lancée</h1>
      <p class="lede">Dix équipes, trois compétitions. Vulgariser les talents, mettre en avant le professionnalisme et permettre aux jeunes footballeurs d'évoluer dans un milieu professionnel.</p>
      <div class="hero__actions">
        <a class="btn btn--primary" href="/matchs">Calendrier &amp; résultats</a>
        <a class="btn btn--ghost" href="/competitions">Grand Prix Mbaïrobé</a>
      </div>
    </div>

    <?php if ($live !== []): $m = $live[0]; ?>
      <aside class="livecard">
        <div class="livecard__head">
          <span class="badge badge--live"><span class="dot"></span>Live</span>
          <span><?= View::e($m['competition']) ?> · <?= (int) $m['minute'] ?>'</span>
        </div>
        <div class="livecard__score">
          <span class="right"><?= View::e($m['home']) ?></span>
          <strong><?= (int) $m['home_score'] ?> – <?= (int) $m['away_score'] ?></strong>
          <span><?= View::e($m['away']) ?></span>
        </div>
        <a class="btn btn--primary btn--block" href="/matchs/<?= (int) $m['id'] ?>">Feuille de match</a>
      </aside>
    <?php elseif ($upcoming !== []): $m = $upcoming[0]; ?>
      <aside class="livecard">
        <div class="livecard__head"><span class="badge">Prochain match</span><span><?= View::e($m['competition']) ?></span></div>
        <div class="livecard__score">
          <span class="right"><?= View::e($m['home']) ?></span>
          <strong>vs</strong>
          <span><?= View::e($m['away']) ?></span>
        </div>
        <p class="livecard__meta"><?= View::date($m['kickoff_at']) ?> · <?= View::e((string) $m['venue']) ?></p>
      </aside>
    <?php endif; ?>
  </section>

  <div class="cols">
    <div class="cols__main">
      <h2 class="section__title">À la une</h2>
      <div class="cards">
        <?php foreach ($news as $n): ?>
          <article class="newscard">
            <div class="newscard__cover">
              <?php if (!empty($n['cover_path'])): ?>
                <img src="<?= View::e($n['cover_path']) ?>" alt="" loading="lazy" />
              <?php else: ?>
                <img src="/assets/img/logo.png" alt="" class="newscard__fallback" />
              <?php endif; ?>
            </div>
            <div class="newscard__body">
              <p class="newscard__meta"><?= View::e($n['category']) ?> · <?= View::date($n['published_at'], 'j M') ?></p>
              <h3><?= View::e($n['title']) ?></h3>
              <p><?= View::e((string) $n['excerpt']) ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <h2 class="section__title">Résultats récents</h2>
      <div class="card list">
        <?php foreach ($results as $m): ?>
          <a class="matchrow" href="/matchs/<?= (int) $m['id'] ?>">
            <span class="matchrow__date"><?= View::date($m['kickoff_at'], 'D j M') ?></span>
            <span class="matchrow__teams">
              <span class="right"><?= View::e($m['home']) ?></span>
              <strong><?= (int) $m['home_score'] ?> – <?= (int) $m['away_score'] ?></strong>
              <span><?= View::e($m['away']) ?></span>
            </span>
            <span class="matchrow__comp"><?= View::e($m['competition']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <aside class="cols__side">
      <div class="card">
        <p class="card__head--dark">Classement</p>
        <?php foreach (array_slice($standings, 0, 6) as $r): ?>
          <a class="standrow<?= $r['zone'] === 'qualify' ? ' is-qualify' : '' ?>" href="/equipes/<?= (int) $r['team_id'] ?>">
            <span class="standrow__rank"><?= (int) $r['rank'] ?></span>
            <span class="standrow__crest" style="background:<?= View::e($r['color_primary']) ?>"><?= View::e($r['short_name']) ?></span>
            <span class="standrow__name"><?= View::e($r['team_name']) ?></span>
            <span class="standrow__pts"><?= (int) $r['points'] ?></span>
          </a>
        <?php endforeach; ?>
        <a class="card__foot" href="/classement">Classement complet</a>
      </div>

      <div class="card">
        <p class="card__head">Prochains matchs</p>
        <?php foreach (array_slice($upcoming, 0, 4) as $m): ?>
          <a class="nextrow" href="/matchs/<?= (int) $m['id'] ?>">
            <span class="nextrow__meta"><?= View::date($m['kickoff_at']) ?> · <?= View::e($m['competition']) ?></span>
            <span class="nextrow__teams"><?= View::e($m['home']) ?><br /><?= View::e($m['away']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($sponsors !== []): ?>
        <div class="card card--pad">
          <p class="card__title">Partenaires</p>
          <div class="sponsorstrip">
            <?php foreach (array_slice($sponsors, 0, 6) as $s): ?>
              <span class="sponsorstrip__item"><?= View::e($s['name']) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</main>

<footer class="foot">
  <div class="wrap foot__inner">
    <div>
      <div class="brand brand--foot">
        <img src="/assets/img/logo.png" alt="" />
        <strong>Garoua Football<br />Challenge</strong>
      </div>
      <p class="foot__note">Championnat de vacances · Garoua, Cameroun</p>
    </div>
    <div>
      <p class="foot__title">Compétition</p>
      <a href="/classement">Classement</a><a href="/matchs">Calendrier</a><a href="/competitions">Grand Prix Mbaïrobé</a>
    </div>
    <div>
      <p class="foot__title">Le championnat</p>
      <a href="/equipes">Équipes</a><a href="/buteurs">Buteurs</a><a href="/palmares">Palmarès</a>
    </div>
    <div>
      <p class="foot__title">Espaces</p>
      <a href="/inscription">Inscrire une équipe</a><a href="/mon-espace">Mon espace</a><a href="/admin">Espace administration</a>
    </div>
  </div>
  <div class="foot__bar"><div class="wrap">© <?= date('Y') ?> Garoua Football Challenge</div></div>
</footer>

<script src="/assets/js/app.js" defer></script>
</body>
</html>

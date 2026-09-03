<?php use Gfc\Core\View; ?>

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

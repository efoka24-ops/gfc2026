<?php
use Gfc\Core\View;

$title    = 'Saisie en direct';
$kicker   = 'Feuille de match numérique';
$subtitle = $match === null ? 'Aucune rencontre sélectionnée' : $match['home'] . ' – ' . $match['away'];
$action   = $match === null ? '' : 'Clôturer le match';

$icons = ['goal' => '⚽', 'penalty' => '⚽', 'own_goal' => '⚽', 'yellow' => '🟨', 'red' => '🟥', 'sub' => '🔁', 'shot' => '🅾', 'period' => '⏱', 'miss' => '🅾', 'note' => '📝'];
$labels = ['goal' => 'But', 'penalty' => 'Penalty', 'own_goal' => 'CSC', 'yellow' => 'Carton jaune', 'red' => 'Carton rouge', 'sub' => 'Changement', 'shot' => 'Tir', 'period' => 'Période', 'miss' => 'Occasion', 'note' => 'Note'];

ob_start();
?>
<?php if ($match === null): ?>
  <div class="card card--pad"><p class="panel__note">Aucune rencontre à saisir. Sélectionnez un match dans le calendrier.</p></div>
<?php else: ?>
<section class="grid grid--2" data-match="<?= (int) $match['id'] ?>">
  <div class="card card--live">
    <div class="live__banner">
      <p class="live__meta"><?= View::e($match['competition']) ?> · <?= View::e((string) $match['venue']) ?></p>
      <div class="live__score">
        <span class="live__team live__team--right"><?= View::e($match['home']) ?></span>
        <span class="live__digits" data-score><?= (int) $match['home_score'] ?> – <?= (int) $match['away_score'] ?></span>
        <span class="live__team"><?= View::e($match['away']) ?></span>
      </div>
    </div>
    <div class="card--pad">
      <div class="chrono">
        <div>
          <p class="card__title">Chronomètre</p>
          <p class="chrono__value" data-chrono="<?= (int) $match['minute'] ?>"><?= (int) $match['minute'] ?>'</p>
        </div>
        <div class="chrono__actions">
          <button class="btn btn--dark" data-status="halftime">Mi-temps</button>
          <button class="btn btn--light" data-minute="+1">+1 min</button>
        </div>
      </div>

      <p class="card__title">Ajouter un événement</p>
      <div class="events">
        <?php foreach (['goal','yellow','red','sub','shot','period'] as $type): ?>
          <button class="event event--<?= $type ?>" data-event="<?= $type ?>">
            <span class="event__icon"><?= $icons[$type] ?></span>
            <span class="event__label"><?= View::e($labels[$type]) ?></span>
          </button>
        <?php endforeach; ?>
      </div>

      <label class="field">
        <span>Joueur concerné</span>
        <select class="input" data-player>
          <option value="">—</option>
          <optgroup label="<?= View::e($match['home']) ?>">
            <?php foreach ($squads['home'] as $p): ?>
              <option value="<?= (int) $p['id'] ?>" data-team="<?= (int) $match['home_id'] ?>"><?= View::e($p['first_name'] . ' ' . $p['last_name']) ?> · <?= View::e($p['position']) ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="<?= View::e($match['away']) ?>">
            <?php foreach ($squads['away'] as $p): ?>
              <option value="<?= (int) $p['id'] ?>" data-team="<?= (int) $match['away_id'] ?>"><?= View::e($p['first_name'] . ' ' . $p['last_name']) ?> · <?= View::e($p['position']) ?></option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </label>
      <button class="btn btn--primary btn--block" data-publish>Publier sur l'application</button>
    </div>
  </div>

  <div class="card card--pad">
    <div class="card__head">
      <p class="card__title">Feuille de match</p>
      <span class="pill pill--<?= $match['sheet_status'] === 'validated' ? 'ok' : 'wait' ?>">
        <?= $match['sheet_status'] === 'validated' ? 'Validée' : 'En cours' ?>
      </span>
    </div>
    <div data-timeline>
      <?php foreach ($match['events'] as $e): ?>
        <div class="tl" data-event-id="<?= (int) $e['id'] ?>">
          <span class="tl__min"><?= (int) $e['minute'] ?>'</span>
          <span class="tl__icon"><?= $icons[$e['type']] ?? '•' ?></span>
          <div class="tl__body">
            <p class="tl__player"><?= View::e((string) ($e['player'] ?? $labels[$e['type']] ?? '—')) ?></p>
            <p class="tl__detail"><?= View::e($labels[$e['type']] ?? $e['type']) ?><?= $e['team_name'] ? ' · ' . View::e($e['team_name']) : '' ?></p>
          </div>
          <button class="tl__del" data-delete="<?= (int) $e['id'] ?>">✕</button>
        </div>
      <?php endforeach; ?>
      <?php if ($match['events'] === []): ?><p class="panel__note">Aucun événement enregistré.</p><?php endif; ?>
    </div>
    <p class="panel__note">Chaque événement publié déclenche une notification vers les supporters qui suivent l'une des deux équipes.</p>
  </div>
</section>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

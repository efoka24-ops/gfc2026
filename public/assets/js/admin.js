/** Back office : saisie de la feuille de match en direct. */
(function () {
  'use strict';

  const root = document.querySelector('[data-match]');
  if (!root) return;

  const matchId  = root.dataset.match;
  const chrono   = root.querySelector('[data-chrono]');
  const scoreEl  = root.querySelector('[data-score]');
  const timeline = root.querySelector('[data-timeline]');
  const playerEl = root.querySelector('[data-player]');
  let pending    = null;

  const ICONS  = { goal: '⚽', penalty: '⚽', own_goal: '⚽', yellow: '🟨', red: '🟥', sub: '🔁', shot: '🅾', period: '⏱' };
  const LABELS = { goal: 'But', penalty: 'Penalty', own_goal: 'CSC', yellow: 'Carton jaune', red: 'Carton rouge', sub: 'Changement', shot: 'Tir', period: 'Période' };

  async function post(path, body) {
    const res = await fetch(path, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body || {}),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
  }

  root.querySelectorAll('[data-event]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      root.querySelectorAll('[data-event]').forEach(function (b) { b.classList.remove('is-selected'); });
      btn.classList.add('is-selected');
      pending = btn.dataset.event;
    });
  });

  const minuteBtn = root.querySelector('[data-minute]');
  if (minuteBtn) {
    minuteBtn.addEventListener('click', function () {
      const next = Number(chrono.dataset.chrono || 0) + 1;
      chrono.dataset.chrono = String(next);
      chrono.textContent = next + "'";
    });
  }

  root.querySelectorAll('[data-status]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post('/api/matches/' + matchId + '/status', {
        status: btn.dataset.status,
        minute: Number(chrono.dataset.chrono || 0),
      }).catch(function () { alert('Impossible de changer le statut.'); });
    });
  });

  const publish = root.querySelector('[data-publish]');
  if (publish) {
    publish.addEventListener('click', async function () {
      if (!pending) { alert('Choisissez d\'abord un type d\'événement.'); return; }

      const opt = playerEl.selectedOptions[0];
      publish.disabled = true;

      try {
        const data = await post('/api/matches/' + matchId + '/events', {
          type: pending,
          minute: Number(chrono.dataset.chrono || 0),
          player_id: playerEl.value ? Number(playerEl.value) : null,
          team_id: opt && opt.dataset.team ? Number(opt.dataset.team) : null,
        });

        if (scoreEl && data.match) {
          scoreEl.textContent = data.match.home_score + ' – ' + data.match.away_score;
        }
        appendEvent(pending, Number(chrono.dataset.chrono || 0), opt ? opt.textContent.split(' · ')[0] : '', data.id);
        pending = null;
        root.querySelectorAll('[data-event]').forEach(function (b) { b.classList.remove('is-selected'); });
      } catch (e) {
        alert('Enregistrement impossible. Vérifiez la connexion.');
      } finally {
        publish.disabled = false;
      }
    });
  }

  function appendEvent(type, minute, player, id) {
    const row = document.createElement('div');
    row.className = 'tl';
    row.dataset.eventId = id;
    row.innerHTML =
      '<span class="tl__min">' + minute + "'</span>" +
      '<span class="tl__icon">' + (ICONS[type] || '•') + '</span>' +
      '<div class="tl__body"><p class="tl__player">' + (player || LABELS[type] || type) + '</p>' +
      '<p class="tl__detail">' + (LABELS[type] || type) + '</p></div>' +
      '<button class="tl__del" data-delete="' + id + '">✕</button>';
    timeline.prepend(row);
    bindDelete(row.querySelector('[data-delete]'));
  }

  function bindDelete(btn) {
    if (!btn) return;
    btn.addEventListener('click', async function () {
      if (!confirm('Supprimer cet événement de la feuille de match ?')) return;
      await fetch('/api/matches/' + matchId + '/events/' + btn.dataset.delete, { method: 'DELETE' });
      btn.closest('.tl').remove();
    });
  }

  root.querySelectorAll('[data-delete]').forEach(bindDelete);

  /** Recherche dans les tableaux du back office. */
  document.querySelectorAll('[data-table-search]').forEach(function (input) {
    input.addEventListener('input', function () {
      const q = input.value.toLowerCase();
      input.closest('.card').querySelectorAll('tbody tr').forEach(function (tr) {
        tr.hidden = q !== '' && !tr.textContent.toLowerCase().includes(q);
      });
    });
  });
})();

/**
 * Application web publique : rafraîchissement des scores en direct
 * et navigation progressive sur l'API JSON.
 */
(function () {
  'use strict';

  const POLL_MS = 15000;

  async function api(path, options) {
    const res = await fetch('/api' + path, Object.assign({
      headers: { 'Accept': 'application/json' },
    }, options || {}));
    if (!res.ok) throw new Error('API ' + res.status);
    return res.json();
  }

  /** Met à jour les scores affichés sans recharger la page. */
  async function refreshLive() {
    try {
      const { matches } = await api('/matches?status=live');
      if (!matches.length) return;

      matches.forEach(function (m) {
        document.querySelectorAll('[data-match-id="' + m.id + '"]').forEach(function (node) {
          const score = node.querySelector('[data-score]');
          const min   = node.querySelector('[data-minute]');
          if (score) score.textContent = m.home_score + ' – ' + m.away_score;
          if (min) min.textContent = m.minute + "'";
        });
      });

      // Bandeau supérieur
      const ticker = document.querySelector('.ticker__list');
      if (ticker) {
        matches.forEach(function (m) {
          const item = ticker.querySelector('a[href="/matchs/' + m.id + '"] .ticker__score');
          if (item) {
            item.textContent = m.home_score + '-' + m.away_score;
            item.classList.add('is-live');
          }
        });
      }
    } catch (e) {
      /* hors ligne : on réessaiera au prochain cycle */
    }
  }

  if (document.querySelector('.ticker__list')) {
    setInterval(refreshLive, POLL_MS);
    refreshLive();
  }

  /** Espace supporter : connexion par code SMS. */
  const authForm = document.querySelector('[data-auth-form]');
  if (authForm) {
    authForm.addEventListener('submit', async function (ev) {
      ev.preventDefault();
      const phone = authForm.querySelector('[name="phone"]').value;
      const code  = authForm.querySelector('[name="code"]');
      const note  = authForm.querySelector('[data-auth-note]');

      try {
        if (!code || !code.value) {
          await api('/auth/otp', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone: phone }),
          });
          if (note) note.textContent = 'Code envoyé par SMS.';
          if (code) code.hidden = false;
          return;
        }

        const data = await api('/auth/verify', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone: phone, code: code.value }),
        });
        localStorage.setItem('gfc_token', data.token);
        location.href = '/mon-espace';
      } catch (e) {
        if (note) note.textContent = 'Numéro ou code incorrect.';
      }
    });
  }

  /** Suivre / ne plus suivre une équipe. */
  document.querySelectorAll('[data-follow]').forEach(function (btn) {
    btn.addEventListener('click', async function () {
      const token = localStorage.getItem('gfc_token');
      if (!token) { location.href = '/mon-espace'; return; }

      const data = await api('/me/favorites', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
        body: JSON.stringify({ team_id: Number(btn.dataset.follow) }),
      });
      btn.classList.toggle('is-following', data.following);
      btn.textContent = data.following ? 'Suivi' : 'Suivre';
    });
  });
})();

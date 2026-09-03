<?php use Gfc\Core\View; require __DIR__ . '/_head.php'; ?>
<section class="pagehead">
  <p class="pagehead__kicker">Rejoindre le GFC</p>
  <h1>Inscrire une équipe</h1>
  <p class="pagehead__sub">Le comité revient vers vous sous 72 heures après réception du dossier.</p>
</section>

<div class="card card--pad" style="max-width:560px">
  <div id="reg-ok" hidden style="padding:14px 16px;border-radius:10px;background:#e8f5e9;color:#1b5e20;margin-bottom:16px;font-size:14px"></div>
  <div id="reg-err" hidden style="padding:14px 16px;border-radius:10px;background:#fdecea;color:#b3261e;margin-bottom:16px;font-size:14px"></div>

  <form id="reg-form" style="display:grid;gap:14px">
    <label>Nom de l'équipe
      <input class="input" type="text" name="team_name" required>
    </label>
    <label>Ville
      <input class="input" type="text" name="city" required>
    </label>
    <label>Responsable du dossier
      <input class="input" type="text" name="manager_name" required>
    </label>
    <label>Téléphone
      <input class="input" type="text" name="phone" placeholder="+237 6XX XXX XXX" required>
    </label>
    <label>Entraîneur <span style="color:var(--muted);font-weight:400">(facultatif)</span>
      <input class="input" type="text" name="coach">
    </label>
    <label>Nombre de joueurs
      <input class="input" type="number" name="squad_size" min="11" max="40" value="18" required>
    </label>
    <label>Compétition visée
      <select class="input" name="target">
        <option value="Championnat">Championnat</option>
        <option value="Grand Prix">Grand Prix Mbaïrobé</option>
        <option value="Les deux">Les deux</option>
      </select>
    </label>
    <button class="btn btn--primary btn--block" type="submit">Envoyer le dossier</button>
  </form>
</div>

<script>
(function () {
  var form = document.getElementById('reg-form');
  var ok = document.getElementById('reg-ok');
  var err = document.getElementById('reg-err');
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    ok.hidden = true; err.hidden = true;
    var fd = new FormData(form);
    var data = {};
    fd.forEach(function (v, k) { data[k] = v; });
    var btn = form.querySelector('button');
    btn.disabled = true; btn.textContent = 'Envoi…';
    fetch('/api/registrations', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data),
    })
      .then(function (r) { return r.json().then(function (b) { return { ok: r.ok, body: b }; }); })
      .then(function (res) {
        if (res.ok) {
          ok.hidden = false;
          ok.textContent = res.body.message || 'Dossier reçu.';
          form.reset();
        } else {
          err.hidden = false;
          var fields = res.body.fields || {};
          var msgs = Object.keys(fields).map(function (k) { return fields[k]; });
          err.textContent = msgs.length ? msgs.join(' · ') : 'Une erreur est survenue.';
        }
      })
      .catch(function () {
        err.hidden = false;
        err.textContent = 'Connexion impossible. Réessayez.';
      })
      .finally(function () {
        btn.disabled = false; btn.textContent = 'Envoyer le dossier';
      });
  });
})();
</script>
<?php require __DIR__ . '/_foot.php'; ?>

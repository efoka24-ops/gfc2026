<?php
use Gfc\Core\View;
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Connexion · GFC Admin</title>
<link rel="stylesheet" href="/assets/css/admin.css" />
</head>
<body class="login">
  <form class="login__card" method="post" action="/admin/login">
    <img src="/assets/img/logo.png" alt="GFC" class="login__logo" />
    <p class="login__title">GFC Admin</p>
    <p class="login__sub">Comité d'organisation · édition 2026</p>
    <?php if (!empty($error)): ?><p class="login__error"><?= View::e($error) ?></p><?php endif; ?>
    <label class="field">
      <span>Téléphone</span>
      <input class="input" name="phone" type="tel" autocomplete="username" required placeholder="+237 6XX XXX XXX" />
    </label>
    <label class="field">
      <span>Mot de passe</span>
      <input class="input" name="password" type="password" autocomplete="current-password" required />
    </label>
    <button class="btn btn--primary btn--block">Se connecter</button>
  </form>
</body>
</html>

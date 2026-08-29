<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;

Auth::startSession();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $result = Auth::login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
    if ($result) {
        $_SESSION['gfc_user']  = $result['user'];
        $_SESSION['gfc_token'] = $result['token'];
        header('Location: ' . ($result['user']['role'] === Auth::ROLE_ARBITRE ? 'live.php' : 'index.php'));
        exit;
    }
    $error = 'Identifiants invalides.';
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion · GFC Admin</title>
<link rel="stylesheet" href="assets/admin.css">
<style>body{display:flex;background:#efe7dc}</style>
</head>
<body>
<form class="login" method="post">
  <img src="assets/logo.png" alt="Garoua Football Challenge">
  <h1 style="margin:0;text-align:center;font:700 17px/1.2 sans-serif;color:#5A1424;text-transform:uppercase">Back-office GFC</h1>
  <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
  <label>Adresse e-mail<input type="email" name="email" required autofocus></label>
  <label>Mot de passe<input type="password" name="password" required></label>
  <button class="btn" type="submit">Se connecter</button>
  <p class="hint">Accès réservé aux administrateurs, secrétaires et arbitres du Garoua Football Challenge.</p>
</form>
</body>
</html>

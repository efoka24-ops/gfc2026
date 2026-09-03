<?php
use Gfc\Core\View;

$title    = 'Mon compte';
$kicker   = 'Administration';
$subtitle = "Modifier les informations de votre compte";

ob_start();
?>
<?php if (!empty($notice)): ?><div class="pill pill--ok" style="display:inline-block;margin-bottom:16px"><?= View::e($notice) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="pill pill--bad" style="display:inline-block;margin-bottom:16px"><?= View::e($error) ?></div><?php endif; ?>

<form method="post" action="/admin/account" class="card" style="max-width:520px">
  <div class="field">
    <label>Nom affiché</label>
    <input type="text" name="name" value="<?= View::e($user['name']) ?>" required>
  </div>
  <div class="field">
    <label>Adresse e-mail</label>
    <input type="email" name="email" value="<?= View::e($user['email'] ?? '') ?>">
  </div>
  <div class="field">
    <label>Téléphone (identifiant de connexion)</label>
    <input type="text" value="<?= View::e($user['phone']) ?>" disabled>
  </div>
  <hr style="border:none;border-top:1px solid var(--border);margin:18px 0">
  <div class="field">
    <label>Nouveau mot de passe <span style="color:var(--muted-foreground)">(laisser vide pour conserver)</span></label>
    <input type="password" name="password" autocomplete="new-password">
  </div>
  <div class="field">
    <label>Confirmer le mot de passe</label>
    <input type="password" name="password_confirm" autocomplete="new-password">
  </div>
  <button class="btn btn--primary" type="submit">Enregistrer</button>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

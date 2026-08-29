<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin']);
$title  = 'Utilisateurs & rôles';
$active = 'users.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    Database::run(
        'INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)',
        [$_POST['name'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role']]
    );
    header('Location: users.php');
    exit;
}

$users = Database::all('SELECT id, name, email, role, is_active, created_at FROM users ORDER BY name');
$ROLES = [
    'admin'      => 'Administrateur — accès complet',
    'secretaire' => 'Secrétaire — calendrier, équipes, contenus',
    'arbitre'    => 'Arbitre — saisie live uniquement',
];

ob_start(); ?>
<div class="card">
  <h2>Nouvel utilisateur</h2>
  <form method="post" class="grid c4" style="align-items:end">
    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
    <label>Nom<input type="text" name="name" required></label>
    <label>E-mail<input type="email" name="email" required></label>
    <label>Mot de passe<input type="password" name="password" minlength="8" required></label>
    <label>Rôle
      <select name="role"><?php foreach ($ROLES as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?></select>
    </label>
    <div><button class="btn" type="submit">Créer le compte</button></div>
  </form>
</div>

<div class="card">
  <h2>Comptes</h2>
  <table>
    <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Statut</th><th>Créé le</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><strong><?= e($u['name']) ?></strong></td>
        <td><?= e($u['email']) ?></td>
        <td><?= e($u['role']) ?></td>
        <td><span class="badge <?= $u['is_active'] ? 'ok' : 'grey' ?>"><?= $u['is_active'] ? 'actif' : 'désactivé' ?></span></td>
        <td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

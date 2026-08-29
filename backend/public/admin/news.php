<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin', 'secretaire']);
$title  = 'Actualités';
$active = 'news.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['title']), '-'));
    Database::run(
        'INSERT INTO news (title, slug, category, excerpt, body, published_at, author_id) VALUES (?,?,?,?,?,?,?)',
        [$_POST['title'], $slug . '-' . substr((string) time(), -5), $_POST['category'],
         $_POST['excerpt'], $_POST['body'],
         ($_POST['publish'] ?? '') === '1' ? date('Y-m-d H:i:s') : null, $user['id']]
    );
    header('Location: news.php');
    exit;
}

$news = Database::all('SELECT id, title, category, published_at FROM news ORDER BY COALESCE(published_at, NOW()) DESC LIMIT 50');

ob_start(); ?>
<div class="card">
  <h2>Publier une actualité</h2>
  <form method="post" class="grid" style="gap:14px">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="grid c2">
      <label>Titre<input type="text" name="title" required maxlength="200"></label>
      <label>Catégorie
        <select name="category">
          <?php foreach (['Grand Prix', 'Championnat', 'Super Coupe', 'Formation', 'Organisation', 'Communiqué'] as $c): ?>
            <option><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label>Chapeau<textarea name="excerpt" rows="2" maxlength="400"></textarea></label>
    <label>Article<textarea name="body" rows="8"></textarea></label>
    <label style="flex-direction:row;align-items:center;gap:8px;text-transform:none;letter-spacing:0;font-size:13px">
      <input type="checkbox" name="publish" value="1" checked style="width:auto"> Publier immédiatement dans l'application
    </label>
    <div><button class="btn or" type="submit">Enregistrer</button></div>
  </form>
</div>

<div class="card">
  <h2>Articles</h2>
  <table>
    <thead><tr><th>Titre</th><th>Catégorie</th><th>Publication</th></tr></thead>
    <tbody>
    <?php foreach ($news as $n): ?>
      <tr>
        <td><strong><?= e($n['title']) ?></strong></td>
        <td><?= e($n['category']) ?></td>
        <td><?= $n['published_at'] ? e(date('d/m/Y H\\hi', strtotime($n['published_at']))) : '<span class="badge warn">Brouillon</span>' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

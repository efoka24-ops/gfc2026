<?php
require __DIR__ . '/bootstrap.php';
use Gfc\Auth;
use Gfc\Database;

$user   = Auth::requireSession(['admin', 'secretaire']);
$title  = 'Photos & vidéos';
$active = 'media.php';

$uploadDir = $config['app']['upload_dir'];
$error     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $url = trim($_POST['url'] ?? '');

    if (!empty($_FILES['file']['tmp_name'])) {
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }
        $ext   = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $valid = ['jpg', 'jpeg', 'png', 'webp', 'mp4'];
        if (!in_array($ext, $valid, true)) {
            $error = 'Format non autorisé (jpg, png, webp, mp4).';
        } elseif ($_FILES['file']['size'] > 25 * 1024 * 1024) {
            $error = 'Fichier trop lourd (25 Mo maximum).';
        } else {
            $name = bin2hex(random_bytes(8)) . '.' . $ext;
            move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . '/' . $name);
            $url = $config['app']['base_url'] . '/uploads/' . $name;
        }
    }

    if (!$error && $url !== '') {
        Database::run(
            'INSERT INTO media (type, title, url, thumbnail, match_id) VALUES (?,?,?,?,?)',
            [$_POST['type'], $_POST['title'], $url, $_POST['thumbnail'] ?: $url,
             $_POST['match_id'] !== '' ? (int) $_POST['match_id'] : null]
        );
        header('Location: media.php');
        exit;
    }
}

$matches = Database::all(
    "SELECT m.id, CONCAT(h.abbr,' - ',a.abbr,' (',DATE_FORMAT(m.kickoff_at,'%d/%m'),')') AS label
     FROM matches m JOIN teams h ON h.id = m.home_team_id JOIN teams a ON a.id = m.away_team_id
     ORDER BY m.kickoff_at DESC LIMIT 40"
);
$media = Database::all('SELECT * FROM media ORDER BY published_at DESC LIMIT 60');

ob_start(); ?>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<div class="card">
  <h2>Ajouter un média</h2>
  <form method="post" enctype="multipart/form-data" class="grid" style="gap:14px">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <div class="grid c4">
      <label>Type
        <select name="type"><option value="photo">Photo</option><option value="video">Vidéo</option></select>
      </label>
      <label>Titre<input type="text" name="title" required></label>
      <label>Match associé
        <select name="match_id"><option value="">—</option>
          <?php foreach ($matches as $m): ?><option value="<?= (int) $m['id'] ?>"><?= e($m['label']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>Fichier<input type="file" name="file" accept="image/*,video/mp4"></label>
    </div>
    <div class="grid c2">
      <label>… ou URL externe (YouTube, Facebook)<input type="url" name="url" placeholder="https://"></label>
      <label>Vignette<input type="url" name="thumbnail" placeholder="https://"></label>
    </div>
    <div><button class="btn" type="submit">Ajouter</button></div>
  </form>
</div>

<div class="card">
  <h2><?= count($media) ?> médias</h2>
  <table>
    <thead><tr><th>Type</th><th>Titre</th><th>URL</th><th>Publié le</th></tr></thead>
    <tbody>
    <?php foreach ($media as $m): ?>
      <tr>
        <td><span class="badge <?= $m['type'] === 'video' ? 'live' : 'grey' ?>"><?= e($m['type']) ?></span></td>
        <td><strong><?= e($m['title']) ?></strong></td>
        <td class="hint"><?= e(mb_strimwidth($m['url'], 0, 60, '…')) ?></td>
        <td><?= e(date('d/m/Y H\\hi', strtotime($m['published_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

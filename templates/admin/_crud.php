<?php
/**
 * Rendu CRUD generique.
 * @var array  $crud    ['entity'=>slug route, 'title'=>'equipe', 'fields'=>[...], 'columns'=>[names]]
 * @var array  $rows    lignes brutes de la base (avec 'id')
 * @var ?string $notice  @var ?string $error
 */
use Gfc\Core\View;

$fields  = $crud['fields'];
$columns = $crud['columns'] ?? array_column($fields, 'name');
$entity  = $crud['entity'];
$labelOf = [];
foreach ($fields as $f) { $labelOf[$f['name']] = $f['label']; }

$renderField = static function (array $f, array $row = []): string {
    $name = $f['name'];
    $val  = $row[$name] ?? ($f['default'] ?? '');
    $type = $f['type'] ?? 'text';
    $req  = !empty($f['required']) ? 'required' : '';
    $h    = static fn ($v) => View::e((string) $v);
    $out  = '<label class="field"><span>' . $h($f['label']) . '</span>';
    if ($type === 'select') {
        $out .= '<select class="input" name="' . $h($name) . '">';
        foreach (($f['options'] ?? []) as $ov => $ol) {
            $sel = ((string) $val === (string) $ov) ? ' selected' : '';
            $out .= '<option value="' . $h($ov) . '"' . $sel . '>' . $h($ol) . '</option>';
        }
        $out .= '</select>';
    } elseif ($type === 'textarea') {
        $out .= '<textarea class="input" name="' . $h($name) . '" rows="3">' . $h($val) . '</textarea>';
    } elseif ($type === 'checkbox') {
        $out .= '<input type="checkbox" name="' . $h($name) . '" value="1"' . (!empty($val) ? ' checked' : '') . '>';
    } elseif ($type === 'password') {
        $out .= '<input class="input" type="password" name="' . $h($name) . '" autocomplete="new-password">';
    } elseif ($type === 'slug') {
        $out .= '<input class="input" type="text" name="' . $h($name) . '" value="' . $h($val) . '" placeholder="(auto)">';
    } elseif ($type === 'file') {
        if ($val) {
            $out .= '<img src="' . $h($val) . '" alt="" style="height:40px;border-radius:6px;margin-bottom:6px;display:block;object-fit:cover">';
        }
        $out .= '<input class="input" type="file" name="' . $h($name) . '" accept="image/*">';
    } else {
        $it = in_array($type, ['date','datetime-local','number','email','color'], true) ? $type : 'text';
        if ($type === 'datetime-local' && $val) { $val = str_replace(' ', 'T', substr((string) $val, 0, 16)); }
        $out .= '<input class="input" type="' . $it . '" name="' . $h($name) . '" value="' . $h($val) . '" ' . $req . '>';
    }
    return $out . '</label>';
};

ob_start();
?>
<?php if (!empty($notice)): ?><div class="pill pill--ok" style="display:inline-block;margin-bottom:14px"><?= View::e($notice) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="pill pill--bad" style="display:inline-block;margin-bottom:14px"><?= View::e($error) ?></div><?php endif; ?>

<details class="card card--pad" style="margin-bottom:16px">
  <summary style="cursor:pointer;font-weight:600;color:var(--primary)">+ Ajouter <?= View::e($crud['title']) ?></summary>
  <form method="post" action="/admin/<?= View::e($entity) ?>" enctype="multipart/form-data" style="margin-top:14px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;align-items:end">
    <input type="hidden" name="_action" value="create">
    <?php foreach ($fields as $f): if (!empty($f['noform'])) continue; ?>
      <?= $renderField($f) ?>
    <?php endforeach; ?>
    <button class="btn btn--primary" type="submit">Enregistrer</button>
  </form>
</details>

<div class="card">
  <div class="table__scroll">
    <table class="table">
      <thead><tr>
        <?php foreach ($columns as $c): ?><th><?= View::e($labelOf[$c] ?? $c) ?></th><?php endforeach; ?>
        <th style="width:160px"></th>
      </tr></thead>
      <tbody>
      <?php if ($rows === []): ?><tr><td class="table__empty" colspan="<?= count($columns) + 1 ?>">Aucune donnée.</td></tr><?php endif; ?>
      <?php foreach ($rows as $row): ?>
        <tr>
          <?php foreach ($columns as $c): ?><td><?= View::e((string) ($row[$c] ?? '—')) ?></td><?php endforeach; ?>
          <td style="text-align:right;white-space:nowrap">
            <details style="display:inline-block">
              <summary class="btn btn--mini" style="list-style:none">Éditer</summary>
              <div class="card card--pad" style="position:absolute;right:24px;z-index:5;min-width:280px;box-shadow:0 12px 40px rgba(0,0,0,.2)">
                <form method="post" action="/admin/<?= View::e($entity) ?>" enctype="multipart/form-data" style="display:grid;gap:10px">
                  <input type="hidden" name="_action" value="update">
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                  <?php foreach ($fields as $f): if (!empty($f['noform'])) continue; ?>
                    <?= $renderField($f, $row) ?>
                  <?php endforeach; ?>
                  <button class="btn btn--primary btn--mini" type="submit">Mettre à jour</button>
                </form>
              </div>
            </details>
            <form method="post" action="/admin/<?= View::e($entity) ?>" style="display:inline" onsubmit="return confirm('Supprimer définitivement ?')">
              <input type="hidden" name="_action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
              <button class="btn btn--mini btn--ghost" type="submit">Suppr.</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

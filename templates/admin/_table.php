<?php
/**
 * Tableau générique du back office.
 * @var array  $cols   [['label'=>..,'w'=>'80px','align'=>'center'], ...]
 * @var array  $rows   [[['v'=>..,'pill'=>'ok|wait|bad|neutral','strong'=>true,'num'=>true], ...], ...]
 * @var string $search
 * @var array  $filters
 * @var string $count
 */
use Gfc\Core\View;
?>
<div class="card">
  <div class="table__toolbar">
    <input class="input" type="search" placeholder="<?= View::e($search ?? 'Rechercher…') ?>" data-table-search />
    <?php foreach (($filters ?? []) as $filter): ?>
      <button class="chip"><?= View::e($filter) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="table__scroll">
    <table class="table">
      <thead>
        <tr>
          <?php foreach ($cols as $col): ?>
            <th style="<?= isset($col['w']) ? 'width:' . $col['w'] . ';' : '' ?>text-align:<?= $col['align'] ?? 'left' ?>">
              <?= View::e($col['label']) ?>
            </th>
          <?php endforeach; ?>
          <th style="width:86px"></th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows === []): ?>
          <tr><td class="table__empty" colspan="<?= count($cols) + 1 ?>">Aucune donnée pour le moment.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($row as $i => $cell): ?>
              <td style="text-align:<?= $cols[$i]['align'] ?? 'left' ?>">
                <?php if (!empty($cell['pill'])): ?>
                  <span class="pill pill--<?= View::e($cell['pill']) ?>"><?= View::e((string) $cell['v']) ?></span>
                <?php else: ?>
                  <span class="<?= !empty($cell['strong']) ? 'cell--strong' : (!empty($cell['num']) ? 'cell--num' : '') ?>"><?= View::e((string) $cell['v']) ?></span>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>
            <td style="text-align:right"><button class="btn btn--mini">Éditer</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="table__foot">
    <span><?= View::e($count ?? '') ?></span>
    <span class="pager"><button class="btn btn--mini">Précédent</button><button class="btn btn--mini">Suivant</button></span>
  </div>
</div>

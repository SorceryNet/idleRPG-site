<?php
// items.php — live items on the map, from mapitems.db
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';

$irpg_page_title = 'Items on the World Map';
$active = 'items';
include __DIR__.'/_inc/header.php';

$rows   = [];
$totals = ['all'=>0,'unique'=>0,'normal'=>0,'byType'=>[]];
$err    = null;

if (!is_readable($irpg_itemdb)) {
  $err = "Item database not readable: {$irpg_itemdb}";
} else {
  $fh = fopen($irpg_itemdb, 'r');
  if ($fh === false) {
    $err = "Unable to open item database.";
  } else {
    // First line is a header/comment; skip
    fgets($fh, 1024);
    while (($line = fgets($fh, 1024)) !== false) {
      // Expected: x  y  type  level
      $p = explode("\t", trim($line));
      if (!$p || count($p) < 4) continue;

      $x     = (int)($p[0] ?? 0);
      $y     = (int)($p[1] ?? 0);
      $type  = (string)($p[2] ?? '');
      $level = (string)($p[3] ?? '');

      // Map logic parity: numeric level = normal (orange), non-numeric = unique (yellow)
      $isUnique = !is_numeric($level);
      $rarity   = $isUnique ? 'Unique' : 'Normal';

      $rows[] = [
        'type'    => $type,
        'level'   => $level,
        'x'       => $x,
        'y'       => $y,
        'rarity'  => $rarity,
        'unique'  => $isUnique,
      ];

      $totals['all']++;
      $totals[$isUnique ? 'unique' : 'normal']++;

      if (!isset($totals['byType'][$type])) {
        $totals['byType'][$type] = ['all'=>0,'unique'=>0,'normal'=>0];
      }
      $totals['byType'][$type]['all']++;
      $totals['byType'][$type][$isUnique ? 'unique' : 'normal']++;
    }
    fclose($fh);
  }
}
?>

<section class="card">
  <h1>Items on the World Map</h1>
  <p class="lead">Normal items have a numeric level; “unique” items have a non-numeric marker.</p>
</section>

<section class="card">
  <?php if ($err): ?>
    <p class="text-muted"><?php echo e($err); ?></p>
  <?php else: ?>
    <div class="kpi mt-2" style="display:flex;gap:1rem;flex-wrap:wrap;">
      <div class="metric"><div class="value"><?php echo format_num($totals['all']); ?></div><div class="hint">total items</div></div>
      <div class="metric"><div class="value"><?php echo format_num($totals['normal']); ?></div><div class="hint">normal</div></div>
      <div class="metric"><div class="value"><?php echo format_num($totals['unique']); ?></div><div class="hint">unique</div></div>
      <div class="metric"><div class="hint"><a href="<?php echo $BASEURL; ?>worldmap.php">View on map →</a></div></div>
    </div>
  <?php endif; ?>
</section>

<section class="card">
  <h2>All Items</h2>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Level / Mark</th>
          <th>Rarity</th>
          <th>X</th>
          <th>Y</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows): ?>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?php echo e($r['type']); ?></td>
              <td><?php echo e($r['level']); ?></td>
              <td><?php echo $r['unique'] ? '<strong style="color:var(--brand-gold)">Unique</strong>' : 'Normal'; ?></td>
              <td><?php echo (int)$r['x']; ?></td>
              <td><?php echo (int)$r['y']; ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-muted">No items found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if (!empty($totals['byType'])): ?>
<section class="card">
  <h2>Counts by Type</h2>
  <div class="table-wrapper">
    <table class="table">
      <thead><tr><th>Type</th><th>Total</th><th>Normal</th><th>Unique</th></tr></thead>
      <tbody>
        <?php foreach ($totals['byType'] as $type => $t): ?>
          <tr>
            <td><?php echo e($type); ?></td>
            <td><?php echo format_num($t['all']); ?></td>
            <td><?php echo format_num($t['normal']); ?></td>
            <td><?php echo format_num($t['unique']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__.'/_inc/footer.php'; ?>

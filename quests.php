<?php
// quests.php — modernised, same logic/outputs as your old quest.php
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';

$irpg_page_title = "Quest Info";
$active = 'quests';
include __DIR__.'/_inc/header.php';

// Parse quest file
$type = 0;      // 0 = none, 1 = timer, 2 = coordinates/stage
$text = '';
$time = 0;
$stage = 0;
$p1 = [0,0]; $p2 = [0,0];
$player = [1=>['name'=>''],2=>['name'=>''],3=>['name'=>''],4=>['name'=>'']];

if (is_readable($irpg_qfile) && ($file = fopen($irpg_qfile, "r"))) {
  while (($line = fgets($file, 1024)) !== false) {
    $arg = explode(" ", trim($line));
    if (!isset($arg[0])) continue;

    if ($arg[0] === "T") {
      unset($arg[0]);
      $text = trim(implode(" ", $arg));
    }
    elseif ($arg[0] === "Y") {
      $type = (int)$arg[1];
    }
    elseif ($arg[0] === "P") {
      $p1[0] = (int)($arg[1] ?? 0);
      $p1[1] = (int)($arg[2] ?? 0);
      $p2[0] = (int)($arg[3] ?? 0);
      $p2[1] = (int)($arg[4] ?? 0);
    }
    elseif ($arg[0] === "S") {
      if ($type === 1) $time = (int)($arg[1] ?? 0);
      elseif ($type === 2) $stage = (int)($arg[1] ?? 0);
    }
    elseif ($arg[0] === "P1") {
      $player[1]['name'] = $arg[1] ?? '';
      if ($type === 2) { $player[1]['x'] = (int)($arg[2] ?? 0); $player[1]['y'] = (int)($arg[3] ?? 0); }
    }
    elseif ($arg[0] === "P2") {
      $player[2]['name'] = $arg[1] ?? '';
      if ($type === 2) { $player[2]['x'] = (int)($arg[2] ?? 0); $player[2]['y'] = (int)($arg[3] ?? 0); }
    }
    elseif ($arg[0] === "P3") {
      $player[3]['name'] = $arg[1] ?? '';
      if ($type === 2) { $player[3]['x'] = (int)($arg[2] ?? 0); $player[3]['y'] = (int)($arg[3] ?? 0); }
    }
    elseif ($arg[0] === "P4") {
      $player[4]['name'] = $arg[1] ?? '';
      if ($type === 2) { $player[4]['x'] = (int)($arg[2] ?? 0); $player[4]['y'] = (int)($arg[3] ?? 0); }
    }
  }
  fclose($file);
}
?>

<section class="card">
  <h1>Current Quest</h1>
  <?php if ($type === 0): ?>
    <p>Sorry, there is no active quest.</p>
  <?php else: ?>
    <p><strong>Quest:</strong> To <?php echo e($text); ?>.</p>

    <?php if ($type === 1): ?>
      <p><strong>Time to completion:</strong> <?php echo e(duration($time - time())); ?></p>
    <?php elseif ($type === 2): ?>
      <?php if ($stage === 1): ?>
        <p><strong>Current goal:</strong> [<?php echo e($p1[0]); ?>,<?php echo e($p1[1]); ?>]</p>
      <?php else: ?>
        <p><strong>Current goal:</strong> [<?php echo e($p2[0]); ?>,<?php echo e($p2[1]); ?>]</p>
      <?php endif; ?>
    <?php endif; ?>

    <?php for ($i=1; $i<=4; $i++): ?>
      <?php $pn = $player[$i]['name'] ?? ''; ?>
      <?php if ($pn !== ''): ?>
        <p><strong>Participant <?php echo $i; ?>:</strong>
          <a href="<?php echo $BASEURL.'playerview.php?player='.rawurlencode($pn); ?>">
            <?php echo e($pn); ?>
          </a><br/>
          <?php if ($type === 2 && isset($player[$i]['x'], $player[$i]['y'])): ?>
            <strong>Position:</strong> [<?php echo (int)$player[$i]['x']; ?>,<?php echo (int)$player[$i]['y']; ?>]
          <?php endif; ?>
        </p>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($type === 2): ?>
      <h2>Quest Map:</h2>
      <p class="text-muted">[Questers are shown in blue, current goal in red]</p>

      <div id="map" class="map-stage">
        <img src="<?php echo $BASEURL; ?>makequestmap.php" alt="Idle RPG Quest Map" usemap="#quest" class="map-img"
             style="max-width:100%;height:auto;border:1px solid var(--border);border-radius:10px;box-shadow:var(--shadow);" />
        <map id="quest" name="quest">
          <?php for ($i=1; $i<=4; $i++):
            if (!empty($player[$i]['name']) && isset($player[$i]['x'],$player[$i]['y'])): ?>
              <area shape="circle"
                    coords="<?php echo (int)$player[$i]['x']; ?>,<?php echo (int)$player[$i]['y']; ?>,6"
                    alt="<?php echo e($player[$i]['name']); ?>"
                    href="<?php echo $BASEURL.'playerview.php?player='.rawurlencode($player[$i]['name']); ?>"
                    title="<?php echo e($player[$i]['name']); ?>" />
          <?php endif; endfor; ?>
        </map>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php include __DIR__.'/_inc/footer.php'; ?>

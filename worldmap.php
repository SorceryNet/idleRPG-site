<?php
// worldmap.php — fixed 750×750 map, clickable hotspots, legend only (no toggles)
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';

$irpg_page_title = "World Map";
$active = 'map';
include __DIR__.'/_inc/header.php';

// Build areas
$playerAreas = []; $itemAreas = []; $errMsgs = [];

/* --- Players --- */
if (!is_readable($irpg_db)) {
  $errMsgs[] = "Character database not readable.";
} else if (($fh = fopen($irpg_db, 'r')) !== false) {
  fgets($fh, 1024); // skip header
  while (($line = fgets($fh, 1024)) !== false) {
    $p = explode("\t", trim($line));
    $who = $p[0]  ?? '';
    $x   = $p[10] ?? '';
    $y   = $p[11] ?? '';
    if ($who !== '' && $x !== '' && $y !== '') {
      $playerAreas[] = ['who'=>$who,'x'=>(int)$x,'y'=>(int)$y];
    }
  }
  fclose($fh);
} else {
  $errMsgs[] = "Unable to open character database.";
}

/* --- Items --- */
if (!is_readable($irpg_itemdb)) {
  $errMsgs[] = "Item database not readable.";
} else if (($ifh = fopen($irpg_itemdb, 'r')) !== false) {
  fgets($ifh, 1024); // skip header
  while (($line = fgets($ifh, 1024)) !== false) {
    $p = explode("\t", trim($line));
    $x     = $p[0] ?? '';
    $y     = $p[1] ?? '';
    $type  = $p[2] ?? '';
    $level = $p[3] ?? '';
    if ($x !== '' && $y !== '') {
      $label = $type !== '' ? "$type [$level]" : "Item";
      $itemAreas[] = ['x'=>(int)$x,'y'=>(int)$y,'label'=>$label];
    }
  }
  fclose($ifh);
} else {
  $errMsgs[] = "Unable to open item database.";
}
?>

<section class="card">
  <h1>World Map</h1>
  <div class="legend" aria-label="Legend" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
    <span style="display:inline-flex;align-items:center;gap:.35rem;"><i style="width:10px;height:10px;background:#0080ff;display:inline-block;border-radius:50%;"></i> Online</span>
    <span style="display:inline-flex;align-items:center;gap:.35rem;"><i style="width:10px;height:10px;background:#d30000;display:inline-block;border-radius:50%;"></i> Offline</span>
    <span style="display:inline-flex;align-items:center;gap:.35rem;"><i style="width:10px;height:10px;background:#ff8000;display:inline-block;border-radius:50%;"></i> Item</span>
    <span style="display:inline-flex;align-items:center;gap:.35rem;"><i style="width:10px;height:10px;background:#ffc000;display:inline-block;border-radius:50%;"></i> Unique</span>
  </div>
</section>

<section class="card">
  <?php if ($errMsgs): ?>
    <p class="text-muted"><?php foreach ($errMsgs as $m) echo e($m)."<br>"; ?></p>
  <?php endif; ?>

  <div id="map" class="map-stage" style="width:min(100%, 750px); margin:0 auto;">
    <img
      id="worldImg"
      src="<?php echo $BASEURL; ?>makeworldmap.php"
      alt="IdleRPG World Map"
      title="IdleRPG World Map"
      usemap="#world"
      class="map-img"
      style="width:100%; height:auto; border:1px solid var(--border); border-radius:10px; box-shadow:var(--shadow);" />

    <map id="world" name="world">
<?php
  // Players (clickable -> playerview)
  foreach ($playerAreas as $p) {
    echo '      <area shape="circle" data-x="'.(int)$p['x'].'" data-y="'.(int)$p['y'].'" data-r="'.(int)$crosssize.
         '" coords="'.(int)$p['x'].','.((int)$p['y']).','.(int)$crosssize.
         '" alt="'.e($p['who']).'" href="playerview.php?player='.rawurlencode($p['who']).
         '" title="'.e($p['who']).'" />'."\n";
  }
  // Items (tooltip only)
  foreach ($itemAreas as $it) {
    echo '      <area shape="circle" data-x="'.(int)$it['x'].'" data-y="'.(int)$it['y'].'" data-r="'.(int)$crosssize.
         '" coords="'.(int)$it['x'].','.((int)$it['y']).','.(int)$crosssize.
         '" alt="'.e($it['label']).'" title="'.e($it['label']).'" />'."\n";
  }
?>
    </map>
  </div>
</section>

<script>
// Auto-rescale <area> coords to match displayed image size
(function(){
  const img = document.getElementById('worldImg');
  const getAreas = () => document.querySelectorAll('map[name="world"] area');

  function rescaleAreas() {
    if (!img.naturalWidth || !img.naturalHeight) return;
    const scaleX = img.clientWidth  / img.naturalWidth;
    const scaleY = img.clientHeight / img.naturalHeight;
    const rScale = (scaleX + scaleY) / 2;

    getAreas().forEach(a => {
      const x = parseFloat(a.dataset.x || '0');
      const y = parseFloat(a.dataset.y || '0');
      const r = parseFloat(a.dataset.r || '0');
      a.coords = Math.round(x*scaleX)+','+Math.round(y*scaleY)+','+Math.max(1,Math.round(r*rScale));
    });
  }
  if (!img.complete) img.addEventListener('load', rescaleAreas, { once:true });
  window.addEventListener('resize', rescaleAreas);
  setTimeout(rescaleAreas, 50);
})();
</script>

<?php include __DIR__.'/_inc/footer.php'; ?>

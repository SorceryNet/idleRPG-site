<?php
// playerview.php — modernised, preserves legacy behaviour
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';  // e(), helpers

// --- Input sanitisation ----------------------------------------------------
$player = isset($_GET['player']) ? substr((string)$_GET['player'], 0, 30) : '';
$showmap = isset($_GET['showmap']) ? (int)$_GET['showmap'] : 0;
$allmods = isset($_GET['allmods']) ? (int)$_GET['allmods'] : 0;

// Redirect if no player
if ($player === '') {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
  $host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
  header('Location: '.$scheme.$host.$BASEURL.'players.php');
  exit;
}

$irpg_page_title = "Player Info: " . $player;
$active = 'players';
include __DIR__.'/_inc/header.php';

// --- Load player record ----------------------------------------------------
$found    = false;
$record   = null;
$pen      = [];  // penalties
$item     = [];  // items
$err      = null;

if (!is_readable($irpg_db)) {
  $err = "Character database not readable: ".$irpg_db;
} else {
  $fh = fopen($irpg_db, "r");
  if ($fh === false) {
    $err = "Unable to open character database.";
  } else {
    fgets($fh, 1024); // skip header/comment line
    while (($line = fgets($fh, 8192)) !== false) {
      // Fast check: line starts with "player\t"
      if (strncmp($line, $player."\t", strlen($player)+1) === 0) {
        $parts = explode("\t", trim($line));
        // Keep original field mapping exactly as before
        // list indices for readability
        $idx = 0;
        $user     = $parts[$idx++] ?? '';           // 0
        $idx++;                                     // 1 (unused)
        $isadmin  = (int)($parts[$idx++] ?? 0);     // 2
        $level    = (int)($parts[$idx++] ?? 0);     // 3
        $class    = $parts[$idx++] ?? '';           // 4
        $secs     = (int)($parts[$idx++] ?? 0);     // 5
        $idx++;                                     // 6 (unused)
        $uhost    = $parts[$idx++] ?? '';           // 7
        $online   = !empty($parts[$idx++] ?? '');   // 8
        $idled    = (int)($parts[$idx++] ?? 0);     // 9
        $x        = $parts[$idx++] ?? '';           // 10
        $y        = $parts[$idx++] ?? '';           // 11

        // penalties
        $pen['mesg']   = (int)($parts[$idx++] ?? 0);  // 12
        $pen['nick']   = (int)($parts[$idx++] ?? 0);  // 13
        $pen['part']   = (int)($parts[$idx++] ?? 0);  // 14
        $pen['kick']   = (int)($parts[$idx++] ?? 0);  // 15
        $pen['quit']   = (int)($parts[$idx++] ?? 0);  // 16
        $pen['quest']  = (int)($parts[$idx++] ?? 0);  // 17
        $pen['logout'] = (int)($parts[$idx++] ?? 0);  // 18

        // timestamps
        $created   = (int)($parts[$idx++] ?? 0);      // 19
        $lastlogin = (int)($parts[$idx++] ?? 0);      // 20

        // items
        $item['amulet']   = $parts[$idx++] ?? '';     // 21
        $item['charm']    = $parts[$idx++] ?? '';     // 22
        $item['helm']     = $parts[$idx++] ?? '';     // 23
        $item['boots']    = $parts[$idx++] ?? '';     // 24
        $item['gloves']   = $parts[$idx++] ?? '';     // 25
        $item['ring']     = $parts[$idx++] ?? '';     // 26
        $item['leggings'] = $parts[$idx++] ?? '';     // 27
        $item['shield']   = $parts[$idx++] ?? '';     // 28
        $item['tunic']    = $parts[$idx++] ?? '';     // 29
        $item['weapon']   = $parts[$idx++] ?? '';     // 30

        $alignment = $parts[$idx++] ?? '';           // 31

        $record = compact(
          'user','isadmin','level','class','secs','uhost','online','idled','x','y',
          'created','lastlogin','alignment'
        );
        $found = true;
        break;
      }
    }
    fclose($fh);
  }
}
?>

<section class="card">
  <h1>Player Info</h1>
  <?php if ($err): ?>
    <p class="text-muted"><?php echo e($err); ?></p>
  <?php elseif (!$found || !$record): ?>
    <h2>Error</h2>
    <p><strong>No such user.</strong></p>
  <?php else: ?>
    <?php
      $classSafe  = htmlentities($record['class'], ENT_QUOTES, 'UTF-8');
      $alignLabel = ($record['alignment']==='e' ? 'Evil' : ($record['alignment']==='n' ? 'Neutral' : 'Good'));
      $eta        = function_exists('duration') ? duration((int)$record['secs']) : ((int)$record['secs'].'s');
      $statusText = $record['online'] ? 'Online' : 'Offline';
      $hostText   = $record['uhost'] ? $record['uhost'] : 'Unknown';
      $createdAt  = $record['created'] ? date("D M j H:i:s Y", (int)$record['created']) : '—';
      $lastLogin  = $record['lastlogin'] ? date("D M j H:i:s Y", (int)$record['lastlogin']) : '—';
      $xmlUrl     = $BASEURL.'xml.php?player='.urlencode($record['user']);
      $mapUrl     = $BASEURL.'makemap.php?player='.urlencode($record['user']);
      $selfUrl    = $BASEURL.'playerview.php?player='.urlencode($record['user']);
    ?>

    <div class="table-wrapper">
      <table class="table">
        <tbody>
          <tr><th>User</th><td><?php echo e($record['user']); ?></td></tr>
          <tr><th>Class</th><td><?php echo $classSafe; ?></td></tr>
          <tr><th>Admin?</th><td><?php echo $record['isadmin'] ? 'Yes' : 'No'; ?></td></tr>
          <tr><th>Level</th><td><?php echo (int)$record['level']; ?></td></tr>
          <tr><th>Next level</th><td><?php echo e($eta); ?></td></tr>
          <tr><th>Status</th><td><?php echo $record['online'] ? 'Online' : '<span class="text-muted">Offline</span>'; ?></td></tr>
          <tr><th>Host</th><td><?php echo e($hostText); ?></td></tr>
          <tr><th>Account Created</th><td><?php echo e($createdAt); ?></td></tr>
          <tr><th>Last login</th><td><?php echo e($lastLogin); ?></td></tr>
          <tr><th>Total time idled</th><td><?php echo function_exists('duration') ? e(duration((int)$record['idled'])) : e($record['idled'].'s'); ?></td></tr>
          <tr><th>Current position</th><td>[<?php echo e($record['x']); ?>,<?php echo e($record['y']); ?>]</td></tr>
          <tr><th>Alignment</th><td><?php echo e($alignLabel); ?></td></tr>
          <tr><th>XML</th><td>[<a href="<?php echo $xmlUrl; ?>">link</a>]</td></tr>
        </tbody>
      </table>
    </div>

    <h2>Map</h2>
    <?php if ($showmap): ?>
      <div id="map" class="mt-1">
        <img src="<?php echo $mapUrl; ?>" alt="Player location map for <?php echo e($record['user']); ?>" style="max-width:100%;height:auto;border:1px solid var(--border);border-radius:10px;box-shadow:var(--shadow);" />
      </div>
      <p class="mt-1"><a href="<?php echo $selfUrl; ?>">Hide map</a></p>
    <?php else: ?>
      <p><a href="<?php echo $selfUrl; ?>&amp;showmap=1">Show map</a></p>
    <?php endif; ?>

    <h2>Items</h2>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Slot</th><th>Value</th></tr></thead>
        <tbody>
          <?php
            ksort($item);
            $sumItems = 0;
            foreach ($item as $slot => $valRaw) {
              $val = $valRaw;
              $badge = '';

              // Unique item detection (same rules you had, with nicer markup)
              $uniquecolor = 'var(--brand-gold)';

              if ($slot === 'helm'    && substr($val, -1) === 'a') { $badge = "Mattt's Omniscience Grand Crown";   $val = (int)$val; }
              if ($slot === 'tunic'   && substr($val, -1) === 'b') { $badge = "Res0's Protectorate Plate Mail";     $val = (int)$val; }
              if ($slot === 'amulet'  && substr($val, -1) === 'c') { $badge = "Dwyn's Storm Magic Amulet";          $val = (int)$val; }
              if ($slot === 'weapon'  && substr($val, -1) === 'd') { $badge = "Jotun's Fury Colossal Sword";        $val = (int)$val; }
              if ($slot === 'weapon'  && substr($val, -1) === 'e') { $badge = "Drdink's Cane of Blind Rage";        $val = (int)$val; }
              if ($slot === 'boots'   && substr($val, -1) === 'f') { $badge = "Mrquick's Magical Boots of Swiftness"; $val = (int)$val; }
              if ($slot === 'weapon'  && substr($val, -1) === 'g') { $badge = "Jeff's Cluehammer of Doom";          $val = (int)$val; }
              if ($slot === 'ring'    && substr($val, -1) === 'h') { $badge = "Juliet's Glorious Ring of Sparkliness"; $val = (int)$val; }

              $sumItems += (int)$val;
              ?>
              <tr>
                <td><?php echo e($slot); ?></td>
                <td>
                  <?php echo e((string)$val); ?>
                  <?php if ($badge): ?>
                    <span class="badge gold" title="Unique item" style="margin-left:.5rem;"><?php echo e($badge); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php } ?>
          <tr>
            <th>sum</th>
            <th><?php echo (int)$sumItems; ?></th>
          </tr>
        </tbody>
      </table>
    </div>

    <h2>Penalties</h2>
    <div class="table-wrapper">
      <table class="table">
        <thead><tr><th>Type</th><th>Time</th></tr></thead>
        <tbody>
          <?php
            ksort($pen);
            $sumPen = 0;
            foreach ($pen as $key => $val) {
              $sumPen += (int)$val;
              echo '<tr><td>'.e($key).'</td><td>'.e(function_exists('duration') ? duration((int)$val) : ($val.'s')).'</td></tr>';
            }
          ?>
          <tr>
            <th>total</th>
            <th><?php echo e(function_exists('duration') ? duration($sumPen) : ($sumPen.'s')); ?></th>
          </tr>
        </tbody>
      </table>
    </div>

    <?php
      // Character modifiers from modifiers.txt (legacy behaviour)
      $mods = [];
      if (is_readable($irpg_mod) && ($mf = fopen($irpg_mod, 'r'))) {
        while (($ln = fgets($mf, 4096)) !== false) {
          // Match same loose patterns you used
          if (strpos($ln, ' '.$player.' ') !== false ||
              strpos($ln, ' '.$player.', ') !== false ||
              strncmp($ln, $player.' ', strlen($player)+1) === 0 ||
              strncmp($ln, $player."'s ", strlen($player)+3) === 0) {
            $mods[] = trim($ln);
          }
        }
        fclose($mf);
      }
      $modsCount = count($mods);
    ?>

    <?php if ($modsCount): ?>
      <h2><?php echo $allmods != 1 ? 'Recent ' : ''; ?>Character Modifiers</h2>
      <p>
      <?php
        if ($allmods == 1 || $modsCount < 6) {
          foreach ($mods as $ln) {
            echo e($ln)."<br />\n";
          }
          echo "<br />\n";
        } else {
          // Show last 5 (same as your logic)
          for ($i = max(0, $modsCount - 5); $i < $modsCount; $i++) {
            echo e($mods[$i])."<br />\n";
          }
        }
      ?>
      </p>

      <?php if ($allmods != 1 && $modsCount > 5): ?>
        <p>
          [<a href="<?php echo $selfUrl; ?>&amp;allmods=1">View all Character Modifiers</a> (<?php echo (int)$modsCount; ?>)]
        </p>
      <?php endif; ?>
    <?php endif; ?>

  <?php endif; ?>
</section>

<?php include __DIR__.'/_inc/footer.php'; ?>

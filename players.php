<?php
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';

$irpg_page_title = "Players";
$active = 'players';

// Inputs
$q       = trim($_GET['q'] ?? '');
$online  = isset($_GET['online']) ? (int)$_GET['online'] : -1;   // -1:any, 1:online, 0:offline
$align   = isset($_GET['align']) && in_array($_GET['align'], ['g','n','e'], true) ? $_GET['align'] : '';
$minlvl  = (int)($_GET['minlvl'] ?? 0);
$sort    = $_GET['sort'] ?? 'cmp_level_desc';
$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = 50; // fixed page size

include __DIR__.'/_inc/header.php';

// Load lines
$rows = [];
if (is_readable($irpg_db) && ($fh = fopen($irpg_db, 'r')) !== false) {
  fgets($fh, 1024);
  while (($line = fgets($fh, 4096)) !== false) { $rows[] = $line; }
  fclose($fh);
}

// Sort (limit to practical options here)
$validSorts = [
  'cmp_level_asc','cmp_level_desc',
  'cmp_user_asc','cmp_user_desc',
  'cmp_online_asc','cmp_online_desc',
];
if (!in_array($sort, $validSorts, true)) $sort = 'cmp_level_desc';
if ($rows) usort($rows, $sort);

// Filter -> structure
$players = [];
if ($rows) {
  foreach ($rows as $line) {
    $p = explode("\t", trim($line));
    $user      = $p[0]  ?? '';
    $isadmin   = (int)($p[2]  ?? 0); // retained for display if needed later
    $level     = (int)($p[3]  ?? 0);
    $class     = $p[4]  ?? '';
    $ttl       = (int)($p[5]  ?? 0);
    $uhost     = $p[7]  ?? '';
    $isOnline  = (int)($p[8]  ?? 0);
    $idled     = (int)($p[9]  ?? 0);
    $x         = (int)($p[10] ?? 0);
    $y         = (int)($p[11] ?? 0);
    $created   = (int)($p[19] ?? 0);
    $lastlogin = (int)($p[20] ?? 0);
    $alignment = $p[31] ?? '';

    if ($q !== '' && stripos($user, $q) === false && stripos($class, $q) === false) continue;
    if ($minlvl > 0 && $level < $minlvl) continue;
    if ($online !== -1 && $isOnline !== $online) continue;
    if ($align !== '' && $alignment !== $align)  continue;

    $players[] = compact('user','isadmin','level','class','ttl','uhost','isOnline','idled','x','y','created','lastlogin','alignment');
  }
}

$total  = count($players);
$pages  = max(1, (int)ceil($total / $per));
$page   = min($page, $pages);
$offset = ($page - 1) * $per;
$view   = array_slice($players, $offset, $per);
?>

<section class="card">
  <h1>Players</h1>
  <form method="get" class="filters" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.5rem 1rem;align-items:end;">
    <label>Search
      <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="nick or class">
    </label>
    <label>Min level
      <input type="number" name="minlvl" value="<?php echo (int)$minlvl; ?>" min="0" step="1">
    </label>
    <label>Online
      <select name="online">
        <option value="-1"<?php if($online===-1) echo ' selected'; ?>>Any</option>
        <option value="1"<?php  if($online===1)  echo ' selected'; ?>>Online</option>
        <option value="0"<?php  if($online===0)  echo ' selected'; ?>>Offline</option>
      </select>
    </label>
    <label>Alignment
      <select name="align">
        <option value=""<?php  if($align==='') echo ' selected'; ?>>Any</option>
        <option value="g"<?php if($align==='g') echo ' selected'; ?>>Good</option>
        <option value="n"<?php if($align==='n') echo ' selected'; ?>>Neutral</option>
        <option value="e"<?php if($align==='e') echo ' selected'; ?>>Evil</option>
      </select>
    </label>
    <label>Sort
      <select name="sort">
        <option value="cmp_level_desc"<?php if($sort==='cmp_level_desc') echo ' selected'; ?>>Level (high→low)</option>
        <option value="cmp_level_asc"<?php  if($sort==='cmp_level_asc')  echo ' selected'; ?>>Level (low→high)</option>
        <option value="cmp_user_asc"<?php   if($sort==='cmp_user_asc')   echo ' selected'; ?>>Name (A→Z)</option>
        <option value="cmp_user_desc"<?php  if($sort==='cmp_user_desc')  echo ' selected'; ?>>Name (Z→A)</option>
        <option value="cmp_online_desc"<?php if($sort==='cmp_online_desc') echo ' selected'; ?>>Online first</option>
        <option value="cmp_online_asc"<?php  if($sort==='cmp_online_asc')  echo ' selected'; ?>>Offline first</option>
      </select>
    </label>
    <div><button type="submit">Apply</button></div>
  </form>

  <p class="text-muted" style="margin:.5rem 0 0;">
    Showing <?php echo $total ? ($offset+1).'–'.min($offset+count($view), $total) : '0'; ?> of <?php echo format_num($total); ?> (50 per page)
  </p>
</section>

<section class="card">
  <?php if (!$view): ?>
    <p class="text-muted">No players matched.</p>
  <?php else: ?>
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>Player</th><th>Level</th><th>Class</th><th>Status</th>
            <th>TTL</th><th>Last login</th><th>Align</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($view as $p): ?>
            <tr>
              <td><a href="<?php echo $BASEURL.'playerview.php?player='.rawurlencode($p['user']); ?>"><?php echo e($p['user']); ?></a><?php if(!$p['isOnline']) echo ' <span class="text-muted">(offline)</span>'; ?></td>
              <td><?php echo (int)$p['level']; ?></td>
              <td><?php echo e($p['class']); ?></td>
              <td><?php echo $p['isOnline'] ? 'Online' : 'Offline'; ?></td>
              <td><?php echo e(duration($p['ttl'])); ?></td>
              <td><?php echo $p['lastlogin'] ? e(date("D M j H:i:s Y", $p['lastlogin'])) : '—'; ?></td>
              <td><?php echo $p['alignment']==='g'?'Good':($p['alignment']==='n'?'Neutral':($p['alignment']==='e'?'Evil':'?')); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Pagination" style="margin-top:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;">
        <?php
          $base = $_GET; unset($base['page']); // no per/admin in query anymore
          for ($i=1; $i<=$pages; $i++):
            $href = $BASEURL.'players.php?'.http_build_query($base + ['page'=>$i]);
            $isCur = $i===$page;
        ?>
          <a href="<?php echo $href; ?>" class="btn<?php echo $isCur?' active':''; ?>"<?php echo $isCur?' aria-current="page"':''; ?>>
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php include __DIR__.'/_inc/footer.php'; ?>

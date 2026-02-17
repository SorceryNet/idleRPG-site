<?php
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';

$irpg_page_title = "DB-style Player Listing";
$active = 'db';

// Filters
$q       = trim($_GET['q'] ?? '');
$online  = isset($_GET['online']) ? (int)!!$_GET['online'] : -1;
$admin   = isset($_GET['admin'])  ? (int)!!$_GET['admin']  : -1;
$align   = $_GET['align'] ?? '';
$minlvl  = (int)($_GET['minlvl'] ?? 0);

// Sorting
$sort = $_GET['sort'] ?? 'cmp_level_desc';
$validSorts = [
  'cmp_level_asc','cmp_level_desc',
  'cmp_isadmin_asc','cmp_isadmin_desc',
  'cmp_alignment_asc','cmp_alignment_desc',
  'cmp_ttl_asc','cmp_ttl_desc',
  'cmp_pen_asc','cmp_pen_desc',
  'cmp_lastlogin_asc','cmp_lastlogin_desc',
  'cmp_created_asc','cmp_created_desc',
  'cmp_idled_asc','cmp_idled_desc',
  'cmp_user_asc','cmp_user_desc',
  'cmp_online_asc','cmp_online_desc',
  'cmp_uhost_asc','cmp_uhost_desc',
  'cmp_sum_asc','cmp_sum_desc'
];
if (!in_array($sort, $validSorts, true)) $sort = 'cmp_level_desc';

// Paging
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = min(200, max(10, (int)($_GET['per'] ?? 50)));

include __DIR__.'/_inc/header.php';

// Load file
$file = is_readable($irpg_db) ? file($irpg_db) : [];
if ($file) unset($file[0]); // drop header

// Sort
if ($file) usort($file, $sort);

// Filter
$rows = [];
foreach ($file as $line) {
  $p = explode("\t", trim($line));
  $user      = $p[0]  ?? '';
  $isadmin   = (int)($p[2]  ?? 0);
  $level     = (int)($p[3]  ?? 0);
  $class     = $p[4]  ?? '';
  $secs      = (int)($p[5]  ?? 0);
  $uhost     = $p[7]  ?? '';
  $isOnline  = (int)($p[8]  ?? 0);
  $idled     = (int)($p[9]  ?? 0);
  $x         = (int)($p[10] ?? 0);
  $y         = (int)($p[11] ?? 0);
  $pen = [
    'mesg'=>(int)($p[12]??0),'nick'=>(int)($p[13]??0),'part'=>(int)($p[14]??0),
    'kick'=>(int)($p[15]??0),'quit'=>(int)($p[16]??0),'quest'=>(int)($p[17]??0),
    'logout'=>(int)($p[18]??0),
  ];
  $created   = (int)($p[19] ?? 0);
  $lastlogin = (int)($p[20] ?? 0);
  $item = [
    'amulet'=>(int)($p[21]??0),'charm'=>(int)($p[22]??0),'helm'=>(int)($p[23]??0),
    'boots'=>(int)($p[24]??0),'gloves'=>(int)($p[25]??0),'ring'=>(int)($p[26]??0),
    'leggings'=>(int)($p[27]??0),'shield'=>(int)($p[28]??0),'tunic'=>(int)($p[29]??0),
    'weapon'=>(int)($p[30]??0),
  ];
  $alignment = $p[31] ?? '';

  if ($q !== '' && stripos($user, $q) === false && stripos($class, $q) === false && stripos($uhost, $q) === false) continue;
  if ($minlvl > 0 && $level < $minlvl) continue;
  if ($online !== -1 && $isOnline !== $online) continue;
  if ($admin  !== -1 && $isadmin  !== $admin)  continue;
  if ($align !== '' && $alignment !== $align)  continue;

  $rows[] = compact('user','isadmin','level','class','secs','uhost','isOnline','idled','x','y','pen','created','lastlogin','item','alignment');
}

$total = count($rows);
$pages = max(1, (int)ceil($total / $per));
$page  = min($page, $pages);
$offset = ($page-1)*$per;
$view = array_slice($rows, $offset, $per);

// headerSort helper
if (!function_exists('headerSort')) {
  function headerSort(string $ascKey, string $descKey, string $label): string {
    global $BASEURL, $sort;
    $icon = ($sort === $ascKey) ? ' ▲' : (($sort === $descKey) ? ' ▼' : '');
    $ascHref  = $BASEURL.'db.php?'.http_build_query(array_merge($_GET, ['sort'=>$ascKey,'page'=>1]));
    $descHref = $BASEURL.'db.php?'.http_build_query(array_merge($_GET, ['sort'=>$descKey,'page'=>1]));
    return sprintf('%s<span class="sort-indicator">%s</span> (<a href="%s" title="%s ascending">▲</a> / <a href="%s" title="%s descending">▼</a>)',
      e($label), $icon, $ascHref, $label, $descHref, $label);
  }
}
?>

<section class="card">
  <h1>DB View</h1>
  <form method="get" class="filters" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.5rem 1rem;align-items:end;">
    <label>Search
      <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="nick, class, host">
    </label>
    <label>Min level
      <input type="number" name="minlvl" value="<?php echo (int)$minlvl; ?>" min="0" step="1">
    </label>
    <label>Online
      <select name="online">
        <option value="-1"<?php if($online===-1) echo ' selected'; ?>>Any</option>
        <option value="1"<?php if($online===1)  echo ' selected'; ?>>Online</option>
        <option value="0"<?php if($online===0)  echo ' selected'; ?>>Offline</option>
      </select>
    </label>
    <label>Admin
      <select name="admin">
        <option value="-1"<?php if($admin===-1) echo ' selected'; ?>>Any</option>
        <option value="1"<?php if($admin===1)  echo ' selected'; ?>>Admin only</option>
        <option value="0"<?php if($admin===0)  echo ' selected'; ?>>Non-admin</option>
      </select>
    </label>
    <label>Alignment
      <select name="align">
        <option value=""<?php if($align==='') echo ' selected'; ?>>Any</option>
        <option value="g"<?php if($align==='g') echo ' selected'; ?>>Good</option>
        <option value="n"<?php if($align==='n') echo ' selected'; ?>>Neutral</option>
        <option value="e"<?php if($align==='e') echo ' selected'; ?>>Evil</option>
      </select>
    </label>
    <label>Per page
      <input type="number" name="per" value="<?php echo (int)$per; ?>" min="10" max="200" step="10">
    </label>
    <div><button type="submit">Apply</button></div>
  </form>

  <p class="text-muted" style="margin:.5rem 0 0;">
    Showing <?php echo $total ? ($offset+1).'–'.min($offset+count($view), $total) : '0'; ?> of <?php echo format_num($total); ?>
  </p>
</section>

<section class="card">
  <?php if (!$view): ?>
    <p class="text-muted">No rows matched.</p>
  <?php else: ?>
  <div class="table-wrapper">
    <table class="table channel-table">
      <thead>
        <tr>
          <th><?php echo headerSort('cmp_user_asc','cmp_user_desc','User'); ?></th>
          <th><?php echo headerSort('cmp_level_asc','cmp_level_desc','Level'); ?></th>
          <th><?php echo headerSort('cmp_isadmin_asc','cmp_isadmin_desc','Admin'); ?></th>
          <th>Class</th>
          <th><?php echo headerSort('cmp_ttl_asc','cmp_ttl_desc','TTL'); ?></th>
          <th><?php echo headerSort('cmp_uhost_asc','cmp_uhost_desc','Nick!User@Host'); ?></th>
          <th><?php echo headerSort('cmp_online_asc','cmp_online_desc','Online'); ?></th>
          <th><?php echo headerSort('cmp_idled_asc','cmp_idled_desc','Total Time Idled'); ?></th>
          <th>X Pos</th><th>Y Pos</th>
          <th>Mesg Pen.</th><th>Nick Pen.</th><th>Part Pen.</th><th>Kick Pen.</th><th>Quit Pen.</th><th>Quest Pen.</th><th>LOGOUT Pen.</th>
          <th><?php echo headerSort('cmp_pen_asc','cmp_pen_desc','Total Pen.'); ?></th>
          <th><?php echo headerSort('cmp_created_asc','cmp_created_desc','Acct. Created'); ?></th>
          <th><?php echo headerSort('cmp_lastlogin_asc','cmp_lastlogin_desc','Last Login'); ?></th>
          <th>Amulet</th><th>Charm</th><th>Helm</th><th>Boots</th><th>Gloves</th><th>Ring</th><th>Leggings</th><th>Shield</th><th>Tunic</th><th>Weapon</th>
          <th><?php echo headerSort('cmp_sum_asc','cmp_sum_desc','Sum'); ?></th>
          <th><?php echo headerSort('cmp_alignment_asc','cmp_alignment_desc','Alignment'); ?></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($view as $r):
        $pentot = array_sum($r['pen']);
        $sum    = array_sum($r['item']);
      ?>
        <tr>
          <td nowrap><a href="<?php echo $BASEURL.'playerview.php?player='.rawurlencode($r['user']); ?>"><?php echo e($r['user']); ?></a></td>
          <td><?php echo (int)$r['level']; ?></td>
          <td><?php echo $r['isadmin'] ? 'Yes' : 'No'; ?></td>
          <td nowrap><?php echo e($r['class']); ?></td>
          <td nowrap><?php echo e(duration($r['secs'])); ?></td>
          <td nowrap><?php echo e($r['uhost']); ?></td>
          <td><?php echo $r['isOnline'] ? 'Yes' : 'No'; ?></td>
          <td nowrap><?php echo e(duration($r['idled'])); ?></td>
          <td nowrap><?php echo (int)$r['x']; ?></td>
          <td nowrap><?php echo (int)$r['y']; ?></td>
          <td nowrap><?php echo e(duration($r['pen']['mesg'])); ?></td>
          <td nowrap><?php echo e(duration($r['pen']['nick'])); ?></td>
          <td nowrap><?php echo e(duration($r['pen']['part'])); ?></td>
          <td nowrap><?php echo e(duration($r['pen']['kick'])); ?></td>
          <td nowrap><?php echo e(duration($r['pen']['quit'])); ?></td>
          <td nowrap><?php echo e(duration($r['pen']['quest'])); ?></td>
          <td nowrap><?php echo e(duration($r['pen']['logout'])); ?></td>
          <td nowrap><?php echo e(duration($pentot)); ?></td>
          <td nowrap><?php echo e(date("D M j H:i:s Y", $r['created'])); ?></td>
          <td nowrap><?php echo e(date("D M j H:i:s Y", $r['lastlogin'])); ?></td>
          <td><?php echo $r['item']['amulet']; ?></td>
          <td><?php echo $r['item']['charm']; ?></td>
          <td><?php echo $r['item']['helm']; ?></td>
          <td><?php echo $r['item']['boots']; ?></td>
          <td><?php echo $r['item']['gloves']; ?></td>
          <td><?php echo $r['item']['ring']; ?></td>
          <td><?php echo $r['item']['leggings']; ?></td>
          <td><?php echo $r['item']['shield']; ?></td>
          <td><?php echo $r['item']['tunic']; ?></td>
          <td><?php echo $r['item']['weapon']; ?></td>
          <td><?php echo $sum; ?></td>
          <td><?php echo $r['alignment']==='e'?'Evil':($r['alignment']==='n'?'Neutral':'Good'); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Pagination" style="margin-top:.75rem;display:flex;gap:.5rem;flex-wrap:wrap;">
      <?php
        $base = $_GET; unset($base['page']);
        for ($i=1; $i<=$pages; $i++):
          $href = $BASEURL.'db.php?'.http_build_query($base + ['page'=>$i]);
          $isCur = $i===$page;
      ?>
        <a href="<?php echo $href; ?>" class="btn<?php echo $isCur?' active':''; ?>"<?php echo $isCur?' aria-current="page"':''; ?>><?php echo $i; ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>

  <p class="text-muted" style="margin-top:.75rem;">* Accounts created before Aug 29, 2003 may have incowrect data fields.</p>
  <?php endif; ?>
</section>

<?php include __DIR__.'/_inc/footer.php'; ?>

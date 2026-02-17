<?php
require_once __DIR__.'/../_inc/config.php';
require_once __DIR__.'/../_inc/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

$q = trim($_GET['q'] ?? '');
$limit = min(500, max(1, (int)($_GET['limit'] ?? 100)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$list = [];
if (is_readable($irpg_db) && ($fh = fopen($irpg_db, 'r'))) {
  fgets($fh, 1024);
  while (($line = fgets($fh, 4096)) !== false) {
    $p = explode("\t", trim($line));
    $user = $p[0] ?? '';
    $class= $p[4] ?? '';
    if ($q !== '' && stripos($user, $q) === false && stripos($class, $q) === false) continue;
    $list[] = [
      'user'=>$user,
      'isadmin'=>(int)($p[2]??0),
      'level'=>(int)($p[3]??0),
      'class'=>$class,
      'online'=>(int)($p[8]??0),
      'x'=>(int)($p[10]??0),
      'y'=>(int)($p[11]??0),
      'created'=>(int)($p[19]??0),
      'lastlogin'=>(int)($p[20]??0),
      'alignment'=>$p[31]??'',
    ];
  }
  fclose($fh);
}

// simple sort by level desc
usort($list, function($a,$b){ return ($a['level']===$b['level']) ? 0 : (($a['level']>$b['level'])?-1:1); });

$total = count($list);
$out = array_slice($list, $offset, $limit);

echo json_encode([
  'total'=>$total,'offset'=>$offset,'limit'=>$limit,'results'=>$out
], JSON_UNESCAPED_SLASHES);

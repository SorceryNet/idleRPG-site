<?php
require_once __DIR__.'/../_inc/config.php';
require_once __DIR__.'/../_inc/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

$now = time(); $threshold24h = $now - 86400;
$onlineNow = 0; $active24 = 0;

if (is_readable($irpg_db) && ($fh = fopen($irpg_db, 'r'))) {
  fgets($fh, 1024);
  while (($line = fgets($fh, 4096)) !== false) {
    $p = explode("\t", trim($line));
    $online    = (int)($p[8]  ?? 0);
    $lastlogin = (int)($p[20] ?? 0);
    if ($online === 1) $onlineNow++;
    if ($online === 1 || $lastlogin >= $threshold24h) $active24++;
  }
  fclose($fh);
}

echo json_encode([
  'channel'     => $irpg_chan ?? '#IdleRPG',
  'online_now'  => $onlineNow,
  'active_24h'  => $active24,
  'generated_at'=> date('c', $now),
], JSON_UNESCAPED_SLASHES);

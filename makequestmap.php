<?php
// makequestmap.php — 750×750 quest overlay (type 2 only)
// Optional background, cache headers, bigger dots for players/goal
require_once __DIR__.'/_inc/config.php';

// --- Throttle ---
session_start();
if (isset($_SESSION['time']) && time() - $_SESSION['time'] < 20) {
  header("Location: {$BASEURL}assets/img/maperror.png");
  exit;
}
$_SESSION['time'] = time();

// --- Parse quest file ---
$type = 0; $time = $stage = 0;
$p1 = $p2 = [0,0];
$player = [1=>[],2=>[],3=>[],4=>[]];

if (is_readable($irpg_qfile) && ($fh = fopen($irpg_qfile, 'r'))) {
  while (($line = fgets($fh, 1024)) !== false) {
    $arg = explode(' ', trim($line));
    if ($arg[0] === 'Y') $type = (int)($arg[1] ?? 0);
    elseif ($arg[0] === 'P') { $p1=[(int)$arg[1],(int)$arg[2]]; $p2=[(int)$arg[3],(int)$arg[4]]; }
    elseif ($arg[0] === 'S') { if ($type===1) $time=(int)($arg[1]??0); else if ($type===2) $stage=(int)($arg[1]??0); }
    elseif ($arg[0] === 'P1') { $player[1]['name']=$arg[1]??''; if ($type===2){$player[1]['x']=(int)($arg[2]??0); $player[1]['y']=(int)($arg[3]??0);} }
    elseif ($arg[0] === 'P2') { $player[2]['name']=$arg[1]??''; if ($type===2){$player[2]['x']=(int)($arg[2]??0); $player[2]['y']=(int)($arg[3]??0);} }
    elseif ($arg[0] === 'P3') { $player[3]['name']=$arg[1]??''; if ($type===2){$player[3]['x']=(int)($arg[2]??0); $player[3]['y']=(int)($arg[3]??0);} }
    elseif ($arg[0] === 'P4') { $player[4]['name']=$arg[1]??''; if ($type===2){$player[4]['x']=(int)($arg[2]??0); $player[4]['y']=(int)($arg[3]??0);} }
  }
  fclose($fh);
}
if ($type !== 2) {
  header("Location: {$BASEURL}assets/img/maperror.png");
  exit;
}

// --- Cache headers (quest file + background) ---
$deps = [__FILE__];
if (is_readable($irpg_qfile)) { $deps[] = $irpg_qfile; }
if (isset($MAP_BG_PATH) && is_readable($MAP_BG_PATH)) { $deps[] = $MAP_BG_PATH; }

$mtimes = array_map(fn($p) => is_readable($p) ? filemtime($p) : 0, $deps);
$lastMod = max($mtimes);
$etag = '"'.md5(implode('|', $mtimes) . "|$stage|{$p1[0]},{$p1[1]}|{$p2[0]},{$p2[1]}").'"';

header('Cache-Control: public, max-age=30');
header('ETag: '.$etag);
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT');

$ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
$ifModSince  = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
if ($ifNoneMatch === $etag || (strtotime($ifModSince) >= $lastMod)) {
  http_response_code(304);
  exit;
}

// --- Size ---
$mapWidth  = isset($mapx) ? (int)$mapx : 750;
$mapHeight = isset($mapy) ? (int)$mapy : 750;

// --- Canvas + optional background ---
$map = imagecreatetruecolor($mapWidth, $mapHeight);
$bgcol = imagecolorallocate($map, 11, 27, 46);
imagefilledrectangle($map, 0, 0, $mapWidth, $mapHeight, $bgcol);

if (isset($MAP_BG_PATH) && is_readable($MAP_BG_PATH)) {
  $bg = @imagecreatefrompng($MAP_BG_PATH);
  if ($bg) {
    $bw = imagesx($bg); $bh = imagesy($bg);
    $scale = min($mapWidth / $bw, $mapHeight / $bh);
    $nw = (int) round($bw * $scale);
    $nh = (int) round($bh * $scale);
    $dx = (int) floor(($mapWidth  - $nw) / 2);
    $dy = (int) floor(($mapHeight - $nh) / 2);
    imagecopyresampled($map, $bg, $dx, $dy, 0, 0, $nw, $nh, $bw, $bh);
    imagedestroy($bg);
  }
}

// --- Colors & sizes ---
$blue = imagecolorallocate($map, 0, 128, 255);
$red  = imagecolorallocate($map, 255, 0, 0);
$dotPlayers = max(6, (int)$crosssize + 2);
$dotGoal    = max(8, (int)$crosssize + 4);

// --- Players & goal ---
for ($i=1; $i<=4; $i++) {
  if (isset($player[$i]['x'], $player[$i]['y'])) {
    imagefilledellipse($map, (int)$player[$i]['x'], (int)$player[$i]['y'], $dotPlayers, $dotPlayers, $blue);
  }
}
if ($stage === 1) imagefilledellipse($map, (int)$p1[0], (int)$p1[1], $dotGoal, $dotGoal, $red);
else              imagefilledellipse($map, (int)$p2[0], (int)$p2[1], $dotGoal, $dotGoal, $red);

header("Content-type: image/png");
imagepng($map);
imagedestroy($map);

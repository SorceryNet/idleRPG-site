<?php
// makeworldmap.php — 750×750 world map with thicker markers, optional background,
// cache headers (ETag/Last-Modified), and flags: ?players=1&items=1
require_once __DIR__.'/_inc/config.php';

// --- Throttle (one image per session each 20s) ---
session_start();
if (isset($_SESSION['time']) && time() - $_SESSION['time'] < 20) {
  header("Location: {$BASEURL}assets/img/maperror.png");
  exit;
}
$_SESSION['time'] = time();

// --- Flags (default ON) ---
$showPlayers = isset($_GET['players']) ? (int)!!$_GET['players'] : 1;
$showItems   = isset($_GET['items'])   ? (int)!!$_GET['items']   : 1;

// --- Cache / ETag / Last-Modified (based on data + background) ---
$deps = [__FILE__];
if (is_readable($irpg_db))     { $deps[] = $irpg_db; }
if (is_readable($irpg_itemdb)) { $deps[] = $irpg_itemdb; }
if (isset($MAP_BG_PATH) && is_readable($MAP_BG_PATH)) { $deps[] = $MAP_BG_PATH; }

$mtimes = array_map(fn($p) => is_readable($p) ? filemtime($p) : 0, $deps);
$lastMod = max($mtimes);
$etag = '"'.md5(implode('|', $mtimes) . "|$showPlayers|$showItems").'"';

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

// --- Canvas + optional background (resampled to fit) ---
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

// --- Colors & thickness ---
$blue   = imagecolorallocate($map, 0, 128, 255);
$red    = imagecolorallocate($map, 211, 0, 0);
$orange = imagecolorallocate($map, 255, 128, 0);
$yellow = imagecolorallocate($map, 255, 192, 0);
imagesetthickness($map, max(1, (int)($marker_thickness ?? 3)));

// --- Players ---
if ($showPlayers && is_readable($irpg_db) && ($fh = fopen($irpg_db, 'r'))) {
  fgets($fh, 1024); // skip header
  while (($line = fgets($fh, 1024)) !== false) {
    $p = explode("\t", trim($line));
    $online = (int)($p[8]  ?? 0);
    $x      = (int)($p[10] ?? 0);
    $y      = (int)($p[11] ?? 0);
    $c = $online === 1 ? $blue : $red;
    imageline($map, $x - $crosssize, $y, $x + $crosssize, $y, $c);
    imageline($map, $x, $y - $crosssize, $x, $y + $crosssize, $c);
  }
  fclose($fh);
}

// --- Items ---
if ($showItems && is_readable($irpg_itemdb) && ($ifh = fopen($irpg_itemdb, 'r'))) {
  fgets($ifh, 1024); // skip header
  while (($line = fgets($ifh, 1024)) !== false) {
    $p = explode("\t", trim($line));
    $x     = (int)($p[0] ?? 0);
    $y     = (int)($p[1] ?? 0);
    $level = $p[3] ?? '';
    $c = is_numeric($level) ? $orange : $yellow;
    imageline($map, $x - $crosssize, $y - $crosssize, $x + $crosssize, $y + $crosssize, $c);
    imageline($map, $x + $crosssize, $y - $crosssize, $x - $crosssize, $y + $crosssize, $c);
  }
  fclose($ifh);
}

header("Content-type: image/png");
imagepng($map);
imagedestroy($map);

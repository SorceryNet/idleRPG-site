<?php
require_once __DIR__.'/../_inc/config.php';
require_once __DIR__.'/../_inc/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

$nick = substr((string)($_GET['nick'] ?? ''), 0, 30);
if ($nick === '') { http_response_code(400); echo json_encode(['error'=>'nick required']); exit; }

$found = null;
if (is_readable($irpg_db) && ($fh = fopen($irpg_db, 'r'))) {
  fgets($fh, 1024);
  while (($line = fgets($fh, 4096)) !== false) {
    if (strncmp($line, $nick."\t", strlen($nick)+1) === 0) {
      $p = explode("\t", trim($line));
      $found = [
        'user'=>$p[0]??'','isadmin'=>(int)($p[2]??0),'level'=>(int)($p[3]??0),'class'=>$p[4]??'',
        'ttl'=>(int)($p[5]??0),'uhost'=>$p[7]??'','online'=>(int)($p[8]??0),'idled'=>(int)($p[9]??0),
        'x'=>(int)($p[10]??0),'y'=>(int)($p[11]??0),
        'pen'=>['mesg'=>(int)($p[12]??0),'nick'=>(int)($p[13]??0),'part'=>(int)($p[14]??0),'kick'=>(int)($p[15]??0),'quit'=>(int)($p[16]??0),'quest'=>(int)($p[17]??0),'logout'=>(int)($p[18]??0)],
        'created'=>(int)($p[19]??0),'lastlogin'=>(int)($p[20]??0),
        'items'=>['amulet'=>(int)($p[21]??0),'charm'=>(int)($p[22]??0),'helm'=>(int)($p[23]??0),'boots'=>(int)($p[24]??0),'gloves'=>(int)($p[25]??0),'ring'=>(int)($p[26]??0),'leggings'=>(int)($p[27]??0),'shield'=>(int)($p[28]??0),'tunic'=>(int)($p[29]??0),'weapon'=>(int)($p[30]??0)],
        'alignment'=>$p[31]??'',
      ];
      break;
    }
  }
  fclose($fh);
}

if (!$found) { http_response_code(404); echo json_encode(['error'=>'not found']); exit; }
echo json_encode($found, JSON_UNESCAPED_SLASHES);

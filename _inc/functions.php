<?php
/* ============================================================================
   IdleRPG — Shared helpers (lives in /idlerpg/_inc/functions.php)
   Keeps legacy comparators + duration(), adds safe utilities used by new pages.
   ============================================================================ */

/* ---------- General utilities ---------- */

/** HTML-escape a value (short alias). */
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/** Format a number with thousands separators. */
function format_num($n) {
    return number_format((float)$n, 0, '.', ',');
}

/**
 * Read the last N lines of a text file efficiently.
 * Used by logs.php (optional).
 */
function read_tail($path, $max = 500){
    if (!is_readable($path)) return [];
    $lines = [];
    $fh = fopen($path, 'r');
    if (!$fh) return [];
    fseek($fh, -1, SEEK_END);
    $buffer = '';
    $pos = ftell($fh);
    while ($pos > 0 && count($lines) < $max){
        $step = min(2048, $pos);
        fseek($fh, -$step, SEEK_CUR);
        $chunk = fread($fh, $step);
        $pos -= $step;
        $buffer = $chunk . $buffer;
        fseek($fh, $pos, SEEK_SET);
        $parts = explode("\n", $buffer);
        $buffer = array_shift($parts);
        foreach (array_reverse($parts) as $line){
            if ($line !== '') $lines[] = $line;
            if (count($lines) >= $max) break 2;
        }
    }
    if ($buffer !== '' && count($lines) < $max) $lines[] = $buffer;
    fclose($fh);
    return array_reverse($lines);
}

/** Load and decode a JSON file as an associative array (or null). */
function load_json($path){
    if (!is_readable($path)) return null;
    $txt = file_get_contents($path);
    $data = json_decode($txt, true);
    return is_array($data) ? $data : null;
}

/* ---------- Time formatting ---------- */

/**
 * Legacy duration formatter; preserves original output.
 * Examples: "None", "1 day, 03:04:05", "2 days, 00:00:01"
 */
function duration($s) {
    $s = abs((int)$s);
    if ($s === 0) return "None";
    $days = intdiv($s, 86400);
    $hours = intdiv($s % 86400, 3600);
    $mins = intdiv($s % 3600, 60);
    $secs = $s % 60;
    return sprintf("%d day%s, %02d:%02d:%02d",
                   $days, $days===1 ? "" : "s",
                   $hours, $mins, $secs);
}

/* ---------- Legacy comparators (db.php expects these names) ---------- */
/* All use the same field ordering as your existing .db format.          */
/* They’re intentionally simple to avoid side effects.                   */

function cmp_level_asc($a,$b) { return cmp_level_desc($b,$a); }
function cmp_level_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $level1 = isset($pa[3]) ? (int)$pa[3] : 0;
    $time1  = isset($pa[5]) ? (int)$pa[5] : 0;
    $level2 = isset($pb[3]) ? (int)$pb[3] : 0;
    $time2  = isset($pb[5]) ? (int)$pb[5] : 0;
    if ($level1 === $level2) return ($time1 <= $time2) ? -1 : 1;
    return ($level1 > $level2) ? -1 : 1;
}

function cmp_alignment_asc($a,$b) { return cmp_alignment_desc($b,$a); }
function cmp_alignment_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $a1 = $pa[31] ?? '';
    $a2 = $pb[31] ?? '';
    if ($a1 === 'g' || $a2 === 'e') return -1;
    if ($a1 === 'e' || $a2 === 'g') return 1;
    return 0;
}

function cmp_isadmin_asc($a,$b) { return cmp_isadmin_desc($b,$a); }
function cmp_isadmin_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $o1 = isset($pa[2]) ? (int)$pa[2] : 0;
    $o2 = isset($pb[2]) ? (int)$pb[2] : 0;
    return ($o1 > $o2) ? -1 : 1;
}

function cmp_ttl_asc($a,$b) { return cmp_ttl_desc($b,$a); }
function cmp_ttl_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $t1 = isset($pa[5]) ? (int)$pa[5] : 0;
    $t2 = isset($pb[5]) ? (int)$pb[5] : 0;
    return ($t2 < $t1) ? -1 : 1;
}

function cmp_user_asc($a,$b) { return cmp_user_desc($b,$a); }
function cmp_user_desc($a,$b) {
    $u1 = strtolower(explode("\t", trim($a))[0] ?? '');
    $u2 = strtolower(explode("\t", trim($b))[0] ?? '');
    return ($u1 > $u2) ? -1 : 1;
}

function cmp_online_asc($a,$b) { return cmp_online_desc($b,$a); }
function cmp_online_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $o1 = isset($pa[8]) ? (int)$pa[8] : 0;
    $o2 = isset($pb[8]) ? (int)$pb[8] : 0;
    return ($o1 > $o2) ? -1 : 1;
}

function cmp_idled_asc($a,$b) { return cmp_idled_desc($b,$a); }
function cmp_idled_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $i1 = isset($pa[9]) ? (int)$pa[9] : 0;
    $i2 = isset($pb[9]) ? (int)$pb[9] : 0;
    return ($i1 > $i2) ? -1 : 1;
}

function cmp_created_asc($a,$b) { return cmp_created_desc($b,$a); }
function cmp_created_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $c1 = isset($pa[19]) ? (int)$pa[19] : 0;
    $c2 = isset($pb[19]) ? (int)$pb[19] : 0;
    return ($c1 > $c2) ? -1 : 1;
}

function cmp_lastlogin_asc($a,$b) { return cmp_lastlogin_desc($b,$a); }
function cmp_lastlogin_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $l1 = isset($pa[20]) ? (int)$pa[20] : 0;
    $l2 = isset($pb[20]) ? (int)$pb[20] : 0;
    return ($l1 > $l2) ? -1 : 1;
}

function cmp_uhost_asc($a,$b) { return cmp_uhost_desc($b,$a); }
function cmp_uhost_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    $u1 = strtolower($pa[7] ?? '');
    $u2 = strtolower($pb[7] ?? '');
    return ($u1 > $u2) ? -1 : 1;
}

function cmp_pen_asc($a,$b) { return cmp_pen_desc($b,$a); }
function cmp_pen_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    // penalties start at index 12 .. 18 (mesg..logout)
    $s1 = 0; for ($i=12; $i<=18; $i++) { $s1 += isset($pa[$i]) ? (int)$pa[$i] : 0; }
    $s2 = 0; for ($i=12; $i<=18; $i++) { $s2 += isset($pb[$i]) ? (int)$pb[$i] : 0; }
    return ($s1 > $s2) ? -1 : 1;
}

function cmp_sum_asc($a,$b) { return cmp_sum_desc($b,$a); }
function cmp_sum_desc($a,$b) {
    $pa = explode("\t", trim($a));
    $pb = explode("\t", trim($b));
    // items start at index 21 .. 30 (amulet..weapon)
    $s1 = 0; for ($i=21; $i<=30; $i++) { $s1 += isset($pa[$i]) ? (int)$pa[$i] : 0; }
    $s2 = 0; for ($i=21; $i<=30; $i++) { $s2 += isset($pb[$i]) ? (int)$pb[$i] : 0; }
    return ($s1 > $s2) ? -1 : 1;
}

/* ---------- Optional UI helper (used in db.php). Guard to avoid redeclare. ---------- */
if (!function_exists('headerSort')) {
    /**
     * Build a sortable header label with active ▲/▼ and asc/desc links.
     * You can use this in db.php, or keep the copy there; both are fine.
     */
    function headerSort(string $ascKey, string $descKey, string $label): string {
        global $BASEURL, $sort;
        $icon = ($sort === $ascKey) ? ' ▲' : (($sort === $descKey) ? ' ▼' : '');
        $ascHref  = $BASEURL.'db.php?sort='.rawurlencode($ascKey);
        $descHref = $BASEURL.'db.php?sort='.rawurlencode($descKey);
        $ascAttr  = ($sort === $ascKey)  ? ' aria-current="true"' : '';
        $descAttr = ($sort === $descKey) ? ' aria-current="true"' : '';
        return sprintf(
            '%s<span class="sort-indicator">%s</span> (<a href="%s"%s title="%s ascending">▲</a> / <a href="%s"%s title="%s descending">▼</a>)',
            e($label),
            $icon,
            $ascHref,  $ascAttr,  $label,
            $descHref, $descAttr, $label
        );
    }
}

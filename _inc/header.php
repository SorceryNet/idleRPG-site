<?php
// _inc/header.php — CSS-only hamburger; SorceryNet banner + "#IdleRPG @ irc.sorcery.net" brand text

if (!headers_sent()) {
  header('Referrer-Policy: no-referrer');
  header('X-Content-Type-Options: nosniff');
  header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

if (!isset($irpg_page_title)) { $irpg_page_title = 'IdleRPG'; }
$SITE_TITLE = $SITE_TITLE ?? 'IdleRPG @ SorceryNet';
$full_title = ($irpg_page_title ? $irpg_page_title.' — ' : '') . $SITE_TITLE;

/* Normalize BASEURL to absolute with trailing slash */
if (!isset($BASEURL) || $BASEURL === '') {
  $BASEURL = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/idlerpg/index.php'), '/').'/';
}
if ($BASEURL === '' || $BASEURL === '.') $BASEURL = '/';
if ($BASEURL[0] !== '/') $BASEURL = '/'.ltrim($BASEURL, '/');
if (substr($BASEURL, -1) !== '/') $BASEURL .= '/';

/* Asset bases */
$IMG_URL = $BASEURL . 'assets/img/';
$CSS_URL = $BASEURL . 'assets/css/';

/* Force SorceryNet banner logo */
$IRPG_LOGO_URL = $IMG_URL . 'BannerLogo.png';

/* Nav items (DB last) */
$nav = [
  ['href' => $BASEURL.'index.php',    'key' => 'home',    'label' => 'Home'],
  ['href' => $BASEURL.'players.php',  'key' => 'players', 'label' => 'Players'],
  ['href' => $BASEURL.'quests.php',   'key' => 'quests',  'label' => 'Quests'],
  ['href' => $BASEURL.'items.php',    'key' => 'items',   'label' => 'Items'],
  ['href' => $BASEURL.'worldmap.php', 'key' => 'map',     'label' => 'Map'],
  ['href' => $BASEURL.'db.php',       'key' => 'db',      'label' => 'DB View'],
];
?>
<!doctype html>
<html class="dark" lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($full_title, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?php echo $IMG_URL; ?>favicon.ico">
  <link rel="stylesheet" href="<?php echo $CSS_URL; ?>idlerpg.css">
  <style>
    /* CSS-only hamburger toggle (gold), scoped to mobile */
    .nav-toggle-input { position:absolute; opacity:0; pointer-events:none; }
    .nav-toggle { display:none; color: var(--link); background:none; border:0; cursor:pointer; padding:.25rem; }
    .nav-toggle svg { width:28px; height:28px; display:block; }
    .nav-toggle .bar { stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; transition: transform .2s ease, opacity .2s ease; }

    @media (max-width: 768px) {
      .primary-nav { position: relative; }
      .nav-toggle { display:inline-flex; align-items:center; justify-content:center; }
      .primary-nav .nav-menu {
        position: absolute;
        top: 100%;
        right: 0;
        display: none;
        flex-direction: column;
        background: var(--bg-header);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 6px;
        width: 260px;
        box-shadow: var(--shadow);
        z-index: 1001;
      }
      /* When checkbox is checked, show menu */
      #navToggle:checked ~ .nav-menu { display: flex; }

      /* Animate hamburger -> X when open */
      #navToggle:checked + label .bar1 { transform: translateY(7px) rotate(45deg); }
      #navToggle:checked + label .bar2 { opacity: 0; }
      #navToggle:checked + label .bar3 { transform: translateY(-7px) rotate(-45deg); }
    }
  </style>
</head>
<body>
<header class="site-header">
  <a href="<?php echo $BASEURL; ?>" class="brand" style="display:flex;align-items:center;gap:.6rem;text-decoration:none;">
    <img src="<?php echo $IRPG_LOGO_URL; ?>" class="logo-banner" alt="SorceryNet">
    <strong style="color:#fff">#IdleRPG @ irc.sorcery.net</strong>
  </a>

  <nav class="primary-nav" aria-label="Primary" style="display:flex;align-items:center;gap:.5rem;">
    <!-- Hidden checkbox drives the toggle; label is the gold hamburger -->
    <input type="checkbox" id="navToggle" class="nav-toggle-input" aria-hidden="true">
    <label for="navToggle" class="nav-toggle" id="navButton" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle menu">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <line class="bar bar1" x1="4" y1="7"  x2="20" y2="7"></line>
        <line class="bar bar2" x1="4" y1="12" x2="20" y2="12"></line>
        <line class="bar bar3" x1="4" y1="17" x2="20" y2="17"></line>
      </svg>
    </label>

    <ul id="navMenu" class="nav-menu">
      <?php foreach ($nav as $item):
        $isActive = (isset($active) && $active === $item['key']);
      ?>
        <li class="<?php echo $isActive ? 'active' : ''; ?>">
          <a href="<?php echo $item['href']; ?>" <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
            <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>
</header>

<main role="main">

<script>
  // Optional a11y enhancer: keep aria-expanded in sync (menu works without this)
  (function(){
    var cb  = document.getElementById('navToggle');
    var btn = document.getElementById('navButton');
    if (!cb || !btn) return;
    function sync(){ btn.setAttribute('aria-expanded', cb.checked ? 'true' : 'false'); }
    cb.addEventListener('change', sync);
    sync();

    // Close on Escape / outside click
    document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ cb.checked=false; sync(); }});
    document.addEventListener('click', function(e){
      if (!cb.checked) return;
      if (!e.target.closest('.primary-nav')) { cb.checked=false; sync(); }
    });
  })();
</script>

<?php
/* ============================================================================
   IdleRPG Config (standalone under /idlerpg/)
   Keeps legacy variables for compatibility + adds a few modern helpers.
   Last updated: 2025-08-10
   ============================================================================ */

/* --- Admin / contact --- */
$admin_email = "ops@sorcery.net";
$admin_nick  = "SorceryNET Ops";

/* --- Game identity --- */
$irpg_bot     = "IdleRPG";          // nick of your bot
$irpg_network = "irc.sorcery.net";  // your IRC server
$irpg_chan    = "#IdleRPG";         // your game channel

/* --- Data sources (legacy) --- */
$irpg_db     = "/home/sorcery/idleRPG-bot/irpg.db";        // character database
$irpg_itemdb = "/home/sorcery/idleRPG-bot/mapitems.db";    // item database
$irpg_mod    = "/home/sorcery/idleRPG-bot/modifiers.txt";  // time modifiers file
$irpg_qfile  = "/home/sorcery/idleRPG-bot/questinfo.txt";  // active quest info

/* --- Site paths --- */
$irpg_logo = "idlerpg.png";   // top logo file name (we’ll serve from /idlerpg/assets/img/)
$BASEURL   = "/idlerpg/";     // directory in which this site lives (URL path prefix)

// Map size (fixed)
$mapx = 750;
$mapy = 750;

// Marker sizing
$crosssize = 8;          // circle radius for <area> and cross half-length
$marker_thickness = 3;   // line thickness of crosses

// Optional background map (filesystem path, not URL)
// If set, it will be resampled/cropped to 750×750 automatically.
$MAP_BG_PATH = __DIR__ . '/../assets/img/world_base.png';



/* ============================================================================
   Modern helpers (non-breaking). New pages use these if available.
   ============================================================================ */
$SITE_TITLE = 'IdleRPG @ SorceryNet';

$ASSETS_URL = $BASEURL . 'assets/';
$IMG_URL    = $ASSETS_URL . 'img/';
$CSS_URL    = $ASSETS_URL . 'css/';

/* Computed logo path for new header (keeps $irpg_logo intact for any legacy use) */
$IRPG_LOGO_PATH = $IMG_URL . $irpg_logo;

/* Data dir for optional CSV/log/JSON sources used by the new pages */
$DATA_DIR    = __DIR__ . '/../data';
$LOG_FILE    = $DATA_DIR . '/logs/events.log';  // adjust to point at your live log if you like
$PLAYERS_SRC = $DATA_DIR . '/players.csv';      // optional fallback for players list
$MAP_JSON    = $DATA_DIR . '/map.json';         // optional markers for the new world map

/* Output controls */
$MAX_LOG_LINES = 500;   // how many recent log lines to show on /logs.php
$TABLE_PAGE_SZ = 100;   // future: server-side paging size

/* Sensible defaults */
if (!ini_get('date.timezone')) { date_default_timezone_set('UTC'); }

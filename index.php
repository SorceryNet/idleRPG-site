<?php
// index.php — IdleRPG home with live online/active counts (no logs/quest metric)
require_once __DIR__.'/_inc/config.php';
require_once __DIR__.'/_inc/functions.php';

$irpg_page_title = 'Game Info';
$active = 'home';

// Use channel from config if available; fallback
if (!isset($irpg_chan) || $irpg_chan === '') {
  $irpg_chan = '#IdleRPG';
}

/* -------------------- KPIs -------------------- */
$now = time();
$threshold24h = $now - 86400;

$onlineNow = null; // null => unknown (file missing), int => count
$active24  = null;

if (is_readable($irpg_db) && ($fh = fopen($irpg_db, 'r')) !== false) {
  $onlineNow = 0;
  $active24  = 0;

  fgets($fh, 1024); // skip header
  while (($line = fgets($fh, 4096)) !== false) {
    $p = explode("\t", trim($line));
    $online    = isset($p[8])  ? (int)$p[8]  : 0;   // online flag
    $lastlogin = isset($p[20]) ? (int)$p[20] : 0;   // last login epoch

    if ($online === 1) $onlineNow++;
    if ($online === 1 || $lastlogin >= $threshold24h) $active24++;
  }
  fclose($fh);
}

include __DIR__.'/_inc/header.php';
?>

<section class="card">
  <h1><?php echo htmlspecialchars($irpg_chan, ENT_QUOTES, 'UTF-8'); ?> • IdleRPG</h1>
  <p class="lead">
    The Idle RPG is what it sounds like: an RPG where the players idle.
    Gain levels, find items, and battle — all automatically while you idle.
  </p>

  <div class="kpi mt-2" style="display:flex;gap:1.25rem;flex-wrap:wrap;">
    <div class="metric">
      <div class="value">
        <?php echo ($onlineNow === null) ? '—' : number_format((int)$onlineNow); ?>
      </div>
      <div class="hint">online now</div>
      <?php if ($onlineNow === null): ?>
        <div class="text-muted" style="font-size:.85rem">Cannot read character DB.</div>
      <?php endif; ?>
    </div>

    <div class="metric">
      <div class="value">
        <?php echo ($active24 === null) ? '—' : number_format((int)$active24); ?>
      </div>
      <div class="hint">active characters (24h)</div>
      <?php if ($active24 === null): ?>
        <div class="text-muted" style="font-size:.85rem">Cannot read character DB.</div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="card">
  <h2>Location</h2>
  <p>Play on the SorceryNet IRC Network in <strong><?php echo htmlspecialchars($irpg_chan, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>

  <h2>Register</h2>
  <pre><code>/msg IdleRPG REGISTER &lt;char name&gt; &lt;password&gt; &lt;char class&gt;</code></pre>
  <p class="text-muted">Name ≤ 16 chars, password ≤ 8, class ≤ 30.</p>

  <h2>Login / Logout</h2>
  <pre><code>/msg IdleRPG LOGIN &lt;char name&gt; &lt;password&gt;</code></pre>
  <pre><code>/msg IdleRPG LOGOUT</code></pre>

  <h2>Change Password</h2>
  <pre><code>/msg IdleRPG NEWPASS &lt;new password&gt;</code></pre>

  <h2>Remove Account</h2>
  <pre><code>/msg IdleRPG REMOVEME</code></pre>
</section>

<section class="card">
  <h2>Alignment</h2>
  <pre><code>/msg IdleRPG ALIGN &lt;good|neutral|evil&gt;</code></pre>
  <p class="text-muted">Affects battles and events; neutral is baseline.</p>
</section>

<section class="card">
  <h2>Penalties (shorthand p[num])</h2>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr><th>Action</th><th>Penalty</th></tr>
      </thead>
      <tbody>
        <tr><td>Nick change</td><td>30 * (1.14 ^ level)</td></tr>
        <tr><td>Part</td><td>200 * (1.14 ^ level)</td></tr>
        <tr><td>Quit</td><td>20 * (1.14 ^ level)</td></tr>
        <tr><td>LOGOUT command</td><td>20 * (1.14 ^ level)</td></tr>
        <tr><td>Being Kicked</td><td>250 * (1.14 ^ level)</td></tr>
        <tr><td>Channel privmsg/notice</td><td>[message_length] * (1.14 ^ level)</td></tr>
      </tbody>
    </table>
  </div>
  <p class="text-muted mt-1">Example: level 25 nick change ≈ 793 seconds added.</p>
</section>

<?php include __DIR__.'/_inc/footer.php'; ?>

<?php
// /idlerpg/_inc/footer.php — footer aligned with sorcery.net, with homage to idlerpg.net
?>
  </main>

  <footer class="site-footer">
    <div class="footer-inner" style="max-width:1100px;margin:0 auto;padding:1rem;">
      <!-- Quick links -->
      <nav aria-label="Footer" class="footer-nav" style="margin-bottom:.5rem;">
        <ul class="footer-links" style="list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:.75rem;">
          <li><a href="/getting-started.php">Getting Started</a></li>
          <li><a href="/channels.php">Channels</a></li>
          <li><a href="/idlerpg/">IdleRPG</a></li>
          <li><a href="https://webchat.sorcery.net:9000" target="_blank" rel="noopener">Chat Now</a></li>
          <li><a href="https://status.sorcery.net/status/uptime" target="_blank" rel="noopener">Status</a></li>
          <li><a href="/charter.php">Charter</a></li>
          <li><a href="/privacy.php">Privacy</a></li>
          <li><a href="/terms.php">Terms</a></li>
          <li>
            <!-- Homage badge -->
            <a class="footer-homage" href="https://idlerpg.net" target="_blank" rel="noopener noreferrer"
               title="IdleRPG — the open-source classic this is built on">
              <span class="dot" aria-hidden="true"></span>
              Built on <strong>IdleRPG</strong>
            </a>
          </li>
        </ul>
      </nav>

      <!-- Help + socials -->
      <div class="footer-help" style="margin:.25rem 0 .5rem 0;">
        Need help? Join <a href="https://webchat.sorcery.net:9000/#/connect?join=Square" target="_blank" rel="noopener">#Square</a>.
      </div>
      <div class="footer-social" style="margin-bottom:.5rem; opacity:.9;">
        Follow us:
        <a href="https://discord.gg/vnMrQj9JRT" target="_blank" rel="noopener">Discord</a> ·
        <a href="https://bsky.app/profile/sorcery.net" target="_blank" rel="noopener">Bluesky</a> ·
        <a href="https://www.facebook.com/SorceryNetIRC" target="_blank" rel="noopener">Facebook</a>
      </div>

      <!-- Copyright -->
      <div class="footer-copy" style="opacity:.8;">
        &copy; SorceryNet 1996–<?= date('Y') ?>. All rights reserved.
      </div>
    </div>

    <!-- Minimal Organization schema for SEO -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "SorceryNet",
      "url": "https://sorcery.net",
      "logo": "https://sorcery.net/assets/img/BannerLogo.png",
      "sameAs": [
        "https://bsky.app/profile/sorcery.net",
        "https://www.facebook.com/SorceryNetIRC"
      ]
    }
    </script>
  </footer>

  <style>
    /* Scoped styling for the homage pill (uses your tokens) */
    .site-footer .footer-homage {
      display:inline-flex; align-items:center; gap:.4rem;
      text-decoration:none;
      color: var(--brand-gold);
      background: var(--accent-weak);
      border: 1px solid var(--border);
      border-radius: 9999px;
      padding: .35rem .6rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .site-footer .footer-homage:hover {
      background: var(--accent-mid);
      color: #fff;
    }
    .site-footer .footer-homage .dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--brand-gold); display:inline-block;
    }
  </style>
</body>
</html>

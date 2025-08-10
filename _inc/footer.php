<?php
// _inc/footer.php — site footer with gold homage badge to idlerpg.net
?>
</main>

<footer class="site-footer">
  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.5rem 1rem;">
    <span>&copy; <?php echo date('Y'); ?> SorceryNet</span>
    <span class="text-muted">•</span>
    <a class="footer-homage" href="https://idlerpg.net" target="_blank" rel="noopener noreferrer"
       title="IdleRPG — the open-source classic this is built on">
      <span class="dot" aria-hidden="true"></span>
      Built on <strong>IdleRPG</strong>
    </a>
  </div>
</footer>

<style>
  /* Scoped footer badge styling (uses your design tokens) */
  .site-footer .footer-homage {
    display:inline-flex; align-items:center; gap:.4rem;
    text-decoration:none;
    color: var(--brand-gold);
    background: var(--accent-weak);
    border: 1px solid var(--border);
    border-radius: 9999px;
    padding: .35rem .6rem;
    font-weight: 600;
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

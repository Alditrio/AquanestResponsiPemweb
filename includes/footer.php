<?php require_once __DIR__ . '/ui.php'; $baseUrl = $baseUrl ?? ''; ?>
</main>
</div><!-- /.shell -->
<footer class="site-footer">
  <button type="button" class="scroll-top-btn" data-scroll-top aria-label="Kembali ke atas">&uarr;</button>
  <div class="footer-grid">
    <div class="footer-brand">
      <a href="<?= e($baseUrl) ?>index.php" class="brand"><?= logoMark('brand-mark') ?><span class="brand-text">AQUANEST</span></a>
      <p>Ensiklopedia biota laut Nusantara: spesies, ekosistem, dan cara menjaganya.</p>
    </div>
    <nav class="footer-links" aria-label="Navigasi footer">
      <a href="<?= e($baseUrl) ?>species.php">Katalog</a>
      <a href="<?= e($baseUrl) ?>ecosystems.php">Ekosistem</a>
      <a href="<?= e($baseUrl) ?>tips.php">Tips Konservasi</a>
      <a href="<?= e($baseUrl) ?>education.php">Edukasi</a>
      <a href="<?= e($baseUrl) ?>quiz.php">Kuis</a>
    </nav>
  </div>
  <div class="footer-copy">&copy; <?= date('Y') ?> AQUANEST &middot; Dokumentasi Biota Laut Indonesia</div>
</footer>
<script src="<?= e($baseUrl) ?>assets/js/app.js"></script>
</body>
</html>

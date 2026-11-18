<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$cat = trim($_GET['category'] ?? 'all');

$categories = $pdo->query("SELECT DISTINCT category FROM conservation_tips WHERE is_active = 1 ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
if ($cat !== 'all' && !in_array($cat, $categories, true)) {
    $cat = 'all';
}

$sql = "
    SELECT t.*, s.common_name AS species_name, s.slug AS species_slug, s.image_url AS species_image,
           h.name AS habitat_name, h.slug AS habitat_slug, h.image_url AS habitat_image
    FROM conservation_tips t
    LEFT JOIN species s ON s.id = t.related_species_id
    LEFT JOIN habitats h ON h.id = t.related_habitat_id
    WHERE t.is_active = 1
";
$params = [];
if ($cat !== 'all') {
    $sql .= ' AND t.category = ?';
    $params[] = $cat;
}
$sql .= ' ORDER BY t.sort_order ASC, t.id ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tips = $stmt->fetchAll();

$currentPage = 'tips';
$pageTitle = 'Tips Konservasi Laut – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="kicker">
      <span>Tips Konservasi</span>
      <?= icoTitleWave('kicker-wave') ?>
    </div>
    <h1>CARA SEDERHANA MENJAGA LAUT</h1>
    <p style="color:var(--muted);max-width:620px;">Langkah nyata dan kebiasaan kecil di rumah, di pesisir, dan saat berwisata bahari demi kelestarian laut Nusantara.</p>
  </div>
</section>

<section class="filter-bar">
  <div class="chip-row">
    <a href="tips.php" class="chip <?= $cat === 'all' ? 'active' : '' ?>">Semua</a>
    <?php foreach ($categories as $c): ?>
      <a href="tips.php?category=<?= urlencode($c) ?>" class="chip <?= $cat === $c ? 'active' : '' ?>"><?= e($c) ?></a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section-pad">
  <div class="container">
    <?php if (!$tips): ?>
      <p style="color:var(--muted);">Belum ada tips untuk kategori ini.</p>
    <?php else: ?>
    <div class="tip-grid cols-3">
      <?php foreach ($tips as $t): ?>
      <article class="tip-card" id="tip-<?= (int)$t['id'] ?>">
        <div class="tip-card-media">
          <img src="<?= e(tipImage($t)) ?>" alt="<?= e($t['title']) ?>" loading="lazy">
          <div class="tip-card-badges">
            <span class="pill pill-cat"><?= e($t['category']) ?></span>
            <span class="pill pill-diff lvl-<?= strtolower(e($t['difficulty'])) ?>"><?= e($t['difficulty']) ?></span>
          </div>
        </div>
        <div class="tip-card-body">
          <h3 class="tip-card-title"><?= e($t['title']) ?></h3>
          <p class="tip-card-desc"><?= e($t['summary']) ?></p>
          <?php if ($t['species_name'] || $t['habitat_name']): ?>
          <div class="tip-card-footer">
            <?php if ($t['species_name']): ?>
              <a href="species-detail.php?slug=<?= urlencode($t['species_slug']) ?>" class="tip-tag-link">
                <span class="tag-ico">🐟</span>
                <span class="tag-label"><?= e($t['species_name']) ?></span>
              </a>
            <?php elseif ($t['habitat_name']): ?>
              <a href="species.php?habitat=<?= urlencode($t['habitat_slug']) ?>" class="tip-tag-link">
                <span class="tag-ico">🌊</span>
                <span class="tag-label"><?= e($t['habitat_name']) ?></span>
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

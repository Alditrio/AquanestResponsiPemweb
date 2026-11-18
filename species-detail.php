<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$slug = trim($_GET['slug'] ?? '');

$stmt = $pdo->prepare("
    SELECT s.*, c.name AS category_name, h.name AS habitat_name, cs.code AS status_code, cs.label_id AS status_label_id, cs.description AS status_desc
    FROM species s
    JOIN categories c ON c.id = s.category_id
    JOIN habitats h ON h.id = s.habitat_id
    JOIN conservation_status cs ON cs.id = s.conservation_status_id
    WHERE s.slug = ?
");
$stmt->execute([$slug]);
$sp = $stmt->fetch();

if (!$sp) {
    header('Location: species.php');
    exit;
}

$relStmt = $pdo->prepare("SELECT * FROM species WHERE habitat_id = ? AND id != ? LIMIT 4");
$relStmt->execute([$sp['habitat_id'], $sp['id']]);
$related = $relStmt->fetchAll();

$tipStmt = $pdo->prepare("SELECT * FROM conservation_tips WHERE is_active = 1 AND (related_species_id = ? OR related_habitat_id = ?) ORDER BY (related_species_id = ?) DESC, sort_order ASC LIMIT 3");
$tipStmt->execute([$sp['id'], $sp['habitat_id'], $sp['id']]);
$relatedTips = $tipStmt->fetchAll();

$currentPage = 'catalog';
$pageTitle = $sp['common_name'] . ' – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="section-pad">
  <div class="container">
    <p style="margin-bottom:20px;">
      <a href="species.php" style="color:var(--brand);font-weight:600;font-size:14px;">&larr; Kembali ke Katalog Spesies</a>
    </p>

    <div class="hero-grid" style="align-items:start;">
      <div class="hero-frame">
        <img src="<?= e(ltrim($sp['image_url'], '/')) ?>" alt="<?= e($sp['common_name']) ?>">
      </div>

      <div>
        <div class="kicker"><?= e($sp['category_name']) ?> &middot; <?= e($sp['habitat_name']) ?></div>
        <h1 style="font-size:36px;color:var(--ink);"><?= e($sp['common_name']) ?></h1>
        <p style="font-size:18px;font-style:italic;color:var(--muted);margin-bottom:14px;"><?= e($sp['scientific_name']) ?></p>
        <p style="color:var(--muted);margin-bottom:22px;"><?= e($sp['summary']) ?></p>

        <table class="data-table" style="background:var(--cream);border-radius:16px;overflow:hidden;">
          <tr>
            <th>Status IUCN</th>
            <td>
              <?= statusBadge($sp['status_code'], $sp['status_label_id']) ?>
              <span style="font-size:13px;color:var(--muted);margin-left:8px;"><?= e($sp['status_desc']) ?></span>
            </td>
          </tr>
          <tr>
            <th>Kedalaman</th>
            <td>
              <span class="depth-info" style="font-size:14px;">
                <svg class="ico-depth" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 17l-5 5-5-5M7 7l5-5 5 5"/></svg>
                <span class="depth-label">Kedalaman:</span>
                <strong class="depth-val"><?= (int)$sp['depth_min_m'] ?> &ndash; <?= (int)$sp['depth_max_m'] ?> m</strong>
                <span style="font-size:12px;color:var(--muted);margin-left:4px;">(rentang jelajah di bawah permukaan)</span>
              </span>
            </td>
          </tr>
          <tr>
            <th>Ukuran Tubuh (Maks)</th>
            <td class="mono">
              <span style="display:inline-flex;align-items:center;gap:6px;">
                <svg style="width:16px;height:16px;color:var(--blueberry);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20M19 9l3 3-3 3M5 9l-3 3 3 3"/></svg>
                <span>Panjang fisik tubuh:</span>
                <strong><?= (int)$sp['length_cm'] ?> cm</strong>
              </span>
            </td>
          </tr>
          <tr><th>Pola Makan</th><td><?= e($sp['diet']) ?></td></tr>
          <tr><th>Sebaran Indonesia</th><td><?= e($sp['distribution_indonesia']) ?></td></tr>
        </table>
      </div>
    </div>

    <div style="margin-top:40px;padding-top:32px;border-top:1px solid var(--line);" class="hero-grid">
      <div>
        <h2 style="font-size:22px;font-weight:700;color:var(--ink);margin-bottom:12px;">Deskripsi Biologi &amp; Perilaku</h2>
        <p style="color:var(--muted);white-space:pre-line;"><?= e($sp['description']) ?></p>
      </div>
      <div style="background:var(--cream);border:1px solid var(--line);border-radius:18px;padding:24px;">
        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:10px;">Catatan Konservasi Nusantara</h2>
        <p style="font-size:14px;color:var(--muted);"><?= e($sp['conservation_notes']) ?></p>
      </div>
    </div>

    <?php if ($relatedTips): ?>
    <div style="margin-top:48px;">
      <h2 style="font-size:20px;margin-bottom:18px;">Tips konservasi untuk <?= e($sp['common_name']) ?></h2>
      <div class="tip-grid cols-3">
        <?php foreach ($relatedTips as $t): ?>
        <a href="tips.php?category=<?= urlencode($t['category']) ?>#tip-<?= (int)$t['id'] ?>" class="tip-card">
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
            <span class="tip-card-link">Baca Panduan &rarr;</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($related): ?>
    <div style="margin-top:48px;">
      <h2 style="font-size:20px;font-weight:700;color:var(--ink);margin-bottom:18px;">Biota Lain di <?= e($sp['habitat_name']) ?></h2>
      <div class="card-4grid">
        <?php foreach ($related as $rel): ?>
        <a href="species-detail.php?slug=<?= urlencode($rel['slug']) ?>" class="catalog-card">
          <img src="<?= e(ltrim($rel['image_url'], '/')) ?>" alt="<?= e($rel['common_name']) ?>">
          <div class="catalog-body">
            <strong style="font-size:15px;color:var(--ink);"><?= e($rel['common_name']) ?></strong>
            <div style="font-size:12px;font-style:italic;color:var(--muted);"><?= e($rel['scientific_name']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

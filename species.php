<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? 'all');
$hab = trim($_GET['habitat'] ?? 'all');
$status = trim($_GET['status'] ?? 'all');

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(s.common_name LIKE ? OR s.scientific_name LIKE ? OR s.distribution_indonesia LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($cat !== 'all') {
    $where[] = 'c.slug = ?';
    $params[] = $cat;
}
if ($hab !== 'all') {
    $where[] = 'h.slug = ?';
    $params[] = $hab;
}
if ($status !== 'all') {
    $where[] = 'cs.code = ?';
    $params[] = $status;
}

$sql = "
    SELECT s.*, c.name AS category_name, h.name AS habitat_name, cs.code AS status_code, cs.label_id AS status_label_id
    FROM species s
    JOIN categories c ON c.id = s.category_id
    JOIN habitats h ON h.id = s.habitat_id
    JOIN conservation_status cs ON cs.id = s.conservation_status_id
";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY s.is_featured DESC, s.id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$speciesList = $stmt->fetchAll();

$categories = $pdo->query('SELECT * FROM categories ORDER BY id ASC')->fetchAll();
$habitats = $pdo->query('SELECT * FROM habitats ORDER BY id ASC')->fetchAll();
$statuses = $pdo->query('SELECT * FROM conservation_status ORDER BY severity_level DESC')->fetchAll();

$currentPage = 'catalog';
$pageTitle = 'Katalog Spesies – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="kicker" style="color:var(--brand);">Arsip Taksonomi Nusantara</div>
    <h1 style="font-size:34px;">Katalog Spesies Biota Laut Indonesia</h1>
    <p style="color:var(--muted);margin-top:8px;">Menampilkan <?= count($speciesList) ?> spesies terdokumentasi dari database MySQL.</p>
  </div>
</section>

<section class="filter-bar">
  <div class="container">
    <form method="GET" action="species.php" class="form-row">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama spesies, ilmiah, atau lokasi..." class="input" data-live-search>
      <select name="category" class="select">
        <option value="all">Semua Kategori</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="habitat" class="select">
        <option value="all">Semua Ekosistem</option>
        <?php foreach ($habitats as $h): ?>
          <option value="<?= e($h['slug']) ?>" <?= $hab === $h['slug'] ? 'selected' : '' ?>><?= e($h['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status" class="select">
        <option value="all">Semua Status IUCN</option>
        <?php foreach ($statuses as $st): ?>
          <option value="<?= e($st['code']) ?>" <?= $status === $st['code'] ? 'selected' : '' ?>><?= e($st['code']) ?> &mdash; <?= e($st['label_id']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Filter</button>
    </form>
  </div>
</section>

<section class="section-pad">
  <div class="container">
    <div class="card-3catalog">
      <?php foreach ($speciesList as $sp): ?>
      <a href="species-detail.php?slug=<?= urlencode($sp['slug']) ?>" class="catalog-card">
        <div>
          <img src="<?= e(ltrim($sp['image_url'], '/')) ?>" alt="<?= e($sp['common_name']) ?>">
          <div class="catalog-body">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;flex-wrap:wrap;">
              <span style="font-size:12px;color:var(--muted);font-weight:600;"><?= e($sp['habitat_name']) ?></span>
              <?= statusBadge($sp['status_code'], $sp['status_label_id']) ?>
            </div>
            <h2 style="font-size:18px;font-weight:700;color:var(--ink);"><?= e($sp['common_name']) ?></h2>
            <div style="font-size:12px;font-style:italic;color:var(--muted);margin-bottom:8px;"><?= e($sp['scientific_name']) ?></div>
            <p style="font-size:13px;color:var(--muted);"><?= e($sp['summary']) ?></p>
          </div>
        </div>
        <div class="catalog-foot">
          <span class="depth-info" title="Kedalaman habitat">
            <svg class="ico-depth" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v20M17 17l-5 5-5-5M7 7l5-5 5 5"/></svg>
            <span class="depth-label">Kedalaman:</span>
            <strong class="depth-val"><?= (int)$sp['depth_min_m'] ?>&ndash;<?= (int)$sp['depth_max_m'] ?> m</strong>
          </span>
          <strong class="catalog-detail-link">Detail &rarr;</strong>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

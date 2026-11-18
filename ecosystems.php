<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$habitats = $pdo->query("
    SELECT h.*, (SELECT COUNT(*) FROM species s WHERE s.habitat_id = h.id) AS species_count
    FROM habitats h ORDER BY h.id ASC
")->fetchAll();

$currentPage = 'ecosystems';
$pageTitle = 'Ekosistem Laut Nusantara – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="kicker" style="color:var(--brand);">Zona Ekologi Perairan</div>
    <h1 style="font-size:34px;">Ekosistem Laut Nusantara</h1>
    <p style="color:var(--muted);margin-top:8px;">Terumbu karang, padang lamun, hutan mangrove, dan laut dalam Nusantara.</p>
  </div>
</section>

<section class="section-pad">
  <div class="container" style="display:grid;gap:32px;">
    <?php foreach ($habitats as $hab): ?>
    <article class="hero-grid" style="background:var(--cream);border:1px solid var(--line);border-radius:24px;padding:28px;">
      <div class="hero-frame">
        <img src="<?= e(ltrim($hab['image_url'], '/')) ?>" alt="<?= e($hab['name']) ?>">
      </div>
      <div>
        <div class="kicker">Kedalaman <?= e($hab['depth_range']) ?> &middot; Suhu <?= e($hab['temperature_range']) ?></div>
        <h2 style="font-size:26px;color:var(--ink);margin-bottom:10px;"><?= e($hab['name']) ?></h2>
        <p style="color:var(--muted);margin-bottom:14px;"><?= e($hab['description']) ?></p>
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px;"><strong>Lokasi Utama:</strong> <?= e($hab['key_locations']) ?></p>
        <a href="species.php?habitat=<?= urlencode($hab['slug']) ?>" class="btn btn-primary btn-sm">
          Lihat <?= (int)$hab['species_count'] ?> Spesies Penghuni &rarr;
        </a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

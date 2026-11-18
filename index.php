<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$featured = $pdo->query("
    SELECT s.*, h.name AS habitat_name, cs.code AS status_code, cs.label_id AS status_label_id
    FROM species s
    JOIN habitats h ON h.id = s.habitat_id
    JOIN conservation_status cs ON cs.id = s.conservation_status_id
    ORDER BY s.is_featured DESC, s.id ASC
    LIMIT 8
")->fetchAll();
$habitats = $pdo->query("SELECT * FROM habitats ORDER BY id ASC")->fetchAll();
$tips = $pdo->query("
    SELECT t.*, s.common_name AS species_name, s.slug AS species_slug, s.image_url AS species_image,
           h.name AS habitat_name, h.slug AS habitat_slug, h.image_url AS habitat_image
    FROM conservation_tips t
    LEFT JOIN species s ON s.id = t.related_species_id
    LEFT JOIN habitats h ON h.id = t.related_habitat_id
    WHERE t.is_active = 1
    ORDER BY t.sort_order ASC, t.id ASC
    LIMIT 4
")->fetchAll();

$totalSpecies = (int)$pdo->query('SELECT COUNT(*) FROM species')->fetchColumn();
$totalHabitats = count($habitats);
$totalTips = (int)$pdo->query('SELECT COUNT(*) FROM conservation_tips WHERE is_active = 1')->fetchColumn();

$heroHab = $habitats[0] ?? null;
$aboutHab = $habitats[1] ?? $heroHab;
$quoteHab = $habitats[2] ?? $heroHab;

$img = fn(?array $row) => $row ? e(ltrim($row['image_url'], '/')) : '';

$currentPage = 'home';
$pageTitle = 'AQUANEST – Ensiklopedia Biota Laut Nusantara';
require __DIR__ . '/includes/header.php';
?>

<!-- ==========================================================
     HERO SECTION (Pupjoy Poster Style with Dominant Text)
     ========================================================== -->
<section class="hero-pupjoy-wrap">
  <div class="hero-mega-headline">
    AQUA<span class="accent-blue">NEST</span>
  </div>

  <div class="hero-subhead-bar">
    <div class="hero-tagline-text">
      ENSIKLOPEDIA BIOTA LAUT INDONESIA &middot; JELAJAHI &bull; KENALI &bull; LINDUNGI
    </div>
    <div class="hero-trust-pill">
      <div class="avatar-stack-mini">
        <img src="assets/images/species/penyu-hijau.jpg" alt="Penyu Hijau">
        <img src="assets/images/species/ikan-badut.jpg" alt="Ikan Badut">
        <img src="assets/images/species/lumba-lumba.jpg" alt="Lumba-lumba">
      </div>
      <span><b><?= $totalSpecies ?>+</b> Spesies Dilindungi</span>
    </div>
  </div>

  <!-- Main Hero Card (Pupjoy Style) -->
  <div class="hero-showcase-card">
    <img src="assets/images/hero-ocean-fresh.jpg" class="hero-card-bg-photo" alt="Keindahan Bawah Laut Nusantara">
    <div class="hero-card-overlay"></div>

    <!-- Floating Cute Badges (NO AI STARS) -->
    <div class="floating-cute-badge top-left">
      <span class="badge-emoji">🐢</span>
      <span>100% Satwa Laut Nusantara</span>
    </div>
    <div class="floating-cute-badge top-right">
      <span class="badge-emoji">🌊</span>
      <span>Status Konservasi IUCN</span>
    </div>

    <!-- Animated Floating Sea Bubbles -->
    <span class="sea-bubble b1"></span>
    <span class="sea-bubble b2"></span>
    <span class="sea-bubble b3"></span>
    <span class="sea-bubble b4"></span>

    <!-- Floating Circle Button CTA (Pupjoy Style) -->
    <a href="species.php" class="hero-circle-cta" aria-label="Jelajahi Katalog">
      <span>JELAJAHI</span>
      <span>KATALOG</span>
    </a>

    <!-- Animals Cutout Stage -->
    <div class="hero-animals-stage">
      <div class="hero-species-cutout">
        <img src="assets/images/species/penyu-hijau.jpg" style="border-radius: 28px; border: 4px solid #fff; max-height: 280px; aspect-ratio: 4/3; object-fit: cover;" alt="Penyu Hijau">
        <div class="cutout-caption">PENYU HIJAU</div>
      </div>
      <div class="hero-species-cutout">
        <img src="assets/images/species/lumba-lumba.jpg" style="border-radius: 28px; border: 4px solid #fff; max-height: 330px; aspect-ratio: 4/3; object-fit: cover;" alt="Lumba-lumba">
        <div class="cutout-caption">LUMBA-LUMBA</div>
      </div>
      <div class="hero-species-cutout">
        <img src="assets/images/species/ikan-badut.jpg" style="border-radius: 28px; border: 4px solid #fff; max-height: 280px; aspect-ratio: 4/3; object-fit: cover;" alt="Ikan Badut">
        <div class="cutout-caption">IKAN BADUT</div>
      </div>
    </div>
  </div>

  <!-- Hero Bottom Action Buttons -->
  <div class="hero-bottom-actions">
    <a href="species.php" class="btn btn-hero-main">Jelajahi Semua Spesies <?= icoArrow() ?></a>
    <a href="quiz.php" class="btn btn-hero-quiz"><span class="circle-btn" style="width: 32px; height: 32px; margin-right: 6px;"><?= icoPlay() ?></span> Mulai Kuis Interaktif</a>
  </div>
</section>

<!-- ==========================================================
     PERSONALIZED PICKS / SPESIES IKONIK (Pupjoy Style Cards)
     ========================================================== -->
<section class="picks-section">
  <div class="container">
    <div class="section-headline-wrap">
      <div class="eyebrow" style="margin-bottom: 8px;"><span>PILIHAN TERBAIK MINGGU INI</span><?= icoTitleWave('eyebrow-wave') ?></div>
      <h2 class="section-mega-title">SPESIES IKONIK NUSANTARA</h2>
      <p class="section-subtitle">BIOTA LAUT POPULER DENGAN INFORMASI KEDALAMAN &amp; STATUS PERLINDUNGAN</p>
    </div>

    <div class="picks-grid">
      <!-- Card 1: Penyu Hijau (Reef Gold) -->
      <a href="species-detail.php?slug=penyu-hijau" class="pick-card theme-gold">
        <div class="pick-card-top">
          <span class="pick-tag">Padang Lamun</span>
          <?= statusBadge('EN', 'Terancam') ?>
        </div>
        <div class="pick-portrait-frame">
          <img src="assets/images/species/penyu-hijau.jpg" alt="Penyu Hijau">
        </div>
        <div class="pick-card-body">
          <h3 class="pick-title">PENYU HIJAU</h3>
          <div class="pick-scientific">Chelonia mydas</div>
          <div class="pick-depth-pill">
            <svg style="width:13px;height:13px;display:inline-block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 17l-5 5-5-5M7 7l5-5 5 5"/></svg>
            Kedalaman: 1 &ndash; 40 m
          </div>
          <div class="pick-action-btn">Buka Profil Biota &rarr;</div>
        </div>
      </a>

      <!-- Card 2: Ikan Badut (Cobalt Blue) -->
      <a href="species-detail.php?slug=ikan-badut" class="pick-card theme-blue">
        <div class="pick-card-top">
          <span class="pick-tag">Terumbu Karang</span>
          <?= statusBadge('LC', 'Risiko Rendah') ?>
        </div>
        <div class="pick-portrait-frame">
          <img src="assets/images/species/ikan-badut.jpg" alt="Ikan Badut">
        </div>
        <div class="pick-card-body">
          <h3 class="pick-title">IKAN BADUT</h3>
          <div class="pick-scientific">Amphiprion ocellaris</div>
          <div class="pick-depth-pill">
            <svg style="width:13px;height:13px;display:inline-block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 17l-5 5-5-5M7 7l5-5 5 5"/></svg>
            Kedalaman: 1 &ndash; 15 m
          </div>
          <div class="pick-action-btn">Buka Profil Biota &rarr;</div>
        </div>
      </a>

      <!-- Card 3: Pari Manta (Navy Ocean) -->
      <a href="species-detail.php?slug=pari-manta" class="pick-card theme-navy">
        <div class="pick-card-top">
          <span class="pick-tag">Laut Dalam</span>
          <?= statusBadge('EN', 'Terancam') ?>
        </div>
        <div class="pick-portrait-frame">
          <img src="assets/images/species/pari-manta.jpg" alt="Pari Manta">
        </div>
        <div class="pick-card-body">
          <h3 class="pick-title">PARI MANTA</h3>
          <div class="pick-scientific">Mobula birostris</div>
          <div class="pick-depth-pill">
            <svg style="width:13px;height:13px;display:inline-block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 17l-5 5-5-5M7 7l5-5 5 5"/></svg>
            Kedalaman: 0 &ndash; 30 m
          </div>
          <div class="pick-action-btn">Buka Profil Biota &rarr;</div>
        </div>
      </a>

      <!-- Card 4: Lumba-lumba (Coral Salmon) -->
      <a href="species-detail.php?slug=lumba-lumba" class="pick-card theme-coral">
        <div class="pick-card-top">
          <span class="pick-tag">Laut Lepas</span>
          <?= statusBadge('LC', 'Risiko Rendah') ?>
        </div>
        <div class="pick-portrait-frame">
          <img src="assets/images/species/lumba-lumba.jpg" alt="Lumba-lumba">
        </div>
        <div class="pick-card-body">
          <h3 class="pick-title">LUMBA-LUMBA</h3>
          <div class="pick-scientific">Tursiops truncatus</div>
          <div class="pick-depth-pill">
            <svg style="width:13px;height:13px;display:inline-block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 17l-5 5-5-5M7 7l5-5 5 5"/></svg>
            Kedalaman: 0 &ndash; 300 m
          </div>
          <div class="pick-action-btn">Buka Profil Biota &rarr;</div>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- ==========================================================
     TENTANG AQUANEST (Petpy Style: Clean Card, Stickers, Big Stats)
     ========================================================== -->
<section class="about-petpy-section">
  <div class="container">
    <div class="about-petpy-card">
      <div class="about-content-left">
        <div class="eyebrow"><span>TENTANG AQUANEST</span><?= icoTitleWave('eyebrow-wave') ?></div>
        <h2>ENSIKLOPEDIA LAUT UNTUK SEMUA GENERASI</h2>
        <p>
          AQUANEST adalah portal ensiklopedia digital biota laut Indonesia yang didedikasikan untuk mendokumentasikan kekayaan hayati samudra Nusantara, memahami ekosistem karang, dan mengajak masyarakat ikut menjaga kelestariannya.
        </p>

        <!-- Cute Marine Stickers Row (NO AI STARS) -->
        <div class="cute-stickers-row">
          <div class="cute-sticker"><span class="st-emoji">🐢</span><span>Sahabat Penyu</span></div>
          <div class="cute-sticker"><span class="st-emoji">🐠</span><span>Terumbu Karang</span></div>
          <div class="cute-sticker"><span class="st-emoji">🐬</span><span>Bebas Plastik</span></div>
          <div class="cute-sticker"><span class="st-emoji">🇮🇩</span><span>100% Nusantara</span></div>
        </div>

        <!-- 3 Big Colorful Stats (Anton Font) -->
        <div class="petpy-stats-grid">
          <div class="stat-block blue">
            <b><?= $totalSpecies ?></b>
            <span>Spesies Terdata</span>
          </div>
          <div class="stat-block gold">
            <b><?= $totalHabitats ?></b>
            <span>Ekosistem Laut</span>
          </div>
          <div class="stat-block coral">
            <b><?= $totalTips ?></b>
            <span>Aksi Konservasi</span>
          </div>
        </div>
      </div>

      <!-- Right Media Collage -->
      <div class="about-media-collage">
        <div class="collage-main-img">
          <img src="assets/images/habitats/padang-lamun.jpg" alt="Habitat Padang Lamun">
        </div>
        <div class="collage-sub-row">
          <div class="collage-sub-item">
            <img src="assets/images/species/hiu-paus.jpg" alt="Hiu Paus">
          </div>
          <div class="collage-sub-item">
            <img src="assets/images/species/kuda-laut.jpg" alt="Kuda Laut">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ==========================================================
     PETA SEBARAN BIOTA LAUT ENDEMIK & KHAS NUSANTARA
     ========================================================== -->
<?php require __DIR__ . '/includes/endemic-map.php'; ?>

<!-- ==========================================================
     SPESIES PILIHAN CAROUSEL
     ========================================================== -->
<div class="container" style="margin-top: 50px;">
  <div class="section-headline-wrap" style="text-align: left; margin-bottom: 24px;">
    <div class="eyebrow"><span>KATALOG PILIHAN</span><?= icoTitleWave('eyebrow-wave') ?></div>
    <h2 class="section-mega-title">JELAJAHI BIOTA LAUT LAINNYA</h2>
  </div>
</div>
<section class="feat" style="padding-top: 0;">
  <div class="feat-wrap" data-carousel>
    <div class="feat-track" data-track>
      <?php foreach ($featured as $sp): ?>
      <a href="species-detail.php?slug=<?= urlencode($sp['slug']) ?>" class="feat-card">
        <span style="position:absolute;top:12px;right:12px;z-index:2;"><?= statusBadge($sp['status_code'], $sp['status_label_id'] ?? null) ?></span>
        <img src="<?= $img($sp) ?>" alt="<?= e($sp['common_name']) ?>">
        <div class="cap">
          <b><?= e($sp['common_name']) ?></b>
          <small><?= e($sp['scientific_name']) ?></small>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <button type="button" class="circle-btn feat-next" data-next aria-label="Berikutnya"><?= icoArrow() ?></button>
  </div>
</section>

<!-- ==========================================================
     TIPS KONSERVASI
     ========================================================== -->
<?php if ($tips): ?>
<section class="tips-panel">
  <div class="tips-head">
    <div>
      <div class="eyebrow">
        <span>TIPS KONSERVASI</span>
        <?= icoTitleWave('eyebrow-wave') ?>
      </div>
      <h2>LANGKAH KECIL, DAMPAK BESAR UNTUK LAUT</h2>
    </div>
    <a href="tips.php" class="btn btn-outline btn-sm">Semua Panduan <?= icoArrow() ?></a>
  </div>
  <div class="tip-grid">
    <?php foreach ($tips as $t): ?>
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
</section>
<?php endif; ?>

<!-- ==========================================================
     KUTIPAN SAMUDRA
     ========================================================== -->
<section class="quote">
  <div class="quote-photo">
    <img src="assets/images/quote-ocean-fresh.jpg" alt="Pristine Indonesian Ocean Waters">
  </div>
  <div class="quote-body">
    <div class="quote-mark">&ldquo;</div>
    <h2>LAUT YANG SEHAT DIMULAI DARI LANGKAH KECIL KITA BERSAMA.</h2>
    <a href="education.php">Pelajari Lebih Lanjut <?= icoArrow() ?></a>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

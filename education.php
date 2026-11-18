<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$slug = trim($_GET['article'] ?? '');
$article = null;
if ($slug !== '') {
    $stmt = $pdo->prepare('SELECT * FROM educational_materials WHERE slug = ?');
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
}
$materials = $pdo->query('SELECT * FROM educational_materials ORDER BY id DESC')->fetchAll();

$currentPage = 'education';
$pageTitle = 'Materi Edukasi – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="kicker" style="color:var(--brand);">Literasi Kelautan</div>
    <h1 style="font-size:34px;">Materi Edukasi &amp; Konservasi Laut</h1>
  </div>
</section>

<section class="section-pad">
  <div class="container">
    <?php if ($article): ?>
      <p style="margin-bottom:20px;"><a href="education.php" style="color:var(--brand);font-weight:600;">&larr; Kembali ke Semua Materi</a></p>
      <article style="max-width:780px;margin:0 auto;">
        <div class="kicker"><?= e($article['topic']) ?> &middot; <?= (int)$article['reading_minutes'] ?> menit baca &middot; <?= e($article['author_name']) ?></div>
        <h2 style="font-size:32px;color:var(--ink);margin-bottom:14px;"><?= e($article['title']) ?></h2>
        <img src="<?= e(ltrim($article['image_url'], '/')) ?>" alt="<?= e($article['title']) ?>" style="border-radius:20px;margin-bottom:24px;aspect-ratio:16/9;object-fit:cover;">
        <div style="color:var(--muted);white-space:pre-line;font-size:16px;line-height:1.8;"><?= e($article['content']) ?></div>
      </article>
    <?php else: ?>
      <div class="card-3catalog" style="grid-template-columns:repeat(2,1fr);">
        <?php foreach ($materials as $mat): ?>
        <a href="education.php?article=<?= urlencode($mat['slug']) ?>" class="catalog-card">
          <div>
            <img src="<?= e(ltrim($mat['image_url'], '/')) ?>" alt="<?= e($mat['title']) ?>">
            <div class="catalog-body">
              <div style="font-size:12px;color:var(--accent-dark);font-weight:600;margin-bottom:4px;">
                <?= e($mat['topic']) ?> &middot; <?= (int)$mat['reading_minutes'] ?> mnt
              </div>
              <h2 style="font-size:19px;font-weight:700;color:var(--ink);margin-bottom:8px;"><?= e($mat['title']) ?></h2>
              <p style="font-size:14px;color:var(--muted);"><?= e($mat['summary']) ?></p>
            </div>
          </div>
          <div class="catalog-foot">
            <span><?= e($mat['author_name']) ?></span>
            <strong style="color:var(--brand);">Baca Lengkap &rarr;</strong>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/ui.php';
$user = currentUser();
$currentPage = $currentPage ?? 'home';
$pageTitle = $pageTitle ?? 'AQUANEST – Ensiklopedia Biota Laut Nusantara';
$baseUrl = $baseUrl ?? '';
$nav = [
  'home'       => ['index.php', 'Beranda'],
  'map'        => ['index.php#peta', 'Peta Endemik'],
  'catalog'    => ['species.php', 'Katalog'],
  'ecosystems' => ['ecosystems.php', 'Ekosistem'],
  'tips'       => ['tips.php', 'Konservasi'],
  'education'  => ['education.php', 'Edukasi'],
  'quiz'       => ['quiz.php', 'Kuis'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="Ensiklopedia digital biota laut Indonesia: katalog spesies, ekosistem, tips konservasi, materi edukasi, dan kuis.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e($baseUrl) ?>assets/css/style.css">
</head>
<body>
<div class="shell">
<header class="topbar">
  <a href="<?= e($baseUrl) ?>index.php" class="brand" aria-label="AQUANEST – Beranda">
    <?= logoMark('brand-mark') ?><span class="brand-text">AQUANEST</span>
  </a>

  <nav class="nav-links" id="nav-links" aria-label="Navigasi utama">
    <?php foreach ($nav as $key => [$href, $label]): ?>
      <a href="<?= e($baseUrl . $href) ?>" class="<?= $currentPage === $key ? 'active' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
    <div class="nav-cta-mobile">
      <?php if ($user): ?>
        <?php if ($user['role'] === 'admin'): ?><a href="<?= e($baseUrl) ?>admin/dashboard.php">Dashboard</a><?php endif; ?>
        <a href="<?= e($baseUrl) ?>logout.php">Keluar</a>
      <?php else: ?>
        <a href="<?= e($baseUrl) ?>login.php">Masuk</a>
      <?php endif; ?>
    </div>
  </nav>

  <div class="nav-actions">
    <?php if ($user): ?>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="<?= e($baseUrl) ?>admin/dashboard.php" class="btn btn-ghost btn-sm">Dashboard</a>
      <?php endif; ?>
      <a href="<?= e($baseUrl) ?>logout.php" class="btn btn-primary btn-sm">Keluar</a>
    <?php else: ?>
      <a href="<?= e($baseUrl) ?>login.php" class="btn btn-primary btn-sm">Masuk <?= icoArrow() ?></a>
    <?php endif; ?>
    <button type="button" class="nav-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="nav-links" data-nav-toggle>&#9776;</button>
  </div>
</header>
<main>

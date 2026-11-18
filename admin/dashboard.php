<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ui.php';

requireAdmin();
$pdo = getPDO();
$msg = '';
$err = '';

function mb_substr_safe(string $text, int $max): string {
    return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

function handleUpload(string $fieldName, string $fallback): string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return $fallback;
    }
    $file = $_FILES[$fieldName];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran foto maksimal 5 MB.');
    }
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = 'upload-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    move_uploaded_file($file['tmp_name'], $dir . '/' . $name);
    return 'uploads/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $err = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'save_species') {
                $id = (int)($_POST['id'] ?? 0);
                $img = handleUpload('photo', trim($_POST['image_url'] ?? 'assets/images/species/karang-keras.jpg'));
                $common = trim($_POST['common_name']);
                $sci = trim($_POST['scientific_name']);
                $slug = slugify($common) . ($id ? '' : '-' . substr((string)time(), -3));
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE species SET common_name=?, scientific_name=?, category_id=?, habitat_id=?, conservation_status_id=?, depth_min_m=?, depth_max_m=?, length_cm=?, diet=?, distribution_indonesia=?, summary=?, description=?, conservation_notes=?, image_url=? WHERE id=?");
                    $stmt->execute([$common, $sci, (int)$_POST['category_id'], (int)$_POST['habitat_id'], (int)$_POST['conservation_status_id'], (int)$_POST['depth_min_m'], (int)$_POST['depth_max_m'], (int)$_POST['length_cm'], $_POST['diet'], $_POST['distribution_indonesia'], $_POST['summary'], $_POST['description'], $_POST['conservation_notes'], $img, $id]);
                    $msg = 'Data spesies diperbarui.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO species (common_name, scientific_name, slug, category_id, habitat_id, conservation_status_id, depth_min_m, depth_max_m, length_cm, diet, distribution_indonesia, summary, description, conservation_notes, image_url, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)");
                    $stmt->execute([$common, $sci, $slug, (int)$_POST['category_id'], (int)$_POST['habitat_id'], (int)$_POST['conservation_status_id'], (int)$_POST['depth_min_m'], (int)$_POST['depth_max_m'], (int)$_POST['length_cm'], $_POST['diet'], $_POST['distribution_indonesia'], $_POST['summary'], $_POST['description'], $_POST['conservation_notes'], $img]);
                    $msg = 'Spesies baru ditambahkan.';
                }
            } elseif ($action === 'delete_species') {
                $pdo->prepare('DELETE FROM species WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Spesies dihapus.';
            } elseif ($action === 'add_material') {
                $img = handleUpload('photo', 'assets/images/education/edukasi-terumbu.jpg');
                $stmt = $pdo->prepare('INSERT INTO educational_materials (title, slug, topic, reading_minutes, author_name, summary, content, image_url) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([$_POST['title'], slugify($_POST['title']) . '-' . time(), $_POST['topic'], (int)$_POST['reading_minutes'], $_POST['author_name'], $_POST['summary'], $_POST['content'], $img]);
                $msg = 'Materi edukasi diterbitkan.';
            } elseif ($action === 'delete_material') {
                $pdo->prepare('DELETE FROM educational_materials WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Materi edukasi dihapus.';
            } elseif ($action === 'add_quiz') {
                $stmt = $pdo->prepare('INSERT INTO quizzes (title, slug, description, difficulty, time_limit_minutes) VALUES (?,?,?,?,10)');
                $stmt->execute([$_POST['title'], slugify($_POST['title']) . '-' . time(), $_POST['description'], $_POST['difficulty']]);
                $msg = 'Kuis baru dibuat.';
            } elseif ($action === 'delete_quiz') {
                $pdo->prepare('DELETE FROM quizzes WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Kuis dihapus.';
            } elseif ($action === 'add_tip') {
                $title = trim($_POST['title'] ?? '');
                $category = $_POST['category'] ?? '';
                $icon = $_POST['icon'] ?? 'daun';
                $difficulty = $_POST['difficulty'] ?? 'Mudah';
                $summary = trim($_POST['summary'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                if ($title === '' || $summary === '' || $desc === '') {
                    throw new RuntimeException('Judul, ringkasan, dan penjelasan tips wajib diisi.');
                }
                if (!in_array($category, tipCategories(), true) || !in_array($difficulty, tipDifficulties(), true) || !array_key_exists($icon, tipIconPaths())) {
                    throw new RuntimeException('Kategori, tingkat, atau ikon tidak valid.');
                }
                $spId = (int)($_POST['related_species_id'] ?? 0) ?: null;
                $habId = (int)($_POST['related_habitat_id'] ?? 0) ?: null;
                $stmt = $pdo->prepare('INSERT INTO conservation_tips (title, category, icon, difficulty, summary, description, related_species_id, related_habitat_id, sort_order) VALUES (?,?,?,?,?,?,?,?,?)');
                $stmt->execute([mb_substr_safe($title, 160), $category, $icon, $difficulty, mb_substr_safe($summary, 255), $desc, $spId, $habId, (int)($_POST['sort_order'] ?? 0)]);
                $msg = 'Tips konservasi ditambahkan.';
            } elseif ($action === 'toggle_tip') {
                $pdo->prepare('UPDATE conservation_tips SET is_active = 1 - is_active WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Status tips diperbarui.';
            } elseif ($action === 'delete_tip') {
                $pdo->prepare('DELETE FROM conservation_tips WHERE id = ?')->execute([(int)$_POST['id']]);
                $msg = 'Tips dihapus.';
            }
        } catch (Throwable $ex) {
            $err = $ex->getMessage();
        }
    }
}

$totalSpecies = (int)$pdo->query('SELECT COUNT(*) FROM species')->fetchColumn();
$totalMat = (int)$pdo->query('SELECT COUNT(*) FROM educational_materials')->fetchColumn();
$totalQuiz = (int)$pdo->query('SELECT COUNT(*) FROM quizzes')->fetchColumn();
$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$speciesList = $pdo->query("
    SELECT s.*, c.name AS category_name, h.name AS habitat_name, cs.code AS status_code
    FROM species s
    JOIN categories c ON c.id = s.category_id
    JOIN habitats h ON h.id = s.habitat_id
    JOIN conservation_status cs ON cs.id = s.conservation_status_id
    ORDER BY s.id DESC
")->fetchAll();

$categories = $pdo->query('SELECT * FROM categories')->fetchAll();
$habitats = $pdo->query('SELECT * FROM habitats')->fetchAll();
$statuses = $pdo->query('SELECT * FROM conservation_status')->fetchAll();
$materials = $pdo->query('SELECT * FROM educational_materials ORDER BY id DESC')->fetchAll();
$quizzes = $pdo->query('SELECT * FROM quizzes ORDER BY id DESC')->fetchAll();
$tipsAdmin = $pdo->query('SELECT * FROM conservation_tips ORDER BY sort_order ASC, id ASC')->fetchAll();
$speciesOpts = $pdo->query('SELECT id, common_name FROM species ORDER BY common_name ASC')->fetchAll();

$baseUrl = '../';
$currentPage = 'admin';
$pageTitle = 'Dashboard Admin – AQUANEST';
require __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="kicker" style="color:var(--brand);">Panel Kurator &amp; Basis Data MySQL</div>
    <h1 style="font-size:32px;">Dashboard Admin AQUANEST</h1>
  </div>
</section>

<section class="section-pad" style="background:var(--cream);">
  <div class="container">
    <?php if ($msg): ?><div class="alert-ok"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert-err"><?= e($err) ?></div><?php endif; ?>

    <div class="card-4grid" style="margin-bottom:32px;">
      <div class="specimen-card"><div>Total Spesies</div><strong class="mono" style="font-size:28px;"><?= $totalSpecies ?></strong></div>
      <div class="specimen-card"><div>Materi Edukasi</div><strong class="mono" style="font-size:28px;"><?= $totalMat ?></strong></div>
      <div class="specimen-card"><div>Paket Kuis</div><strong class="mono" style="font-size:28px;"><?= $totalQuiz ?></strong></div>
      <div class="specimen-card"><div>Pengguna</div><strong class="mono" style="font-size:28px;"><?= $totalUsers ?></strong></div>
    </div>

    <!-- Form Tambah Spesies + Upload Gambar -->
    <div style="background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;margin-bottom:28px;">
      <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;">Tambah Spesies Baru (dengan Upload Foto)</h2>
      <form method="POST" enctype="multipart/form-data" style="display:grid;gap:12px;">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save_species">
        <div class="form-row">
          <input type="text" name="common_name" required placeholder="Nama Umum" class="input">
          <input type="text" name="scientific_name" required placeholder="Nama Ilmiah" class="input">
        </div>
        <div class="form-row">
          <select name="category_id" class="select"><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
          <select name="habitat_id" class="select"><?php foreach ($habitats as $h): ?><option value="<?= (int)$h['id'] ?>"><?= e($h['name']) ?></option><?php endforeach; ?></select>
          <select name="conservation_status_id" class="select"><?php foreach ($statuses as $st): ?><option value="<?= (int)$st['id'] ?>"><?= e($st['code']) ?> - <?= e($st['label_id']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="form-row">
          <input type="number" name="depth_min_m" value="1" placeholder="Kedalaman Min (m)" class="input">
          <input type="number" name="depth_max_m" value="40" placeholder="Kedalaman Max (m)" class="input">
          <input type="number" name="length_cm" value="80" placeholder="Panjang (cm)" class="input">
        </div>
        <div class="form-row">
          <input type="text" name="diet" placeholder="Pola Makan" class="input">
          <input type="text" name="distribution_indonesia" placeholder="Sebaran di Indonesia" class="input">
        </div>
        <input type="text" name="summary" required placeholder="Ringkasan singkat" class="input">
        <textarea name="description" rows="3" required placeholder="Deskripsi lengkap spesies..." class="textarea"></textarea>
        <textarea name="conservation_notes" rows="2" placeholder="Catatan konservasi..." class="textarea"></textarea>
        <div>
          <label style="font-size:12px;font-weight:600;">Unggah Foto Spesies (JPG/PNG/WEBP):</label>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
        </div>
        <div><button type="submit" class="btn btn-primary btn-sm">Simpan Spesies ke MySQL</button></div>
      </form>
    </div>

    <!-- Tabel Spesies -->
    <div style="background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;margin-bottom:28px;overflow-x:auto;">
      <h2 style="font-size:20px;font-weight:700;margin-bottom:14px;">Daftar Spesies</h2>
      <table class="data-table">
        <thead><tr><th>Nama</th><th>Ilmiah</th><th>Habitat</th><th>IUCN</th><th>Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($speciesList as $sp): ?>
          <tr>
            <td><strong><?= e($sp['common_name']) ?></strong></td>
            <td><em><?= e($sp['scientific_name']) ?></em></td>
            <td><?= e($sp['habitat_name']) ?></td>
            <td><?= statusBadge($sp['status_code']) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="delete_species">
                <input type="hidden" name="id" value="<?= (int)$sp['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Kelola Materi Edukasi & Kuis -->
    <div class="hero-grid" style="align-items:start;">
      <div style="background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:12px;">Terbitkan Materi Edukasi</h2>
        <form method="POST" enctype="multipart/form-data" style="display:grid;gap:10px;margin-bottom:18px;">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" value="add_material">
          <input type="text" name="title" required placeholder="Judul Materi" class="input">
          <input type="text" name="topic" required placeholder="Topik" class="input">
          <input type="text" name="author_name" value="Dr. Nabila Pratiwi" class="input">
          <input type="number" name="reading_minutes" value="5" class="input">
          <input type="text" name="summary" required placeholder="Ringkasan" class="input">
          <textarea name="content" rows="3" required placeholder="Konten materi..." class="textarea"></textarea>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
          <button type="submit" class="btn btn-primary btn-sm">Simpan Materi</button>
        </form>
        <?php foreach ($materials as $m): ?>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--line);font-size:13px;">
            <span><?= e($m['title']) ?></span>
            <form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete_material"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-danger btn-sm">Hapus</button></form>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;">
        <h2 style="font-size:18px;font-weight:700;margin-bottom:12px;">Kelola Kuis</h2>
        <form method="POST" style="display:grid;gap:10px;margin-bottom:18px;">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" value="add_quiz">
          <input type="text" name="title" required placeholder="Judul Kuis" class="input">
          <input type="text" name="description" required placeholder="Deskripsi Kuis" class="input">
          <select name="difficulty" class="select"><option>Pemula</option><option>Menengah</option><option>Lanjutan</option></select>
          <button type="submit" class="btn btn-primary btn-sm">Buat Kuis</button>
        </form>
        <?php foreach ($quizzes as $q): ?>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--line);font-size:13px;">
            <span><?= e($q['title']) ?></span>
            <form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete_quiz"><input type="hidden" name="id" value="<?= (int)$q['id'] ?>"><button class="btn btn-danger btn-sm">Hapus</button></form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Kelola Tips Konservasi -->
    <div style="background:#fff;border:1px solid var(--line);border-radius:20px;padding:24px;margin-top:24px;">
      <h2 style="font-size:18px;margin-bottom:12px;">Kelola Tips Konservasi</h2>
      <form method="POST" style="display:grid;gap:10px;margin-bottom:18px;">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="add_tip">
        <div class="form-row">
          <input type="text" name="title" required maxlength="160" placeholder="Judul tips" class="input">
          <select name="category" class="select"><?php foreach (tipCategories() as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select>
          <select name="difficulty" class="select"><?php foreach (tipDifficulties() as $d): ?><option><?= e($d) ?></option><?php endforeach; ?></select>
          <select name="icon" class="select"><?php foreach (array_keys(tipIconPaths()) as $ik): ?><option><?= e($ik) ?></option><?php endforeach; ?></select>
        </div>
        <input type="text" name="summary" required maxlength="255" placeholder="Ringkasan singkat" class="input">
        <textarea name="description" rows="3" required placeholder="Penjelasan tips..." class="textarea"></textarea>
        <div class="form-row">
          <select name="related_species_id" class="select"><option value="0">Spesies terkait (opsional)</option><?php foreach ($speciesOpts as $so): ?><option value="<?= (int)$so['id'] ?>"><?= e($so['common_name']) ?></option><?php endforeach; ?></select>
          <select name="related_habitat_id" class="select"><option value="0">Ekosistem terkait (opsional)</option><?php foreach ($habitats as $h): ?><option value="<?= (int)$h['id'] ?>"><?= e($h['name']) ?></option><?php endforeach; ?></select>
          <input type="number" name="sort_order" value="0" class="input" style="max-width:120px;" title="Urutan tampil">
        </div>
        <div><button type="submit" class="btn btn-primary btn-sm">Simpan Tips</button></div>
      </form>
      <?php foreach ($tipsAdmin as $t): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border-top:1px solid var(--line);font-size:13px;">
          <span><?= e($t['title']) ?> <small style="color:var(--muted);">&middot; <?= e($t['category']) ?><?= $t['is_active'] ? '' : ' &middot; nonaktif' ?></small></span>
          <span style="display:flex;gap:6px;">
            <form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="toggle_tip"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-outline btn-sm"><?= $t['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
            <form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="action" value="delete_tip"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="btn btn-danger btn-sm">Hapus</button></form>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>

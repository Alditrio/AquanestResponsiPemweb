<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getPDO();
$user = currentUser();
$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$resultFeedback = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        die('Token keamanan tidak valid.');
    }
    $qid = (int)$_POST['quiz_id'];
    $qStmt = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC');
    $qStmt->execute([$qid]);
    $questions = $qStmt->fetchAll();

    $score = 0;
    $ans = $_POST['answers'] ?? [];
    foreach ($questions as $q) {
        $picked = strtoupper($ans[$q['id']] ?? '');
        if ($picked === $q['correct_option']) {
            $score++;
        }
    }
    $total = count($questions);
    $pct = $total > 0 ? (int)round(($score / $total) * 100) : 0;

    if ($user && $total > 0) {
        $ins = $pdo->prepare('INSERT INTO quiz_results (user_id, quiz_id, score, total_questions, percentage) VALUES (?, ?, ?, ?, ?)');
        $ins->execute([$user['id'], $qid, $score, $total, $pct]);
    }
    $resultFeedback = ['score' => $score, 'total' => $total, 'pct' => $pct, 'saved' => (bool)$user];
    $quizId = $qid;
}

$activeQuiz = null;
$questions = [];
if ($quizId > 0) {
    $s = $pdo->prepare('SELECT * FROM quizzes WHERE id = ?');
    $s->execute([$quizId]);
    $activeQuiz = $s->fetch();
    if ($activeQuiz) {
        $qs = $pdo->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC');
        $qs->execute([$quizId]);
        $questions = $qs->fetchAll();
    }
}

$quizzes = $pdo->query("
    SELECT q.*, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count
    FROM quizzes q ORDER BY q.id ASC
")->fetchAll();

$myResults = [];
if ($user) {
    $rs = $pdo->prepare("
        SELECT qr.*, q.title AS quiz_title
        FROM quiz_results qr JOIN quizzes q ON q.id = qr.quiz_id
        WHERE qr.user_id = ? ORDER BY qr.completed_at DESC
    ");
    $rs->execute([$user['id']]);
    $myResults = $rs->fetchAll();
}

$currentPage = 'quiz';
$pageTitle = 'Kuis Biota Laut – AQUANEST';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <div class="kicker" style="color:var(--brand);">Evaluasi Pengetahuan</div>
    <h1 style="font-size:34px;">Kuis Biota Laut Nusantara</h1>
  </div>
</section>

<section class="section-pad">
  <div class="container">
    <?php if ($resultFeedback): ?>
      <div class="alert-ok">
        <strong>Hasil Kuis: <?= $resultFeedback['score'] ?> / <?= $resultFeedback['total'] ?> (<?= $resultFeedback['pct'] ?>%)</strong>
        &mdash; <?= $resultFeedback['saved'] ? 'Skor berhasil disimpan ke akun Anda.' : 'Masuk untuk menyimpan skor ke riwayat akun.' ?>
      </div>
    <?php endif; ?>

    <?php if ($activeQuiz && $questions): ?>
      <p style="margin-bottom:20px;"><a href="quiz.php" style="color:var(--brand);font-weight:600;">&larr; Kembali ke Daftar Kuis</a></p>
      <form method="POST" action="quiz.php?id=<?= (int)$activeQuiz['id'] ?>" style="max-width:760px;margin:0 auto;">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="quiz_id" value="<?= (int)$activeQuiz['id'] ?>">
        <h2 style="font-size:26px;margin-bottom:20px;"><?= e($activeQuiz['title']) ?></h2>

        <?php foreach ($questions as $idx => $q): ?>
          <div style="background:var(--cream);border:1px solid var(--line);border-radius:16px;padding:20px;margin-bottom:16px;">
            <p style="font-weight:700;margin-bottom:12px;"><?= $idx + 1 ?>. <?= e($q['question_text']) ?></p>
            <?php foreach (['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']] as $k => $opt): ?>
              <label style="display:block;padding:8px 10px;border-radius:10px;cursor:pointer;">
                <input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="<?= $k ?>" required>
                <strong><?= $k ?>.</strong> <?= e($opt) ?>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary">Kirim Jawaban Kuis</button>
      </form>
    <?php else: ?>
      <div class="card-3catalog">
        <?php foreach ($quizzes as $q): ?>
        <div class="catalog-card" style="padding:22px;">
          <div>
            <div style="font-size:12px;color:var(--accent-dark);font-weight:600;margin-bottom:6px;">
              <?= e($q['difficulty']) ?> &middot; <?= (int)$q['question_count'] ?> soal
            </div>
            <h2 style="font-size:19px;font-weight:700;margin-bottom:8px;"><?= e($q['title']) ?></h2>
            <p style="font-size:14px;color:var(--muted);margin-bottom:18px;"><?= e($q['description']) ?></p>
          </div>
          <a href="quiz.php?id=<?= (int)$q['id'] ?>" class="btn btn-primary btn-sm">Mulai Kuis &rarr;</a>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($myResults): ?>
      <div style="margin-top:42px;">
        <h2 style="font-size:20px;font-weight:700;margin-bottom:14px;">Riwayat Skor Anda</h2>
        <table class="data-table">
          <thead><tr><th>Kuis</th><th>Skor</th><th>Persentase</th><th>Tanggal</th></tr></thead>
          <tbody>
            <?php foreach ($myResults as $r): ?>
            <tr>
              <td><?= e($r['quiz_title']) ?></td>
              <td class="mono"><?= (int)$r['score'] ?> / <?= (int)$r['total_questions'] ?></td>
              <td class="mono"><strong><?= (int)$r['percentage'] ?>%</strong></td>
              <td class="mono"><?= e($r['completed_at']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

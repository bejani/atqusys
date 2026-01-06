<?php
require_once '../includes/config.php';
checkRole(['teacher', 'admin']);

require_once '../src/autoload.php';
use App\Actions\QuizAction;
use App\Actions\ReportAction;

$quizAction = new QuizAction();
$reportAction = new ReportAction();

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$teacher_id = $_SESSION['user_id'];

// دریافت اطلاعات کوئیز
$pdo = App\Domain\Database::getInstance();
if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT q.*, s.course_id, c.course_name FROM quizzes q JOIN sessions s ON q.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE q.session_id = ?");
    $stmt->execute([$session_id]);
} else {
    $stmt = $pdo->prepare("SELECT q.*, s.course_id, c.course_name FROM quizzes q JOIN sessions s ON q.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE q.session_id = ? AND c.teacher_id = ?");
    $stmt->execute([$session_id, $teacher_id]);
}
$quiz = $stmt->fetch();

if (!$quiz) die("کوئیزی برای این جلسه تعریف نشده است.");

// دریافت نمرات تفصیلی
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, qr.score, qr.submitted_at 
    FROM course_students cs
    JOIN users u ON cs.student_id = u.id
    LEFT JOIN quiz_results qr ON qr.student_id = u.id AND qr.quiz_id = ?
    WHERE cs.course_id = ?
    ORDER BY u.full_name ASC
");
$stmt->execute([$quiz['id'], $quiz['course_id']]);
$results = $stmt->fetchAll();

// خروجی اکسل
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=quiz_results_' . $session_id . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['نام دانشجو', 'نام کاربری', 'نمره (از ۲۰)', 'زمان ارسال']);
    foreach ($results as $row) {
        fputcsv($output, [$row['full_name'], $row['username'], $row['score'] ?? 'شرکت نکرده', $row['submitted_at'] ?? '---']);
    }
    fclose($output);
    exit();
}

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="sessions.php?id=<?php echo $quiz['course_id']; ?>" class="text-decoration-none"><?php echo $quiz['course_name']; ?></a></li>
                <li class="breadcrumb-item active">گزارش نمرات کوئیز</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">گزارش نمرات کوئیز</h2>
                <p class="text-muted">آزمون: <?php echo $quiz['title']; ?> - درس: <?php echo $quiz['course_name']; ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="?session_id=<?php echo $session_id; ?>&export=1" class="btn btn-success btn-modern shadow-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> خروجی اکسل
                </a>
                <a href="sessions.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-light btn-modern border shadow-sm">
                    <i class="bi bi-arrow-right me-1"></i> بازگشت
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modern-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3">نام و نام خانوادگی</th>
                    <th class="py-3">نام کاربری / شماره دانشجویی</th>
                    <th class="py-3 text-center">نمره (از ۲۰)</th>
                    <th class="pe-4 py-3">زمان ارسال پاسخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td class="ps-4 fw-bold"><?php echo $row['full_name']; ?></td>
                    <td><code><?php echo $row['username']; ?></code></td>
                    <td class="text-center">
                        <?php if ($row['score'] !== null): ?>
                            <div class="fs-5 fw-bold <?php echo $row['score'] >= 10 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $row['score']; ?>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-modern">شرکت نکرده</span>
                        <?php endif; ?>
                    </td>
                    <td class="pe-4">
                        <span class="text-muted small">
                            <i class="bi bi-clock me-1"></i> <?php echo $row['submitted_at'] ? date('H:i:s', strtotime($row['submitted_at'])) : '---'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

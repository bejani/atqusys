<?php
require_once '../includes/config.php';
checkRole(['teacher', 'admin']);

require_once '../src/autoload.php';
use App\Actions\QuizAction;
use App\Actions\ReportAction;

$quizAction = new QuizAction();
$reportAction = new ReportAction();

$session_id = $_GET['session_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// دریافت اطلاعات جلسه و درس
$session = $quizAction->getSessionWithCourse($session_id, $teacher_id);
if (!$session && $_SESSION['role'] !== 'admin') die("جلسه یافت نشد.");

// اگر ادمین باشد و جلسه از طریق متد قبلی یافت نشد (چون متد قبلی فیلتر استاد دارد)
if (!$session && $_SESSION['role'] === 'admin') {
    $pdo = App\Domain\Database::getInstance();
    $stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ?");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch();
}

// لیست حضور و غیاب (استفاده از کوئری مستقیم برای گزارش تفصیلی)
$pdo = App\Domain\Database::getInstance();
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, a.scanned_at, a.status 
    FROM course_students cs
    JOIN users u ON cs.student_id = u.id
    LEFT JOIN attendance a ON a.student_id = u.id AND a.session_id = ?
    WHERE cs.course_id = ?
    ORDER BY u.full_name ASC
");
$stmt->execute([$session_id, $session['course_id']]);
$attendance_list = $stmt->fetchAll();

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="sessions.php?id=<?php echo $session['course_id']; ?>" class="text-decoration-none"><?php echo $session['course_name']; ?></a></li>
                <li class="breadcrumb-item active">گزارش حضور و غیاب</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">گزارش حضور و غیاب</h2>
                <p class="text-muted">جلسه مورخ <?php echo $session['session_date']; ?> - درس <?php echo $session['course_name']; ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="export_attendance.php?session_id=<?php echo $session_id; ?>" class="btn btn-success btn-modern shadow-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> خروجی اکسل
                </a>
                <a href="sessions.php?id=<?php echo $session['course_id']; ?>" class="btn btn-light btn-modern border shadow-sm">
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
                    <th class="py-3">وضعیت</th>
                    <th class="pe-4 py-3">زمان ثبت حضور</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_list as $row): ?>
                <tr>
                    <td class="ps-4 fw-bold"><?php echo $row['full_name']; ?></td>
                    <td><code><?php echo $row['username']; ?></code></td>
                    <td>
                        <?php if ($row['status']): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle badge-modern">
                                <i class="bi bi-check-circle me-1"></i> حاضر
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-modern">
                                <i class="bi bi-x-circle me-1"></i> غایب
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="pe-4">
                        <span class="text-muted small">
                            <i class="bi bi-clock me-1"></i> <?php echo $row['scanned_at'] ? date('H:i:s', strtotime($row['scanned_at'])) : '---'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

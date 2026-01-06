<?php
require_once '../includes/config.php';
checkRole(['teacher']);

require_once '../src/autoload.php';
use App\Actions\CourseAction;
use App\Actions\SessionAction;

$courseAction = new CourseAction();
$sessionAction = new SessionAction();

$course_id = $_GET['id'] ?? 0;
$course = $courseAction->getCourseById($course_id, $_SESSION['user_id']);

if (!$course) {
    die("درس یافت نشد.");
}

// ایجاد جلسه جدید
if (isset($_POST['create_session'])) {
    if ($sessionAction->createSession($course_id)) {
        header("Location: sessions.php?id=$course_id");
        exit();
    }
}

// لیست جلسات
$sessions = $sessionAction->getCourseSessions($course_id);

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">داشبورد</a></li>
                <li class="breadcrumb-item active"><?php echo $course['course_name']; ?></li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">جلسات درس: <?php echo $course['course_name']; ?></h2>
                <p class="text-muted">مدیریت حضور و غیاب و کوئیزهای هر جلسه.</p>
            </div>
            <form method="POST">
                <button type="submit" name="create_session" class="btn btn-primary-modern btn-modern shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> ایجاد جلسه امروز
                </button>
            </form>
        </div>
    </div>
</div>

<div class="modern-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3">تاریخ جلسه</th>
                    <th class="py-3">وضعیت</th>
                    <th class="py-3">حضور و غیاب</th>
                    <th class="py-3">کوئیز</th>
                    <th class="pe-4 py-3 text-center">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">هنوز جلسه‌ای برای این درس ثبت نشده است.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $s): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?php echo $s['session_date']; ?></td>
                        <td>
                            <?php if ($s['is_active']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle badge-modern">فعال</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle badge-modern">بسته شده</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="view_qr.php?token=<?php echo $s['qr_code_token']; ?>" class="btn btn-outline-primary" target="_blank" title="نمایش QR">
                                    <i class="bi bi-qr-code"></i> QR
                                </a>
                                <a href="attendance_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-outline-info" title="گزارش حضور">
                                    <i class="bi bi-file-earmark-text"></i> گزارش
                                </a>
                            </div>
                        </td>
                        <td>
                            <?php 
                                $quiz = $sessionAction->getQuizForSession($s['id']);
                                if ($quiz):
                            ?>
                                <div class="btn-group btn-group-sm">
                                    <a href="view_quiz_qr.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-outline-dark" target="_blank" title="QR کوئیز">
                                        <i class="bi bi-qr-code-scan"></i> QR
                                    </a>
                                    <a href="quiz_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-outline-warning" title="نمرات">
                                        <i class="bi bi-trophy"></i> نمرات
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="manage_quiz.php?session_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light border">
                                    <i class="bi bi-plus-lg me-1"></i> تعریف کوئیز
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-center">
                            <a href="manage_quiz.php?session_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-modern btn-light border" title="تنظیمات">
                                <i class="bi bi-gear"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>

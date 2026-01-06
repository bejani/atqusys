<?php
require_once '../includes/config.php';
checkRole(['admin']);

// دریافت لیست تمامی دروس به همراه نام استاد
$stmt = $pdo->query("SELECT c.*, u.full_name as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id");
$courses = $stmt->fetchAll();

$selected_course_id = $_GET['course_id'] ?? 0;
$sessions = [];
if ($selected_course_id) {
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE course_id = ? ORDER BY session_date DESC");
    $stmt->execute([$selected_course_id]);
    $sessions = $stmt->fetchAll();
}

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">گزارشات جامع مدیریتی</h2>
                <p class="text-muted">مشاهده سوابق حضور و غیاب و نمرات تمامی دروس سیستم</p>
            </div>
            <a href="dashboard.php" class="btn btn-light btn-modern border shadow-sm">
                <i class="bi bi-arrow-right me-1"></i> بازگشت به پنل
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- انتخاب درس -->
    <div class="col-lg-4">
        <div class="modern-card p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-2">فیلتر بر اساس درس</h5>
            <form method="GET">
                <div class="mb-3">
                    <label class="form-label small fw-bold">انتخاب درس و استاد</label>
                    <select name="course_id" class="form-select form-control-modern" onchange="this.form.submit()">
                        <option value="">یک درس انتخاب کنید...</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $selected_course_id == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo $c['course_name']; ?> (<?php echo $c['teacher_name']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <?php if (!$selected_course_id): ?>
                <div class="alert alert-info badge-modern small mt-3">
                    <i class="bi bi-info-circle me-2"></i> برای مشاهده لیست جلسات، ابتدا یک درس را انتخاب کنید.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- نمایش جلسات و گزارشات -->
    <div class="col-lg-8">
        <?php if ($selected_course_id): ?>
            <div class="modern-card">
                <div class="p-4 border-bottom bg-light bg-opacity-50">
                    <h5 class="fw-bold mb-0">لیست جلسات و فعالیت‌ها</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">تاریخ جلسه</th>
                                <th class="py-3 text-center">گزارش حضور</th>
                                <th class="pe-4 py-3 text-center">گزارش نمرات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo $s['session_date']; ?></td>
                                <td class="text-center">
                                    <a href="../teacher/attendance_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-info btn-modern px-3">
                                        <i class="bi bi-person-check me-1"></i> مشاهده حضور
                                    </a>
                                </td>
                                <td class="pe-4 text-center">
                                    <?php 
                                        $q_stmt = $pdo->prepare("SELECT id FROM quizzes WHERE session_id = ?");
                                        $q_stmt->execute([$s['id']]);
                                        if ($q_stmt->fetch()):
                                    ?>
                                        <a href="../teacher/quiz_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-warning btn-modern px-3">
                                            <i class="bi bi-trophy me-1"></i> مشاهده نمرات
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border badge-modern">بدون کوئیز</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($sessions)): ?>
                                <tr><td colspan="3" class="text-center py-5 text-muted">هیچ جلسه‌ای برای این درس ثبت نشده است.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="modern-card p-5 text-center">
                <i class="bi bi-bar-chart-line display-1 text-muted opacity-25 mb-4"></i>
                <h4 class="text-muted">در انتظار انتخاب درس...</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../teacher/footer.php'; ?>

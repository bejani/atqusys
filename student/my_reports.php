<?php
require_once '../includes/config.php';
checkRole(['student']);

require_once '../src/autoload.php';
use App\Actions\ReportAction;

$reportAction = new ReportAction();
$student_id = $_SESSION['user_id'];

$attendance_records = $reportAction->getStudentAttendance($student_id);
$quiz_records = $reportAction->getStudentQuizResults($student_id);

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark mb-1">گزارشات و کارنامه</h2>
        <p class="text-muted">سوابق حضور در کلاس و نمرات کوئیزهای خود را مشاهده کنید.</p>
    </div>
</div>

<div class="row g-4">
    <!-- بخش حضور و غیاب -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="p-4 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-success">
                    <i class="bi bi-calendar-check me-1"></i> سوابق حضور و غیاب
                </h5>
                <span class="badge bg-success badge-modern"><?php echo count($attendance_records); ?> جلسه</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">نام درس</th>
                            <th class="py-3">تاریخ</th>
                            <th class="pe-4 py-3">زمان ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendance_records)): ?>
                            <tr><td colspan="3" class="text-center py-5 text-muted">سابقه حضوری یافت نشد.</td></tr>
                        <?php else: ?>
                            <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo $record['course_name']; ?></td>
                                <td><?php echo $record['session_date']; ?></td>
                                <td class="pe-4"><span class="badge bg-light text-dark border badge-modern"><?php echo date('H:i', strtotime($record['scanned_at'])); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- بخش نمرات کوئیز -->
    <div class="col-lg-6">
        <div class="modern-card h-100">
            <div class="p-4 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-trophy me-1"></i> کارنامه کوئیزها
                </h5>
                <span class="badge bg-primary badge-modern"><?php echo count($quiz_records); ?> آزمون</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">نام کوئیز / درس</th>
                            <th class="pe-4 py-3 text-center">نمره (از ۲۰)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quiz_records)): ?>
                            <tr><td colspan="2" class="text-center py-5 text-muted">سابقه کوئیزی یافت نشد.</td></tr>
                        <?php else: ?>
                            <?php foreach ($quiz_records as $record): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?php echo $record['title']; ?></div>
                                    <small class="text-muted"><?php echo $record['course_name']; ?></small>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="fs-5 fw-bold <?php echo $record['score'] >= 10 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $record['score']; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="text-center mt-5">
    <a href="dashboard.php" class="btn btn-light btn-modern border px-5 shadow-sm">
        <i class="bi bi-house-door me-1"></i> بازگشت به داشبورد
    </a>
</div>

<?php include 'footer.php'; ?>

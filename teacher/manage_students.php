<?php
require_once '../includes/config.php';
checkRole(['teacher']);

require_once '../src/autoload.php';
use App\Actions\CourseAction;
use App\Actions\StudentAction;

$courseAction = new CourseAction();
$studentAction = new StudentAction();

$course_id = $_GET['id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// بررسی مالکیت درس
$course = $courseAction->getCourseById($course_id, $teacher_id);
if (!$course) die("درس یافت نشد.");

$message = "";
$messageType = "success";

// بروزرسانی لیست دانشجویان
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_students'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $selected_students = $_POST['student_ids'] ?? [];
    if ($studentAction->updateCourseStudents($course_id, $selected_students)) {
        $message = "لیست دانشجویان با موفقیت به‌روزرسانی شد.";
    } else {
        $message = "خطا در به‌روزرسانی لیست دانشجویان.";
        $messageType = "danger";
    }
}

// دریافت داده‌ها
$all_students = $studentAction->getAllStudents();
$current_student_ids = $studentAction->getCourseStudentIds($course_id);

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">داشبورد</a></li>
                <li class="breadcrumb-item active">مدیریت دانشجویان: <?php echo $course['course_name']; ?></li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">مدیریت دانشجویان</h2>
                <p class="text-muted">دانشجویان مجاز به شرکت در این درس را انتخاب کنید.</p>
            </div>
            <a href="dashboard.php" class="btn btn-light btn-modern border shadow-sm">
                <i class="bi bi-arrow-right me-1"></i> بازگشت
            </a>
        </div>
    </div>
</div>

<?php if ($message): ?> 
    <div class="alert alert-<?php echo $messageType; ?> border-0 shadow-sm badge-modern mb-4">
        <i class="bi bi-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i> 
        <?php echo $message; ?>
    </div> 
<?php endif; ?>

<div class="modern-card">
    <form method="POST">
        <?php csrfField(); ?>
        <div class="p-4 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">لیست دانشجویان سیستم</h5>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label fw-semibold" for="selectAll">انتخاب همه</label>
            </div>
        </div>
        
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light sticky-top">
                    <tr>
                        <th class="ps-4 py-3" width="80">انتخاب</th>
                        <th class="py-3">نام و نام خانوادگی</th>
                        <th class="pe-4 py-3">نام کاربری (شماره دانشجویی)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_students as $student): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="form-check">
                                <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" 
                                       value="<?php echo $student['id']; ?>"
                                       <?php echo in_array($student['id'], $current_student_ids) ? 'checked' : ''; ?>>
                            </div>
                        </td>
                        <td class="fw-bold"><?php echo $student['full_name']; ?></td>
                        <td class="pe-4"><code><?php echo $student['username']; ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-light border-top text-end">
            <button type="submit" name="save_students" class="btn btn-primary-modern btn-modern px-5 shadow">
                <i class="bi bi-save me-1"></i> ذخیره تغییرات لیست
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>

<?php include 'footer.php'; ?>

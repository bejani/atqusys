<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$teacher_id = $_SESSION['user_id'];

// دریافت لیست دروس این استاد
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll();

// افزودن درس جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['course_name'];
    $code = $_POST['course_code'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $code, $teacher_id]);
        header("Location: dashboard.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "خطا در ثبت درس: " . $e->getMessage();
    }
}

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">مدیریت دروس</h2>
                <p class="text-muted">دروس خود را مدیریت کنید و دانشجویان را فراخوانی نمایید.</p>
            </div>
            <button class="btn btn-primary-modern btn-modern shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="bi bi-plus-lg me-1"></i> افزودن درس جدید
            </button>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm badge-modern mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> درس جدید با موفقیت ثبت شد.
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if (empty($courses)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-book display-1 text-muted opacity-25"></i>
            <p class="mt-3 text-muted">هنوز درسی ثبت نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
            <div class="col-md-6 col-lg-4">
                <div class="modern-card h-100">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4">
                                <i class="bi bi-journal-bookmark-fill fs-3 text-primary"></i>
                            </div>
                            <span class="badge bg-light text-dark border badge-modern"><?php echo $course['course_code']; ?></span>
                        </div>
                        <h4 class="fw-bold mb-3"><?php echo $course['course_name']; ?></h4>
                        <div class="d-grid gap-2">
                            <a href="manage_students.php?id=<?php echo $course['id']; ?>" class="btn btn-outline-primary btn-modern btn-sm">
                                <i class="bi bi-people me-1"></i> مدیریت دانشجویان
                            </a>
                            <a href="sessions.php?id=<?php echo $course['id']; ?>" class="btn btn-primary-modern btn-modern btn-sm">
                                <i class="bi bi-calendar-event me-1"></i> مدیریت جلسات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal افزودن درس -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">افزودن درس جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">نام درس</label>
                        <input type="text" name="course_name" class="form-control form-control-modern" placeholder="مثال: ریاضی مهندسی" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">کد درس</label>
                        <input type="text" name="course_code" class="form-control form-control-modern" placeholder="مثال: MATH101" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-modern" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_course" class="btn btn-primary-modern btn-modern">ثبت درس</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

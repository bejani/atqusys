<?php
require_once '../includes/config.php';
checkRole(['student']);

$student_id = $_SESSION['user_id'];

// دریافت دروس دانشجو
$stmt = $pdo->prepare("SELECT c.* FROM courses c JOIN course_students cs ON c.id = cs.course_id WHERE cs.student_id = ?");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll();

// دریافت کوئیزهای فعال برای دروس این دانشجو
$stmt = $pdo->prepare("SELECT q.*, c.course_name FROM quizzes q 
                       JOIN sessions s ON q.session_id = s.id 
                       JOIN courses c ON s.course_id = c.id
                       JOIN course_students cs ON c.id = cs.course_id
                       WHERE cs.student_id = ? AND q.is_active = 1");
$stmt->execute([$student_id]);
$active_quizzes = $stmt->fetchAll();

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark mb-1">سلام، <?php echo explode(' ', $_SESSION['full_name'])[0]; ?>! 👋</h2>
        <p class="text-muted">به پنل دانشجویی خود خوش آمدید. وضعیت کلاس‌های امروز را بررسی کنید.</p>
    </div>
</div>

<?php if (isset($_GET['quiz_success'])): ?>
    <div class="alert alert-success border-0 shadow-sm badge-modern mb-4 d-flex align-items-center">
        <i class="bi bi-check-circle-fill fs-4 me-3"></i>
        <div>
            <div class="fw-bold">کوئیز با موفقیت ثبت شد!</div>
            <div>نمره شما: <span class="badge bg-success"><?php echo $_GET['score']; ?> از ۲۰</span></div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- کوئیزهای فعال -->
    <div class="col-lg-7">
        <div class="modern-card h-100">
            <div class="p-4 border-bottom bg-light bg-opacity-50">
                <h5 class="fw-bold mb-0 text-warning">
                    <i class="bi bi-lightning-charge-fill me-1"></i> کوئیزهای فعال امروز
                </h5>
            </div>
            <div class="p-4">
                <?php if (empty($active_quizzes)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-emoji-smile display-4 text-muted opacity-25"></i>
                        <p class="mt-3 text-muted">در حال حاضر کوئیز فعالی ندارید. استراحت کنید!</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($active_quizzes as $aq): ?>
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1"><?php echo $aq['title']; ?></h6>
                                <small class="text-muted"><i class="bi bi-book me-1"></i> <?php echo $aq['course_name']; ?></small>
                            </div>
                            <a href="take_quiz.php?quiz_id=<?php echo $aq['id']; ?>" class="btn btn-warning btn-modern btn-sm shadow-sm">
                                شرکت در آزمون <i class="bi bi-chevron-left ms-1"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- لیست دروس -->
    <div class="col-lg-5">
        <div class="modern-card h-100">
            <div class="p-4 border-bottom bg-light bg-opacity-50">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="bi bi-journal-check me-1"></i> دروس من
                </h5>
            </div>
            <div class="p-4">
                <?php if (empty($courses)): ?>
                    <p class="text-muted text-center">شما هنوز در درسی عضو نشده‌اید.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($courses as $c): ?>
                        <div class="col-12">
                            <div class="p-3 rounded-4 border bg-white d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                    <i class="bi bi-bookmark-star text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold"><?php echo $c['course_name']; ?></div>
                                    <small class="text-muted"><?php echo $c['course_code']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

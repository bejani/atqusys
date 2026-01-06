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
?>
<?php 
$page_title = "پنل دانشجو - داشبورد";
include 'header.php'; 
?>
        <?php if (isset($_GET['quiz_success'])): ?>
            <div class="alert alert-success">کوئیز با موفقیت ثبت شد. نمره شما: <?php echo $_GET['score']; ?> از ۲۰</div>
        <?php endif; ?>

        <div class="row">
            <!-- کوئیزهای فعال -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning">کوئیزهای فعال امروز</div>
                    <div class="card-body">
                        <?php if (empty($active_quizzes)): ?>
                            <p class="text-muted">در حال حاضر کوئیز فعالی ندارید.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($active_quizzes as $aq): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $aq['course_name']; ?>: <?php echo $aq['title']; ?>
                                    <a href="take_quiz.php?quiz_id=<?php echo $aq['id']; ?>" class="btn btn-sm btn-success">شرکت در کوئیز</a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- لیست دروس -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">دروس من</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($courses as $c): ?>
                            <li class="list-group-item"><?php echo $c['course_name']; ?> (<?php echo $c['course_code']; ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
<?php include 'footer.php'; ?>

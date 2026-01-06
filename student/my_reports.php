<?php
require_once '../includes/config.php';
checkRole(['student']);

$student_id = $_SESSION['user_id'];

// دریافت سوابق حضور و غیاب
$stmt = $pdo->prepare("
    SELECT s.session_date, c.course_name, a.scanned_at 
    FROM attendance a 
    JOIN sessions s ON a.session_id = s.id 
    JOIN courses c ON s.course_id = c.id 
    WHERE a.student_id = ? 
    ORDER BY s.session_date DESC
");
$stmt->execute([$student_id]);
$attendance_records = $stmt->fetchAll();

// دریافت سوابق نمرات کوئیز
$stmt = $pdo->prepare("
    SELECT q.title, c.course_name, qr.score, qr.submitted_at 
    FROM quiz_results qr 
    JOIN quizzes q ON qr.quiz_id = q.id 
    JOIN sessions s ON q.session_id = s.id 
    JOIN courses c ON s.course_id = c.id 
    WHERE qr.student_id = ? 
    ORDER BY qr.submitted_at DESC
");
$stmt->execute([$student_id]);
$quiz_records = $stmt->fetchAll();
?>
<?php 
$page_title = "گزارشات من - سوابق حضور و نمرات";
include 'header.php'; 
?>
        <div class="row">
            <!-- بخش حضور و غیاب -->
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">سوابق حضور و غیاب</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>درس</th>
                                        <th>تاریخ جلسه</th>
                                        <th>زمان ثبت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendance_records as $record): ?>
                                    <tr>
                                        <td><?php echo $record['course_name']; ?></td>
                                        <td><?php echo $record['session_date']; ?></td>
                                        <td><?php echo date('H:i', strtotime($record['scanned_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($attendance_records)): ?>
                                    <tr><td colspan="3" class="text-center">سابقه حضوری یافت نشد.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بخش نمرات کوئیز -->
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">کارنامه کوئیزها</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>نام کوئیز</th>
                                        <th>درس</th>
                                        <th>نمره (از ۲۰)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quiz_records as $record): ?>
                                    <tr>
                                        <td><?php echo $record['title']; ?></td>
                                        <td><?php echo $record['course_name']; ?></td>
                                        <td><strong><?php echo $record['score']; ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($quiz_records)): ?>
                                    <tr><td colspan="3" class="text-center">سابقه کوئیزی یافت نشد.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
        </div>
<?php include 'footer.php'; ?>

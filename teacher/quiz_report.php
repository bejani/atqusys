<?php
require_once '../includes/config.php';
checkRole(['teacher', 'admin']);

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$teacher_id = $_SESSION['user_id'];

// دریافت اطلاعات کوئیز
if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT q.*, s.course_id, c.course_name FROM quizzes q JOIN sessions s ON q.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE q.session_id = ?");
    $stmt->execute([$session_id]);
} else {
    $stmt = $pdo->prepare("SELECT q.*, s.course_id, c.course_name FROM quizzes q JOIN sessions s ON q.session_id = s.id JOIN courses c ON s.course_id = c.id WHERE q.session_id = ? AND c.teacher_id = ?");
    $stmt->execute([$session_id, $teacher_id]);
}
$quiz = $stmt->fetch();

if (!$quiz) die("کوئیزی برای این جلسه تعریف نشده است.");

// دریافت نمرات
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, qr.score, qr.submitted_at 
    FROM course_students cs
    JOIN users u ON cs.student_id = u.id
    LEFT JOIN quiz_results qr ON qr.student_id = u.id AND qr.quiz_id = ?
    WHERE cs.course_id = ?
");
$stmt->execute([$quiz['id'], $quiz['course_id']]);
$results = $stmt->fetchAll();

// خروجی اکسل اگر درخواست شده باشد
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
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش نمرات کوئیز</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <strong>نمرات: <?php echo $quiz['title']; ?> (<?php echo $quiz['course_name']; ?>)</strong>
                <div>
                    <a href="?session_id=<?php echo $session_id; ?>&export=1" class="btn btn-sm btn-success">خروجی اکسل</a>
                    <a href="sessions.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-sm btn-secondary">بازگشت</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>نام دانشجو</th>
                            <th>نام کاربری</th>
                            <th>نمره (از ۲۰)</th>
                            <th>زمان ارسال</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <?php if ($row['score'] !== null): ?>
                                    <strong class="text-primary"><?php echo $row['score']; ?></strong>
                                <?php else: ?>
                                    <span class="text-danger">شرکت نکرده</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['submitted_at'] ?? '---'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

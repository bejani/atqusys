<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$course_id = $_GET['id'] ?? 0;

// بررسی دسترسی استاد به درس
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    die("درس یافت نشد.");
}

// ایجاد جلسه جدید
if (isset($_POST['create_session'])) {
    $date = date('Y-m-d');
    $token = bin2hex(random_bytes(16)); // تولید توکن تصادفی برای QR
    
    $stmt = $pdo->prepare("INSERT INTO sessions (course_id, session_date, qr_code_token) VALUES (?, ?, ?)");
    $stmt->execute([$course_id, $date, $token]);
    header("Location: sessions.php?id=$course_id");
    exit();
}

// لیست جلسات
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE course_id = ? ORDER BY created_at DESC");
$stmt->execute([$course_id]);
$sessions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>جلسات درس - <?php echo $course['course_name']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>جلسات درس: <?php echo $course['course_name']; ?></h3>
            <div>
                <form method="POST" style="display:inline;">
                    <button type="submit" name="create_session" class="btn btn-success">ایجاد جلسه امروز</button>
                </form>
                <a href="dashboard.php" class="btn btn-secondary">بازگشت</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><?php echo $s['session_date']; ?></td>
                            <td><?php echo $s['is_active'] ? '<span class="badge bg-success">فعال</span>' : '<span class="badge bg-secondary">بسته شده</span>'; ?></td>
                            <td>
                                <a href="view_qr.php?token=<?php echo $s['qr_code_token']; ?>" class="btn btn-primary btn-sm" target="_blank" title="QR حضور و غیاب">QR حضور</a>
                                <a href="attendance_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-info btn-sm">گزارش حضور</a>
                                <a href="manage_quiz.php?session_id=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm">مدیریت کوئیز</a>
                                <?php 
                                    // بررسی وجود کوئیز برای این جلسه جهت نمایش دکمه QR
                                    $q_stmt = $pdo->prepare("SELECT id FROM quizzes WHERE session_id = ?");
                                    $q_stmt->execute([$s['id']]);
                                    $quiz_exists = $q_stmt->fetch();
                                    if ($quiz_exists):
                                ?>
                                    <a href="view_quiz_qr.php?quiz_id=<?php echo $quiz_exists['id']; ?>" class="btn btn-dark btn-sm" target="_blank">QR کوئیز</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

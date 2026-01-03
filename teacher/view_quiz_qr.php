<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$quiz_id = $_GET['quiz_id'] ?? 0;

// دریافت اطلاعات کوئیز و درس
$stmt = $pdo->prepare("SELECT q.*, c.course_name, s.session_date FROM quizzes q 
                       JOIN sessions s ON q.session_id = s.id 
                       JOIN courses c ON s.course_id = c.id 
                       WHERE q.id = ? AND c.teacher_id = ?");
$stmt->execute([$quiz_id, $_SESSION['user_id']]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("کوئیز یافت نشد یا دسترسی ندارید.");
}

// آدرس صفحه شرکت در کوئیز برای دانشجو
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$quiz_url = "$protocol://$host/attendance_quiz_system/student/take_quiz.php?quiz_id=$quiz_id";

// استفاده از API گوگل برای تولید QR Code
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($quiz_url);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>QR Code کوئیز</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background-color: #fff; text-align: center; padding-top: 50px; font-family: Tahoma; }
        .qr-card { max-width: 500px; margin: auto; padding: 20px; border: 2px solid #ffc107; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="qr-card shadow">
        <h3 class="text-warning">QR Code شرکت در کوئیز</h3>
        <h4><?php echo $quiz['course_name']; ?></h4>
        <p class="text-muted">عنوان کوئیز: <?php echo $quiz['title']; ?></p>
        <hr>
        <p>دانشجویان عزیز، برای شروع کوئیز کد زیر را اسکن کنید:</p>
        <img src="<?php echo $qr_api_url; ?>" alt="Quiz QR Code" class="img-fluid mb-3">
        <div class="alert alert-warning">
            <strong>توجه:</strong> فقط دانشجویانی که در این درس ثبت‌نام شده‌اند می‌توانند در کوئیز شرکت کنند.
        </div>
        <button onclick="window.print()" class="btn btn-secondary no-print">چاپ کد</button>
        <a href="sessions.php?id=<?php echo $quiz['course_id']; ?>" class="btn btn-outline-dark no-print">بازگشت</a>
    </div>
</body>
</html>

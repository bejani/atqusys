<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$token = $_GET['token'] ?? '';

// پیدا کردن اطلاعات جلسه بر اساس توکن
$stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.qr_code_token = ?");
$stmt->execute([$token]);
$session = $stmt->fetch();

if (!$session) {
    die("جلسه معتبر نیست.");
}

// آدرس صفحه ثبت حضور برای دانشجو
// در محیط واقعی این باید آدرس IP یا دامنه سرور شما باشد
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$attendance_url = "$protocol://$host/attendance_quiz_system/student/mark_attendance.php?token=$token";

// استفاده از API گوگل برای تولید QR Code
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($attendance_url);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>QR Code حضور و غیاب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background-color: #fff; text-align: center; padding-top: 50px; font-family: Tahoma; }
        .qr-card { max-width: 500px; margin: auto; padding: 20px; border: 2px solid #eee; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="qr-card shadow">
        <h3><?php echo $session['course_name']; ?></h3>
        <p class="text-muted">جلسه مورخ: <?php echo $session['session_date']; ?></p>
        <hr>
        <p>دانشجویان عزیز، لطفاً کد زیر را اسکن کنید:</p>
        <img src="<?php echo $qr_api_url; ?>" alt="QR Code" class="img-fluid mb-3">
        <div class="alert alert-info">
            این کد مخصوص ثبت حضور در کلاس امروز است.
        </div>
        <button onclick="window.print()" class="btn btn-secondary no-print">چاپ کد</button>
    </div>
</body>
</html>

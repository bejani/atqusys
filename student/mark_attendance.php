<?php
require_once '../includes/config.php';
checkRole(['student']);

$token = $_GET['token'] ?? '';
$student_id = $_SESSION['user_id'];

// ۱. پیدا کردن جلسه بر اساس توکن
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE qr_code_token = ? AND is_active = 1");
$stmt->execute([$token]);
$session = $stmt->fetch();

$message = "";
$status = "danger";

if ($session) {
    $session_id = $session['id'];
    $course_id = $session['course_id'];

    // ۲. بررسی اینکه آیا دانشجو در این درس ثبت‌نام شده است؟
    $stmt = $pdo->prepare("SELECT * FROM course_students WHERE course_id = ? AND student_id = ?");
    $stmt->execute([$course_id, $student_id]);
    $is_enrolled = $stmt->fetch();

    if ($is_enrolled) {
        // ۳. ثبت حضور (اگر قبلاً ثبت نشده باشد)
        try {
            $stmt = $pdo->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, 'present')");
            $stmt->execute([$session_id, $student_id]);
            $message = "حضور شما با موفقیت در تاریخ " . date('Y-m-d H:i') . " ثبت شد.";
            $status = "success";
        } catch (PDOException $e) {
            // اگر قبلاً ثبت شده باشد، خطای Unique Key رخ می‌دهد
            $message = "حضور شما قبلاً برای این جلسه ثبت شده است.";
            $status = "warning";
        }
    } else {
        $message = "شما در این درس ثبت‌نام نشده‌اید. لطفاً با استاد تماس بگیرید.";
    }
} else {
    $message = "کد اسکن شده معتبر نیست یا جلسه به پایان رسیده است.";
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ثبت حضور و غیاب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f8f9fa; padding-top: 100px; }</style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white text-center">نتیجه ثبت حضور</div>
                    <div class="card-body text-center">
                        <div class="alert alert-<?php echo $status; ?>">
                            <?php echo $message; ?>
                        </div>
                        <a href="dashboard.php" class="btn btn-primary">بازگشت به پنل کاربری</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

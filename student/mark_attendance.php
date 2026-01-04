<?php
require_once '../includes/config.php';

// اگر کاربر لاگین نکرده، او را به صفحه لاگین با ریدایرکت به همین صفحه بفرست
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../login.php?redirect=$redirect_url");
    exit();
}

checkRole(['student']);

$token = $_GET['token'] ?? '';
$student_id = $_SESSION['user_id'];
$message = "";
$error = "";

// پیدا کردن جلسه بر اساس توکن
$stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.qr_code_token = ? AND s.is_active = 1");
$stmt->execute([$token]);
$session = $stmt->fetch();

if (!$session) {
    $error = "کد اسکن شده نامعتبر است یا جلسه به پایان رسیده است.";
} else {
    // بررسی اینکه آیا دانشجو در این درس ثبت‌نام شده است
    $stmt = $pdo->prepare("SELECT * FROM course_students WHERE course_id = ? AND student_id = ?");
    $stmt->execute([$session['course_id'], $student_id]);
    if (!$stmt->fetch()) {
        $error = "شما در این درس ثبت‌نام نشده‌اید و نمی‌توانید حضور خود را ثبت کنید.";
    }
}

// ثبت حضور در صورت فشردن دکمه تایید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_attendance']) && !$error) {
    // بررسی اینکه آیا قبلاً ثبت شده است
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE session_id = ? AND student_id = ?");
    $stmt->execute([$session['id'], $student_id]);
    if ($stmt->fetch()) {
        $message = "حضور شما قبلاً برای این جلسه ثبت شده است.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, 1)");
        if ($stmt->execute([$session['id'], $student_id])) {
            $message = "حضور شما در کلاس «" . $session['course_name'] . "» با موفقیت ثبت شد.";
        } else {
            $error = "خطا در ثبت حضور. لطفاً دوباره تلاش کنید.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تایید حضور در کلاس</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: Tahoma; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .attendance-card { max-width: 400px; width: 90%; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: white; text-align: center; }
        .btn-confirm { padding: 15px 30px; font-size: 1.2rem; border-radius: 50px; transition: all 0.3s; }
        .btn-confirm:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="attendance-card">
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?php echo $error; ?></div>
            <a href="dashboard.php" class="btn btn-secondary w-100">بازگشت به پنل</a>
        <?php elseif ($message): ?>
            <div class="alert alert-success mb-4"><?php echo $message; ?></div>
            <a href="dashboard.php" class="btn btn-primary w-100">مشاهده دروس من</a>
        <?php else: ?>
            <h3 class="mb-3 text-primary">تایید حضور</h3>
            <p class="mb-4">شما در حال ثبت حضور برای درس زیر هستید:</p>
            <div class="p-3 bg-light rounded mb-4">
                <strong><?php echo $session['course_name']; ?></strong><br>
                <small class="text-muted">تاریخ: <?php echo $session['session_date']; ?></small>
            </div>
            <form method="POST">
                <button type="submit" name="confirm_attendance" class="btn btn-success btn-confirm w-100 shadow">تایید حضور در کلاس</button>
            </form>
            <a href="dashboard.php" class="btn btn-link mt-3 text-decoration-none text-muted">انصراف</a>
        <?php endif; ?>
    </div>
</body>
</html>

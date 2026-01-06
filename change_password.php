<?php
require_once 'includes/config.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "رمز عبور جدید و تکرار آن با هم مطابقت ندارند.";
    } else {
        // بررسی رمز عبور فعلی
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (password_verify($current_password, $user['password'])) {
            // آپدیت رمز عبور جدید
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $success = "رمز عبور شما با موفقیت تغییر یافت.";
        } else {
            $error = "رمز عبور فعلی اشتباه است.";
        }
    }
}

// تعیین لینک بازگشت بر اساس نقش
$back_link = ($role === 'admin') ? 'admin/dashboard.php' : (($role === 'teacher') ? 'teacher/dashboard.php' : 'student/dashboard.php');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغییر رمز عبور | سامانه هوشمند</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="modern-card p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-shield-lock display-1 text-primary" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        </div>
                        <h3 class="fw-bold">تغییر رمز عبور</h3>
                        <p class="text-muted small">امنیت حساب کاربری شما اولویت ماست</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4 badge-modern text-center small">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4 badge-modern text-center small">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">رمز عبور فعلی</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="current_password" class="form-control form-control-modern border-start-0 rounded-end-3" required>
                            </div>
                        </div>
                        <hr class="my-4 opacity-25">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">رمز عبور جدید</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="new_password" class="form-control form-control-modern border-start-0 rounded-end-3" required minlength="6">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">تکرار رمز عبور جدید</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-lock-check text-muted"></i></span>
                                <input type="password" name="confirm_password" class="form-control form-control-modern border-start-0 rounded-end-3" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-modern btn-modern w-100 py-3 shadow mb-3">
                            تغییر رمز عبور <i class="bi bi-check2-circle ms-2"></i>
                        </button>
                        <a href="<?php echo $back_link; ?>" class="btn btn-light btn-modern w-100 py-2 border">
                            <i class="bi bi-arrow-right me-1"></i> بازگشت به پنل
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

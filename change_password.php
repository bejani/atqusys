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
    <title>تغییر رمز عبور</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f8f9fa; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">تغییر رمز عبور</div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">رمز عبور فعلی</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">رمز عبور جدید</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تکرار رمز عبور جدید</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-success w-100">تغییر رمز</button>
                            <a href="<?php echo $back_link; ?>" class="btn btn-secondary w-100 mt-2">بازگشت</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

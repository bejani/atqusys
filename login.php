<?php
require_once 'includes/config.php';

$error = '';

require_once 'src/autoload.php';
use App\Actions\LoginAction;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $action = new LoginAction();
        $result = $action->handle($username, $password);

        if ($result['success']) {
            switch ($result['role']) {
                case 'admin': header("Location: admin/dashboard.php"); break;
                case 'teacher': header("Location: teacher/dashboard.php"); break;
                case 'student': header("Location: student/dashboard.php"); break;
            }
            exit();
        } else {
            $error = $result['error'];
        }
    } else {
        $error = "لطفاً تمام فیلدها را پر کنید.";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ورود به سیستم حضور و غیاب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: Tahoma, Arial; }
        .login-container { max-width: 400px; margin: 100px auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-container shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4>ورود به سیستم</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">نام کاربری</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رمز عبور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">ورود</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

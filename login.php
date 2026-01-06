<?php
require_once 'includes/config.php';
require_once 'src/autoload.php';
use App\Actions\LoginAction;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سامانه هوشمند حضور و غیاب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="modern-card p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-mortarboard-fill display-1 text-primary" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                        </div>
                        <h3 class="fw-bold">خوش آمدید</h3>
                        <p class="text-muted">وارد حساب کاربری خود شوید</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4 badge-modern text-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <?php csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">نام کاربری</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="username" class="form-control form-control-modern border-start-0 rounded-end-3" placeholder="نام کاربری خود را وارد کنید" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">رمز عبور</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control form-control-modern border-start-0 rounded-end-3" placeholder="رمز عبور خود را وارد کنید" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-modern btn-modern w-100 py-3 shadow">
                            ورود به سامانه <i class="bi bi-arrow-left-short ms-2"></i>
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">سامانه هوشمند حضور و غیاب و کوئیز</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require_once '../includes/config.php';
checkRole(['admin']);

$user_id = $_GET['id'] ?? 0;

// دریافت اطلاعات کاربر
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("کاربر یافت نشد.");

$message = "";

// بروزرسانی اطلاعات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $username = $_POST['username'];

    try {
        // اگر رمز عبور جدید وارد شده باشد
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, full_name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$username, $password, $full_name, $email, $role, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$username, $full_name, $email, $role, $user_id]);
        }
        header("Location: dashboard.php?updated=1");
        exit();
    } catch (PDOException $e) {
        $message = "خطا در بروزرسانی: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ویرایش کاربر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f8f9fa; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">ویرایش اطلاعات کاربر: <?php echo $user['full_name']; ?></div>
                    <div class="card-body">
                        <?php if ($message): ?> <div class="alert alert-danger"><?php echo $message; ?></div> <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">نام کاربری</label>
                                <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">نام و نام خانوادگی</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">ایمیل</label>
                                <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">نقش</label>
                                <select name="role" class="form-select">
                                    <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>دانشجو</option>
                                    <option value="teacher" <?php echo $user['role'] == 'teacher' ? 'selected' : ''; ?>>استاد</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>ادمین</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رمز عبور جدید (اگر نمی‌خواهید تغییر دهید خالی بگذارید)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" name="update_user" class="btn btn-success">ذخیره تغییرات</button>
                                <a href="dashboard.php" class="btn btn-secondary">انصراف</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

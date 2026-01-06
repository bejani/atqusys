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
    verifyCsrfToken($_POST['csrf_token'] ?? '');
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

include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="p-4 border-bottom bg-light bg-opacity-50">
                <h5 class="fw-bold mb-0">ویرایش اطلاعات کاربر: <?php echo $user['full_name']; ?></h5>
            </div>
            <div class="p-4 p-md-5">
                <?php if ($message): ?> 
                    <div class="alert alert-danger badge-modern mb-4"><?php echo $message; ?></div> 
                <?php endif; ?>
                
                <form method="POST">
                    <?php csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">نام و نام خانوادگی</label>
                        <input type="text" name="full_name" class="form-control form-control-modern" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">نام کاربری</label>
                        <input type="text" name="username" class="form-control form-control-modern" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">ایمیل</label>
                        <input type="email" name="email" class="form-control form-control-modern" value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">نقش کاربری</label>
                        <select name="role" class="form-select form-control-modern">
                            <option value="student" <?php echo $user['role'] == 'student' ? 'selected' : ''; ?>>دانشجو</option>
                            <option value="teacher" <?php echo $user['role'] == 'teacher' ? 'selected' : ''; ?>>استاد</option>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>ادمین</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">رمز عبور جدید</label>
                        <input type="password" name="password" class="form-control form-control-modern" placeholder="اگر نمی‌خواهید تغییر دهید خالی بگذارید">
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" name="update_user" class="btn btn-primary-modern btn-modern flex-grow-1 shadow">
                            <i class="bi bi-check-lg me-1"></i> ذخیره تغییرات
                        </button>
                        <a href="dashboard.php" class="btn btn-light btn-modern border px-4">انصراف</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../teacher/footer.php'; ?>

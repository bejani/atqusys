<?php
require_once '../includes/config.php';
checkRole(['admin']);

// آمار کلی
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_teachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// افزودن کاربر جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $role, $email]);
        header("Location: dashboard.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "خطا در ثبت کاربر: " . $e->getMessage();
    }
}

// حذف کاربر
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    if ($id != $_SESSION['user_id']) { // جلوگیری از حذف خود ادمین
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    header("Location: dashboard.php");
    exit();
}

// لیست کاربران
$users = $pdo->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پنل مدیریت سیستم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f0f2f5; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">پنل مدیریت سیستم (ادمین)</span>
            <div>
                <a href="reports.php" class="btn btn-info btn-sm">گزارشات جامع</a>
                <a href="../change_password.php" class="btn btn-outline-warning btn-sm me-2">تغییر رمز</a>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">خروج</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- کارت‌های آمار -->
        <div class="row mb-4 text-center">
            <div class="col-md-3"><div class="card bg-primary text-white p-3"><h5>کل کاربران</h5><h3><?php echo $total_users; ?></h3></div></div>
            <div class="col-md-3"><div class="card bg-success text-white p-3"><h5>اساتید</h5><h3><?php echo $total_teachers; ?></h3></div></div>
            <div class="col-md-3"><div class="card bg-info text-white p-3"><h5>دانشجویان</h5><h3><?php echo $total_students; ?></h3></div></div>
            <div class="col-md-3"><div class="card bg-secondary text-white p-3"><h5>کل دروس</h5><h3><?php echo $total_courses; ?></h3></div></div>
        </div>

        <div class="row">
            <!-- فرم افزودن کاربر -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">افزودن کاربر جدید</div>
                    <div class="card-body">
                        <?php if (isset($error)): ?> <div class="alert alert-danger small"><?php echo $error; ?></div> <?php endif; ?>
                        <form method="POST">
                            <div class="mb-2">
                                <label class="form-label small">نام کاربری</label>
                                <input type="text" name="username" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">رمز عبور</label>
                                <input type="password" name="password" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">نام و نام خانوادگی</label>
                                <input type="text" name="full_name" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">ایمیل</label>
                                <input type="email" name="email" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">نقش کاربری</label>
                                <select name="role" class="form-select form-select-sm" required>
                                    <option value="student">دانشجو</option>
                                    <option value="teacher">استاد</option>
                                    <option value="admin">ادمین</option>
                                </select>
                            </div>
                            <button type="submit" name="add_user" class="btn btn-primary btn-sm w-100">ثبت کاربر</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- لیست کاربران -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">لیست کاربران سیستم</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>نام و نام خانوادگی</th>
                                    <th>نام کاربری</th>
                                    <th>نقش</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo $u['full_name']; ?></td>
                                    <td><?php echo $u['username']; ?></td>
                                    <td>
                                        <?php 
                                            if($u['role'] == 'admin') echo '<span class="badge bg-danger">ادمین</span>';
                                            elseif($u['role'] == 'teacher') echo '<span class="badge bg-success">استاد</span>';
                                            else echo '<span class="badge bg-info">دانشجو</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-outline-primary btn-sm">ویرایش</a>
                                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete_user=<?php echo $u['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

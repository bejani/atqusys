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

include 'header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold text-dark mb-1">مدیریت کاربران</h2>
        <p class="text-muted">کنترل دسترسی‌ها و مدیریت تمامی کاربران سامانه.</p>
    </div>
</div>

<!-- کارت‌های آمار -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="modern-card p-4 text-center border-start border-primary border-5">
            <div class="text-primary mb-2"><i class="bi bi-people fs-1"></i></div>
            <h6 class="text-muted fw-bold">کل کاربران</h6>
            <h3 class="fw-bold mb-0"><?php echo $total_users; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center border-start border-success border-5">
            <div class="text-success mb-2"><i class="bi bi-person-workspace fs-1"></i></div>
            <h6 class="text-muted fw-bold">اساتید</h6>
            <h3 class="fw-bold mb-0"><?php echo $total_teachers; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center border-start border-info border-5">
            <div class="text-info mb-2"><i class="bi bi-mortarboard fs-1"></i></div>
            <h6 class="text-muted fw-bold">دانشجویان</h6>
            <h3 class="fw-bold mb-0"><?php echo $total_students; ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="modern-card p-4 text-center border-start border-warning border-5">
            <div class="text-warning mb-2"><i class="bi bi-book fs-1"></i></div>
            <h6 class="text-muted fw-bold">کل دروس</h6>
            <h3 class="fw-bold mb-0"><?php echo $total_courses; ?></h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- فرم افزودن کاربر -->
    <div class="col-lg-4">
        <div class="modern-card">
            <div class="p-4 border-bottom bg-light bg-opacity-50">
                <h5 class="fw-bold mb-0">افزودن کاربر جدید</h5>
            </div>
            <div class="p-4">
                <?php if (isset($error)): ?> 
                    <div class="alert alert-danger badge-modern small mb-3"><?php echo $error; ?></div> 
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?> 
                    <div class="alert alert-success badge-modern small mb-3">کاربر با موفقیت ثبت شد.</div> 
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">نام و نام خانوادگی</label>
                        <input type="text" name="full_name" class="form-control form-control-modern" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">نام کاربری</label>
                        <input type="text" name="username" class="form-control form-control-modern" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">رمز عبور</label>
                        <input type="password" name="password" class="form-control form-control-modern" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">ایمیل</label>
                        <input type="email" name="email" class="form-control form-control-modern">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">نقش کاربری</label>
                        <select name="role" class="form-select form-control-modern" required>
                            <option value="student">دانشجو</option>
                            <option value="teacher">استاد</option>
                            <option value="admin">ادمین</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary-modern btn-modern w-100 shadow-sm">
                        <i class="bi bi-person-plus me-1"></i> ثبت کاربر جدید
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- لیست کاربران -->
    <div class="col-lg-8">
        <div class="modern-card">
            <div class="p-4 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">لیست کاربران سیستم</h5>
                <span class="badge bg-primary badge-modern"><?php echo count($users); ?> نفر</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3">نام و نام خانوادگی</th>
                            <th class="py-3">نام کاربری</th>
                            <th class="py-3">نقش</th>
                            <th class="pe-4 py-3 text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo $u['full_name']; ?></div>
                                <small class="text-muted"><?php echo $u['email'] ?: 'بدون ایمیل'; ?></small>
                            </td>
                            <td><code><?php echo $u['username']; ?></code></td>
                            <td>
                                <?php 
                                    if($u['role'] == 'admin') echo '<span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-modern">ادمین</span>';
                                    elseif($u['role'] == 'teacher') echo '<span class="badge bg-success-subtle text-success border border-success-subtle badge-modern">استاد</span>';
                                    else echo '<span class="badge bg-info-subtle text-info border border-info-subtle badge-modern">دانشجو</span>';
                                ?>
                            </td>
                            <td class="pe-4 text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-outline-primary" title="ویرایش">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete_user=<?php echo $u['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('آیا از حذف این کاربر مطمئن هستید؟')" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../teacher/footer.php'; ?>

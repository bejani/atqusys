<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$course_id = $_GET['id'] ?? 0;

// بررسی اینکه درس متعلق به این استاد باشد
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    die("درس یافت نشد یا دسترسی ندارید.");
}

// افزودن دانشجو به درس
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_id = $_POST['student_id'];
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO course_students (course_id, student_id) VALUES (?, ?)");
        $stmt->execute([$course_id, $student_id]);
    } catch (PDOException $e) {}
}

// حذف دانشجو از درس
if (isset($_GET['remove'])) {
    $student_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM course_students WHERE course_id = ? AND student_id = ?");
    $stmt->execute([$course_id, $student_id]);
    header("Location: manage_students.php?id=$course_id");
    exit();
}

// لیست دانشجویان فعلی این درس
$stmt = $pdo->prepare("SELECT u.* FROM users u JOIN course_students cs ON u.id = cs.student_id WHERE cs.course_id = ?");
$stmt->execute([$course_id]);
$current_students = $stmt->fetchAll();

// لیست تمام دانشجویان سیستم برای انتخاب
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student'");
$stmt->execute();
$all_students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت دانشجویان - <?php echo $course['course_name']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>مدیریت دانشجویان درس: <?php echo $course['course_name']; ?></h3>
            <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
        </div>

        <div class="row">
            <!-- فرم افزودن دانشجو -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">افزودن دانشجو به این درس</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">انتخاب دانشجو</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">انتخاب کنید...</option>
                                    <?php foreach ($all_students as $s): ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo $s['full_name']; ?> (<?php echo $s['username']; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_student" class="btn btn-success w-100">افزودن به لیست</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- لیست دانشجویان درس -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">دانشجویان ثبت‌نام شده</div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>نام و نام خانوادگی</th>
                                    <th>نام کاربری</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($current_students as $cs): ?>
                                <tr>
                                    <td><?php echo $cs['full_name']; ?></td>
                                    <td><?php echo $cs['username']; ?></td>
                                    <td>
                                        <a href="?id=<?php echo $course_id; ?>&remove=<?php echo $cs['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('آیا از حذف این دانشجو مطمئن هستید؟')">حذف</a>
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

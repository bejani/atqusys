<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$teacher_id = $_SESSION['user_id'];

// دریافت لیست دروس این استاد
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll();

// افزودن درس جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['course_name'];
    $code = $_POST['course_code'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code, teacher_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $code, $teacher_id]);
        header("Location: dashboard.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "خطا در ثبت درس: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پنل استاد - مدیریت دروس</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">خوش آمدید، <?php echo $_SESSION['full_name']; ?></span>
            <div>
                <a href="question_bank.php" class="btn btn-warning btn-sm">مدیریت بانک سوالات</a>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">خروج</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- فرم افزودن درس -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">افزودن درس جدید</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">نام درس</label>
                                <input type="text" name="course_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">کد درس</label>
                                <input type="text" name="course_code" class="form-control" required>
                            </div>
                            <button type="submit" name="add_course" class="btn btn-success w-100">ثبت درس</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- لیست دروس -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">لیست دروس شما</div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>نام درس</th>
                                    <th>کد درس</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo $course['course_name']; ?></td>
                                    <td><?php echo $course['course_code']; ?></td>
                                    <td>
                                        <a href="manage_students.php?id=<?php echo $course['id']; ?>" class="btn btn-info btn-sm">مدیریت دانشجویان</a>
                                        <a href="sessions.php?id=<?php echo $course['id']; ?>" class="btn btn-warning btn-sm">جلسات</a>
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

<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$course_id = $_GET['id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// بررسی مالکیت درس
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
$course = $stmt->fetch();

if (!$course) die("درس یافت نشد.");

$message = "";

// بروزرسانی لیست دانشجویان
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_students'])) {
    $selected_students = $_POST['student_ids'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        // ابتدا تمام دانشجویان فعلی این درس را حذف می‌کنیم
        $stmt = $pdo->prepare("DELETE FROM course_students WHERE course_id = ?");
        $stmt->execute([$course_id]);
        
        // سپس دانشجویان انتخاب شده جدید را اضافه می‌کنیم
        if (!empty($selected_students)) {
            $stmt = $pdo->prepare("INSERT INTO course_students (course_id, student_id) VALUES (?, ?)");
            foreach ($selected_students as $s_id) {
                $stmt->execute([$course_id, $s_id]);
            }
        }
        
        $pdo->commit();
        $message = "لیست دانشجویان با موفقیت به‌روزرسانی شد.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "خطا در ذخیره‌سازی: " . $e->getMessage();
    }
}

// دریافت لیست تمام دانشجویان سیستم
$stmt = $pdo->query("SELECT id, full_name, username FROM users WHERE role = 'student' ORDER BY full_name ASC");
$all_students = $stmt->fetchAll();

// دریافت لیست دانشجویانی که در حال حاضر در این درس هستند
$stmt = $pdo->prepare("SELECT student_id FROM course_students WHERE course_id = ?");
$stmt->execute([$course_id]);
$current_student_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت دانشجویان درس</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body { font-family: Tahoma; background-color: #f8f9fa; }
        .student-list { max-height: 500px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>مدیریت دانشجویان درس: <?php echo $course['course_name']; ?></strong>
                <a href="dashboard.php" class="btn btn-sm btn-light">بازگشت به داشبورد</a>
            </div>
            <div class="card-body">
                <?php if ($message): ?> <div class="alert alert-success"><?php echo $message; ?></div> <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h5>لیست دانشجویان سیستم</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">انتخاب همه</label>
                        </div>
                    </div>
                    
                    <div class="student-list border rounded p-3 bg-white">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">انتخاب</th>
                                    <th>نام و نام خانوادگی</th>
                                    <th>نام کاربری (شماره دانشجویی)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_students as $student): ?>
                                <tr>
                                    <td>
                                        <input class="form-check-input student-checkbox" type="checkbox" name="student_ids[]" 
                                               value="<?php echo $student['id']; ?>"
                                               <?php echo in_array($student['id'], $current_student_ids) ? 'checked' : ''; ?>>
                                    </td>
                                    <td><?php echo $student['full_name']; ?></td>
                                    <td><?php echo $student['username']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" name="save_students" class="btn btn-success px-5">ذخیره تغییرات لیست</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // اسکریپت انتخاب همه
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
</body>
</html>

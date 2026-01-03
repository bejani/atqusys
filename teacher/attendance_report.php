<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$session_id = $_GET['session_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// دریافت اطلاعات جلسه و درس
$stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ? AND c.teacher_id = ?");
$stmt->execute([$session_id, $teacher_id]);
$session = $stmt->fetch();

if (!$session) die("جلسه یافت نشد.");

// لیست دانشجویان درس و وضعیت حضور آن‌ها
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, a.scanned_at, a.status 
    FROM course_students cs
    JOIN users u ON cs.student_id = u.id
    LEFT JOIN attendance a ON a.student_id = u.id AND a.session_id = ?
    WHERE cs.course_id = ?
");
$stmt->execute([$session_id, $session['course_id']]);
$attendance_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارش حضور و غیاب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <strong>گزارش حضور: <?php echo $session['course_name']; ?> (<?php echo $session['session_date']; ?>)</strong>
                <div>
                    <a href="export_attendance.php?session_id=<?php echo $session_id; ?>" class="btn btn-sm btn-success">خروجی اکسل (CSV)</a>
                    <a href="sessions.php?id=<?php echo $session['course_id']; ?>" class="btn btn-sm btn-secondary">بازگشت</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>نام دانشجو</th>
                            <th>نام کاربری</th>
                            <th>وضعیت</th>
                            <th>زمان ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_list as $row): ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <?php if ($row['status']): ?>
                                    <span class="badge bg-success">حاضر</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">غایب</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $row['scanned_at'] ?? '---'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

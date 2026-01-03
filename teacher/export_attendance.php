<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$session_id = $_GET['session_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ? AND c.teacher_id = ?");
$stmt->execute([$session_id, $teacher_id]);
$session = $stmt->fetch();

if (!$session) die("دسترسی غیرمجاز");

// دریافت داده‌ها
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, IF(a.status IS NULL, 'Absent', 'Present') as attendance_status, a.scanned_at 
    FROM course_students cs
    JOIN users u ON cs.student_id = u.id
    LEFT JOIN attendance a ON a.student_id = u.id AND a.session_id = ?
    WHERE cs.course_id = ?
");
$stmt->execute([$session_id, $session['course_id']]);
$data = $stmt->fetchAll();

// تنظیم هدرها برای دانلود فایل CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_' . $session['session_date'] . '.csv');

$output = fopen('php://output', 'w');
// افزودن BOM برای نمایش صحیح کاراکترهای فارسی در اکسل
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// عنوان ستون‌ها
fputcsv($output, ['نام دانشجو', 'نام کاربری', 'وضعیت حضور', 'زمان ثبت']);

foreach ($data as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>

<?php
require_once '../includes/config.php';

$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$full_name = 'مدیر سیستم';
$role = 'admin';

try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $full_name, $role]);
    echo "کاربر ادمین با موفقیت ایجاد شد.\n";
    echo "نام کاربری: admin\nرمز عبور: admin123\n";

    // ایجاد یک استاد نمونه
    $teacher_pass = password_hash('teacher123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)")
        ->execute(['teacher1', $teacher_pass, 'دکتر علوی', 'teacher']);
    echo "استاد نمونه ایجاد شد: teacher1 / teacher123\n";

    // ایجاد چند دانشجو نمونه
    $student_pass = password_hash('student123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)")
        ->execute(['student1', $student_pass, 'رضا محمدی', 'student']);
    $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)")
        ->execute(['student2', $student_pass, 'مریم حسینی', 'student']);
    echo "دانشجویان نمونه ایجاد شدند: student1, student2 / student123\n";

} catch (PDOException $e) {
    echo "خطا: " . $e->getMessage();
}
?>

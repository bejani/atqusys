<?php
require_once '../includes/config.php';
checkRole(['admin']);

// دریافت لیست تمامی دروس به همراه نام استاد
$stmt = $pdo->query("SELECT c.*, u.full_name as teacher_name FROM courses c JOIN users u ON c.teacher_id = u.id");
$courses = $stmt->fetchAll();

$selected_course_id = $_GET['course_id'] ?? 0;
$sessions = [];
if ($selected_course_id) {
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE course_id = ? ORDER BY session_date DESC");
    $stmt->execute([$selected_course_id]);
    $sessions = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>گزارشات جامع سیستم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f8f9fa; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">گزارشات مدیریتی</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">بازگشت به پنل</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- انتخاب درس -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">انتخاب درس</div>
                    <div class="card-body">
                        <form method="GET">
                            <select name="course_id" class="form-select mb-3" onchange="this.form.submit()">
                                <option value="">یک درس انتخاب کنید...</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $selected_course_id == $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo $c['course_name']; ?> (استاد: <?php echo $c['teacher_name']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            <!-- نمایش جلسات و گزارشات -->
            <div class="col-md-8">
                <?php if ($selected_course_id): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">جلسات و فعالیت‌های درس</div>
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>تاریخ جلسه</th>
                                        <th>گزارش حضور</th>
                                        <th>گزارش نمرات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $s): ?>
                                    <tr>
                                        <td><?php echo $s['session_date']; ?></td>
                                        <td>
                                            <a href="../teacher/attendance_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-info" target="_blank">مشاهده حضور</a>
                                        </td>
                                        <td>
                                            <?php 
                                                $q_stmt = $pdo->prepare("SELECT id FROM quizzes WHERE session_id = ?");
                                                $q_stmt->execute([$s['id']]);
                                                if ($q_stmt->fetch()):
                                            ?>
                                                <a href="../teacher/quiz_report.php?session_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-warning" target="_blank">مشاهده نمرات</a>
                                            <?php else: ?>
                                                <span class="text-muted small">بدون کوئیز</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">لطفاً برای مشاهده گزارشات، ابتدا یک درس را از منوی سمت راست انتخاب کنید.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

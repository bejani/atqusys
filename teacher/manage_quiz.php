<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$session_id = $_GET['session_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// دریافت اطلاعات جلسه
$stmt = $pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ? AND c.teacher_id = ?");
$stmt->execute([$session_id, $teacher_id]);
$session = $stmt->fetch();

if (!$session) die("جلسه یافت نشد.");

// ایجاد یا بروزرسانی کوئیز
if (isset($_POST['save_quiz'])) {
    $title = $_POST['title'];
    $duration = $_POST['duration'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $selected_questions = $_POST['questions'] ?? [];

    // ۱. درج یا آپدیت در جدول quizzes
    $stmt = $pdo->prepare("INSERT INTO quizzes (session_id, title, duration_minutes, is_active) 
                           VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=?, duration_minutes=?, is_active=?");
    $stmt->execute([$session_id, $title, $duration, $is_active, $title, $duration, $is_active]);
    
    $quiz_id = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM quizzes WHERE session_id = $session_id")->fetchColumn();

    // ۲. مدیریت سوالات کوئیز (ابتدا قبلی‌ها را پاک می‌کنیم و جدیدها را اضافه می‌کنیم)
    $pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?")->execute([$quiz_id]);
    foreach ($selected_questions as $q_id) {
        $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question_id) VALUES (?, ?)")->execute([$quiz_id, $q_id]);
    }
    header("Location: manage_quiz.php?session_id=$session_id&success=1");
    exit();
}

// دریافت اطلاعات کوئیز فعلی (اگر وجود داشته باشد)
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE session_id = ?");
$stmt->execute([$session_id]);
$quiz = $stmt->fetch();
$quiz_id = $quiz['id'] ?? 0;

// دریافت سوالات انتخاب شده
$selected_q_ids = [];
if ($quiz_id) {
    $selected_q_ids = $pdo->query("SELECT question_id FROM quiz_questions WHERE quiz_id = $quiz_id")->fetchAll(PDO::FETCH_COLUMN);
}

// دریافت تمام سوالات بانک برای انتخاب
$stmt = $pdo->prepare("SELECT * FROM question_bank WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$bank_questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت کوئیز جلسه</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark d-flex justify-content-between">
                <strong>تنظیم کوئیز برای درس: <?php echo $session['course_name']; ?> (جلسه <?php echo $session['session_date']; ?>)</strong>
                <a href="sessions.php?id=<?php echo $session['course_id']; ?>" class="btn btn-sm btn-outline-dark">بازگشت</a>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">عنوان کوئیز</label>
                            <input type="text" name="title" class="form-control" value="<?php echo $quiz['title'] ?? 'کوئیز کلاسی'; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">زمان (دقیقه)</label>
                            <input type="number" name="duration" class="form-control" value="<?php echo $quiz['duration_minutes'] ?? 10; ?>" required>
                        </div>
                        <div class="col-md-3 pt-4">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" <?php echo ($quiz['is_active'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label">کوئیز فعال باشد</label>
                            </div>
                        </div>
                    </div>

                    <h5>انتخاب سوالات از بانک سوالات:</h5>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>انتخاب</th>
                                    <th>متن سوال</th>
                                    <th>دسته‌بندی</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bank_questions as $bq): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="questions[]" value="<?php echo $bq['id']; ?>" 
                                               <?php echo in_array($bq['id'], $selected_q_ids) ? 'checked' : ''; ?>>
                                    </td>
                                    <td><?php echo $bq['question_text']; ?></td>
                                    <td><?php echo $bq['category']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="save_quiz" class="btn btn-primary mt-3 w-100">ذخیره تنظیمات کوئیز</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

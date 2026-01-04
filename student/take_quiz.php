<?php
require_once '../includes/config.php';
checkRole(['student']);

$quiz_id = $_GET['quiz_id'] ?? 0;
$student_id = $_SESSION['user_id'];

// دریافت اطلاعات کوئیز و سوالات
$stmt = $pdo->prepare("SELECT q.*, s.course_id FROM quizzes q JOIN sessions s ON q.session_id = s.id WHERE q.id = ? AND q.is_active = 1");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) die("کوئیز فعال یافت نشد.");

// بررسی اینکه آیا دانشجو قبلاً شرکت کرده است؟
$stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
$stmt->execute([$quiz_id, $student_id]);
$previous_result = $stmt->fetch();

if ($previous_result) {
    // اگر قبلاً شرکت کرده، نمره را نشان بده
    $message = "شما قبلاً در این کوئیز شرکت کرده‌اید. نمره شما: " . $previous_result['score'];
}

// دریافت سوالات کوئیز
$stmt = $pdo->prepare("SELECT qb.* FROM question_bank qb JOIN quiz_questions qq ON qb.id = qq.question_id WHERE qq.quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

// پردازش پاسخ‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $answers = $_POST['answers'] ?? [];
    $correct_count = 0;
    $total_questions = count($questions);

    foreach ($questions as $q) {
        if (isset($answers[$q['id']]) && trim(strtolower($answers[$q['id']])) === trim(strtolower($q['correct_option']))) {
            $correct_count++;
        }
    }

    $score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 20, 2) : 0; // نمره از ۲۰ با دو رقم اعشار

    try {
        $stmt = $pdo->prepare("INSERT INTO quiz_results (quiz_id, student_id, score) VALUES (?, ?, ?)");
        $stmt->execute([$quiz_id, $student_id, $score]);
        
        // هدایت به داشبورد با پیام موفقیت
        header("Location: dashboard.php?quiz_success=1&score=$score");
        exit();
    } catch (PDOException $e) {
        $error = "خطا در ثبت نتیجه: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo $quiz['title']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f8f9fa; }</style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><?php echo $quiz['title']; ?></h4>
                        <span class="badge bg-light text-dark">زمان: <?php echo $quiz['duration_minutes']; ?> دقیقه</span>
                    </div>
                    <div class="card-body">
                        <?php if (isset($message)): ?>
                            <div class="alert alert-info text-center">
                                <?php echo $message; ?>
                                <br><br>
                                <a href="dashboard.php" class="btn btn-primary">بازگشت به داشبورد</a>
                            </div>
                        <?php elseif (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php else: ?>
                            <form method="POST" id="quizForm">
                                <?php foreach ($questions as $index => $q): ?>
                                <div class="mb-4 p-3 border rounded bg-white shadow-sm">
                                    <p class="mb-3"><strong>سوال <?php echo $index + 1; ?>:</strong> <?php echo $q['question_text']; ?></p>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="a" id="q<?php echo $q['id']; ?>a" required>
                                        <label class="form-check-label" for="q<?php echo $q['id']; ?>a">الف) <?php echo $q['option_a']; ?></label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="b" id="q<?php echo $q['id']; ?>b">
                                        <label class="form-check-label" for="q<?php echo $q['id']; ?>b">ب) <?php echo $q['option_b']; ?></label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="c" id="q<?php echo $q['id']; ?>c">
                                        <label class="form-check-label" for="q<?php echo $q['id']; ?>c">ج) <?php echo $q['option_c']; ?></label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="d" id="q<?php echo $q['id']; ?>d">
                                        <label class="form-check-label" for="q<?php echo $q['id']; ?>d">د) <?php echo $q['option_d']; ?></label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <button type="submit" name="submit_quiz" class="btn btn-success btn-lg w-100 mt-3 shadow" onclick="return confirm('آیا از ارسال پاسخ‌ها و پایان کوئیز مطمئن هستید؟')">ارسال و پایان کوئیز</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

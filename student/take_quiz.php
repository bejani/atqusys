<?php
require_once '../includes/config.php';
checkRole(['student']);

require_once '../src/autoload.php';
use App\Actions\QuizAction;
use App\Domain\Database;

$quizAction = new QuizAction();
$quiz_id = $_GET['quiz_id'] ?? 0;
$student_id = $_SESSION['user_id'];

// دریافت اطلاعات کوئیز
$quiz = $quizAction->getQuizForStudent($quiz_id);
if (!$quiz || $quiz['is_active'] == 0) die("کوئیز فعال یافت نشد.");

// بررسی حضور و غیاب (نیاز به دسترسی مستقیم به دیتابیس برای این مورد خاص یا افزودن به اکشن)
$pdo = Database::getInstance();
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE session_id = ? AND student_id = ?");
$stmt->execute([$quiz['session_id'], $student_id]);
if (!$stmt->fetch()) {
    include 'header.php';
    echo '<div class="modern-card p-5 text-center mt-5">
            <i class="bi bi-exclamation-octagon display-1 text-danger mb-4"></i>
            <h3 class="fw-bold">خطای دسترسی</h3>
            <p class="text-muted">شما ابتدا باید حضور خود را در این جلسه ثبت کنید تا بتوانید در کوئیز شرکت نمایید.</p>
            <a href="dashboard.php" class="btn btn-primary-modern btn-modern px-5 mt-3">بازگشت به داشبورد</a>
          </div>';
    include 'footer.php';
    exit();
}

// بررسی شرکت قبلی
$stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE quiz_id = ? AND student_id = ?");
$stmt->execute([$quiz_id, $student_id]);
$previous_result = $stmt->fetch();

if ($previous_result) {
    include 'header.php';
    echo '<div class="modern-card p-5 text-center mt-5">
            <i class="bi bi-check-circle-fill display-1 text-success mb-4"></i>
            <h3 class="fw-bold">قبلاً شرکت کرده‌اید</h3>
            <p class="text-muted">شما قبلاً در این کوئیز شرکت کرده‌اید و نمره شما ثبت شده است.</p>
            <div class="display-4 fw-bold text-primary mb-4">' . $previous_result['score'] . ' <small class="fs-6 text-muted">از ۲۰</small></div>
            <a href="dashboard.php" class="btn btn-primary-modern btn-modern px-5">بازگشت به داشبورد</a>
          </div>';
    include 'footer.php';
    exit();
}

// دریافت سوالات
$questions = $quizAction->getFullQuizQuestions($quiz_id);

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

    $score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 20, 2) : 0;

    if ($quizAction->submitQuizResult($student_id, $quiz_id, $score)) {
        header("Location: dashboard.php?quiz_success=1&score=$score");
        exit();
    }
}

include 'header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="modern-card overflow-hidden mb-5">
            <div class="p-4 bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0"><?php echo $quiz['title']; ?></h4>
                    <small class="opacity-75"><?php echo $quiz['course_name']; ?></small>
                </div>
                <div class="text-end">
                    <div class="badge bg-white text-primary badge-modern py-2 px-3">
                        <i class="bi bi-clock-history me-1"></i> زمان: <?php echo $quiz['duration_minutes']; ?> دقیقه
                    </div>
                </div>
            </div>
            
            <div class="p-4 p-md-5">
                <form method="POST" id="quizForm">
                    <?php foreach ($questions as $index => $q): ?>
                    <div class="mb-5">
                        <div class="d-flex align-items-start mb-3">
                            <span class="badge bg-primary rounded-circle me-3 mt-1" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;"><?php echo $index + 1; ?></span>
                            <h5 class="fw-bold mb-0 lh-base"><?php echo $q['question_text']; ?></h5>
                        </div>
                        
                        <div class="row g-3 ms-md-4">
                            <?php foreach (['a' => 'الف', 'b' => 'ب', 'c' => 'ج', 'd' => 'د'] as $key => $label): ?>
                            <div class="col-md-6">
                                <label class="form-check-label w-100" for="q<?php echo $q['id'] . $key; ?>">
                                    <input class="form-check-input d-none quiz-radio" type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $key; ?>" id="q<?php echo $q['id'] . $key; ?>" required>
                                    <div class="p-3 rounded-4 border bg-light quiz-option-card transition-all">
                                        <span class="fw-bold me-2"><?php echo $label; ?>)</span> <?php echo $q['option_' . $key]; ?>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-5 pt-4 border-top">
                        <button type="submit" name="submit_quiz" class="btn btn-primary-modern btn-modern w-100 py-3 shadow-lg fs-5" onclick="return confirm('آیا از ارسال پاسخ‌ها و پایان کوئیز مطمئن هستید؟')">
                            <i class="bi bi-send-check me-2"></i> ارسال و پایان کوئیز
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .quiz-radio:checked + .quiz-option-card {
        background: var(--primary-gradient) !important;
        color: white !important;
        border-color: transparent !important;
        transform: scale(1.02);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .quiz-option-card:hover {
        border-color: #667eea;
        cursor: pointer;
    }
    .transition-all { transition: all 0.2s ease; }
</style>

<?php include 'footer.php'; ?>

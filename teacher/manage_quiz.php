<?php
require_once '../includes/config.php';
checkRole(['teacher']);

require_once '../src/autoload.php';
use App\Actions\QuizAction;
use App\Actions\QuestionAction;

$quizAction = new QuizAction();
$questionAction = new QuestionAction();

$session_id = $_GET['session_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// دریافت اطلاعات جلسه
$session = $quizAction->getSessionWithCourse($session_id, $teacher_id);
if (!$session) die("جلسه یافت نشد.");

$message = "";

// ایجاد یا بروزرسانی کوئیز
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quiz'])) {
    $data = [
        'title' => $_POST['title'],
        'duration' => $_POST['duration'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];
    $selected_questions = $_POST['questions'] ?? [];

    if ($quizAction->saveQuiz($session_id, $data, $selected_questions)) {
        header("Location: manage_quiz.php?session_id=$session_id&success=1");
        exit();
    } else {
        $message = "خطا در ذخیره تنظیمات کوئیز.";
    }
}

// دریافت اطلاعات کوئیز فعلی
$quiz = $quizAction->getQuizBySession($session_id);
$quiz_id = $quiz['id'] ?? 0;
$selected_q_ids = $quiz_id ? $quizAction->getQuizQuestions($quiz_id) : [];

// دریافت تمام سوالات بانک
$bank_questions = $questionAction->getTeacherQuestions($teacher_id);

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="sessions.php?id=<?php echo $session['course_id']; ?>" class="text-decoration-none"><?php echo $session['course_name']; ?></a></li>
                <li class="breadcrumb-item active">تنظیم کوئیز</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">تنظیم کوئیز جلسه</h2>
                <p class="text-muted">جلسه مورخ <?php echo $session['session_date']; ?> - درس <?php echo $session['course_name']; ?></p>
            </div>
            <a href="sessions.php?id=<?php echo $session['course_id']; ?>" class="btn btn-light btn-modern border shadow-sm">
                <i class="bi bi-arrow-right me-1"></i> بازگشت به جلسات
            </a>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm badge-modern mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> تنظیمات کوئیز با موفقیت ذخیره شد.
    </div>
<?php endif; ?>

<form method="POST">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="modern-card p-4 sticky-top" style="top: 100px;">
                <h5 class="fw-bold mb-4 border-bottom pb-2">تنظیمات کلی</h5>
                <div class="mb-3">
                    <label class="form-label fw-semibold">عنوان کوئیز</label>
                    <input type="text" name="title" class="form-control form-control-modern" value="<?php echo $quiz['title'] ?? 'کوئیز کلاسی'; ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">زمان آزمون (دقیقه)</label>
                    <div class="input-group">
                        <input type="number" name="duration" class="form-control form-control-modern" value="<?php echo $quiz['duration_minutes'] ?? 10; ?>" required>
                        <span class="input-group-text bg-white border-start-0 rounded-end-3"><i class="bi bi-clock text-muted"></i></span>
                    </div>
                </div>
                <div class="mb-4 p-3 rounded-4 bg-light border">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="quizActive" <?php echo ($quiz['is_active'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="quizActive">وضعیت: فعال برای دانشجو</label>
                    </div>
                    <small class="text-muted d-block mt-1">در صورت غیرفعال بودن، دانشجویان امکان مشاهده و شرکت در آزمون را نخواهند داشت.</small>
                </div>
                <button type="submit" name="save_quiz" class="btn btn-primary-modern btn-modern w-100 py-3 shadow">
                    <i class="bi bi-save me-1"></i> ذخیره نهایی کوئیز
                </button>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="modern-card">
                <div class="p-4 border-bottom bg-light bg-opacity-50 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">انتخاب سوالات از بانک</h5>
                    <span class="badge bg-dark badge-modern" id="selectedCount">0 سوال انتخاب شده</span>
                </div>
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4 py-3" width="80">انتخاب</th>
                                <th class="py-3">متن سوال</th>
                                <th class="pe-4 py-3">دسته‌بندی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bank_questions)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <p class="text-muted mb-3">بانک سوالات شما خالی است.</p>
                                        <a href="question_bank.php" class="btn btn-sm btn-outline-primary btn-modern">ایجاد اولین سوال</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bank_questions as $bq): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="form-check">
                                            <input class="form-check-input question-checkbox" type="checkbox" name="questions[]" value="<?php echo $bq['id']; ?>" 
                                                   <?php echo in_array($bq['id'], $selected_q_ids) ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo $bq['question_text']; ?></div>
                                        <small class="text-muted">گزینه صحیح: <?php echo strtoupper($bq['correct_option']); ?></small>
                                    </td>
                                    <td class="pe-4">
                                        <span class="badge bg-light text-dark border badge-modern"><?php echo $bq['category'] ?: 'عمومی'; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    function updateCount() {
        const count = document.querySelectorAll('.question-checkbox:checked').length;
        document.getElementById('selectedCount').innerText = count + ' سوال انتخاب شده';
    }
    
    document.querySelectorAll('.question-checkbox').forEach(cb => {
        cb.addEventListener('change', updateCount);
    });
    
    updateCount();
</script>

<?php include 'footer.php'; ?>

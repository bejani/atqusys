<?php
require_once '../includes/config.php';
checkRole(['teacher']);

require_once '../src/autoload.php';
use App\Actions\QuestionAction;

$questionAction = new QuestionAction();
$teacher_id = $_SESSION['user_id'];

// افزودن سوال جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    verifyCsrfToken($_POST['csrf_token'] ?? '');
    $data = [
        'text' => $_POST['question_text'],
        'a' => $_POST['option_a'],
        'b' => $_POST['option_b'],
        'c' => $_POST['option_c'],
        'd' => $_POST['option_d'],
        'correct' => $_POST['correct_option'],
        'category' => $_POST['category']
    ];

    if ($questionAction->addQuestion($teacher_id, $data)) {
        header("Location: question_bank.php?success=1");
        exit();
    }
}

// حذف سوال
if (isset($_GET['delete'])) {
    if ($questionAction->deleteQuestion($_GET['delete'], $teacher_id)) {
        header("Location: question_bank.php?deleted=1");
        exit();
    }
}

// لیست سوالات
$questions = $questionAction->getTeacherQuestions($teacher_id);

include 'header.php'; 
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">بانک سوالات</h2>
                <p class="text-muted">سوالات خود را برای استفاده در کوئیزها مدیریت کنید.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="import_questions.php" class="btn btn-outline-primary btn-modern shadow-sm">
                    <i class="bi bi-file-earmark-word me-1"></i> ایمپورت از Word
                </a>
                <button class="btn btn-primary-modern btn-modern shadow-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                    <i class="bi bi-plus-lg me-1"></i> افزودن سوال جدید
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success border-0 shadow-sm badge-modern mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> سوال جدید با موفقیت به بانک سوالات اضافه شد.
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if (empty($questions)): ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-chat-square-quote display-1 text-muted opacity-25"></i>
            <p class="mt-3 text-muted">هنوز سوالی در بانک سوالات خود ثبت نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <?php foreach ($questions as $q): ?>
            <div class="col-12">
                <div class="modern-card p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary badge-modern mb-2">
                                <i class="bi bi-tag me-1"></i> <?php echo $q['category'] ?: 'بدون دسته‌بندی'; ?>
                            </span>
                            <h5 class="fw-bold mb-0"><?php echo $q['question_text']; ?></h5>
                        </div>
                        <a href="?delete=<?php echo $q['id']; ?>" class="btn btn-outline-danger btn-sm border-0 rounded-3" onclick="return confirm('آیا از حذف این سوال مطمئن هستید؟')">
                            <i class="bi bi-trash fs-5"></i>
                        </a>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="p-2 rounded-3 border <?php echo $q['correct_option'] == 'a' ? 'bg-success bg-opacity-10 border-success' : 'bg-light'; ?>">
                                <span class="fw-bold me-2">الف:</span> <?php echo $q['option_a']; ?>
                                <?php if($q['correct_option'] == 'a') echo '<i class="bi bi-check-circle-fill text-success float-end mt-1"></i>'; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 rounded-3 border <?php echo $q['correct_option'] == 'b' ? 'bg-success bg-opacity-10 border-success' : 'bg-light'; ?>">
                                <span class="fw-bold me-2">ب:</span> <?php echo $q['option_b']; ?>
                                <?php if($q['correct_option'] == 'b') echo '<i class="bi bi-check-circle-fill text-success float-end mt-1"></i>'; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 rounded-3 border <?php echo $q['correct_option'] == 'c' ? 'bg-success bg-opacity-10 border-success' : 'bg-light'; ?>">
                                <span class="fw-bold me-2">ج:</span> <?php echo $q['option_c']; ?>
                                <?php if($q['correct_option'] == 'c') echo '<i class="bi bi-check-circle-fill text-success float-end mt-1"></i>'; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 rounded-3 border <?php echo $q['correct_option'] == 'd' ? 'bg-success bg-opacity-10 border-success' : 'bg-light'; ?>">
                                <span class="fw-bold me-2">د:</span> <?php echo $q['option_d']; ?>
                                <?php if($q['correct_option'] == 'd') echo '<i class="bi bi-check-circle-fill text-success float-end mt-1"></i>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal افزودن سوال -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">افزودن سوال جدید به بانک</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">متن سوال</label>
                        <textarea name="question_text" class="form-control form-control-modern" rows="3" placeholder="متن سوال را اینجا بنویسید..." required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">گزینه الف</label>
                            <input type="text" name="option_a" class="form-control form-control-modern" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">گزینه ب</label>
                            <input type="text" name="option_b" class="form-control form-control-modern" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">گزینه ج</label>
                            <input type="text" name="option_c" class="form-control form-control-modern" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">گزینه د</label>
                            <input type="text" name="option_d" class="form-control form-control-modern" required>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">گزینه صحیح</label>
                            <select name="correct_option" class="form-select form-control-modern" required>
                                <option value="a">گزینه الف</option>
                                <option value="b">گزینه ب</option>
                                <option value="c">گزینه ج</option>
                                <option value="d">گزینه د</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">دسته‌بندی</label>
                            <input type="text" name="category" class="form-control form-control-modern" placeholder="مثلاً: فصل اول">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-modern" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_question" class="btn btn-primary-modern btn-modern px-4">
                        <i class="bi bi-save me-1"></i> ذخیره در بانک سوالات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

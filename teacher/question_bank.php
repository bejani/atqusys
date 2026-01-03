<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$teacher_id = $_SESSION['user_id'];

// افزودن سوال جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $text = $_POST['question_text'];
    $a = $_POST['option_a'];
    $b = $_POST['option_b'];
    $c = $_POST['option_c'];
    $d = $_POST['option_d'];
    $correct = $_POST['correct_option'];
    $category = $_POST['category'];

    $stmt = $pdo->prepare("INSERT INTO question_bank (teacher_id, question_text, option_a, option_b, option_c, option_d, correct_option, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $text, $a, $b, $c, $d, $correct, $category]);
    header("Location: question_bank.php?success=1");
    exit();
}

// حذف سوال
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM question_bank WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$_GET['delete'], $teacher_id]);
    header("Location: question_bank.php");
    exit();
}

// لیست سوالات
$stmt = $pdo->prepare("SELECT * FROM question_bank WHERE teacher_id = ? ORDER BY created_at DESC");
$stmt->execute([$teacher_id]);
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بانک سوالات</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f4f7f6; }</style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>بانک سوالات شما</h3>
            <a href="dashboard.php" class="btn btn-secondary">بازگشت به داشبورد</a>
        </div>

        <div class="row">
            <!-- فرم افزودن سوال -->
            <div class="col-md-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">افزودن سوال جدید</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">متن سوال</label>
                                <textarea name="question_text" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-2"><input type="text" name="option_a" class="form-control" placeholder="گزینه الف" required></div>
                                <div class="col-6 mb-2"><input type="text" name="option_b" class="form-control" placeholder="گزینه ب" required></div>
                                <div class="col-6 mb-2"><input type="text" name="option_c" class="form-control" placeholder="گزینه ج" required></div>
                                <div class="col-6 mb-2"><input type="text" name="option_d" class="form-control" placeholder="گزینه د" required></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">گزینه صحیح</label>
                                <select name="correct_option" class="form-select" required>
                                    <option value="a">گزینه الف</option>
                                    <option value="b">گزینه ب</option>
                                    <option value="c">گزینه ج</option>
                                    <option value="d">گزینه د</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">دسته‌بندی (اختیاری)</label>
                                <input type="text" name="category" class="form-control" placeholder="مثلاً: آناتومی">
                            </div>
                            <button type="submit" name="add_question" class="btn btn-success w-100">ذخیره در بانک سوالات</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- لیست سوالات موجود -->
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">سوالات ذخیره شده</div>
                    <div class="card-body">
                        <?php foreach ($questions as $q): ?>
                        <div class="border-bottom mb-3 pb-2">
                            <strong><?php echo $q['question_text']; ?></strong>
                            <div class="small text-muted mt-1">
                                الف: <?php echo $q['option_a']; ?> | ب: <?php echo $q['option_b']; ?> | 
                                ج: <?php echo $q['option_c']; ?> | د: <?php echo $q['option_d']; ?>
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-success">پاسخ صحیح: <?php echo strtoupper($q['correct_option']); ?></span>
                                <a href="?delete=<?php echo $q['id']; ?>" class="text-danger float-start" onclick="return confirm('حذف شود؟')">حذف</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

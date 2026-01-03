<?php
require_once '../includes/config.php';
checkRole(['teacher']);

$teacher_id = $_SESSION['user_id'];
$message = "";

/**
 * تابع ساده برای استخراج متن از فایل .docx بدون نیاز به کتابخانه‌های سنگین
 */
function read_docx($filename) {
    $striped_content = '';
    $content = '';
    if (!$filename || !file_exists($filename)) return false;
    $zip = zip_open($filename);
    if (!$zip || is_numeric($zip)) return false;
    while ($zip_entry = zip_read($zip)) {
        if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
        if (zip_entry_name($zip_entry) != "word/document.xml") continue;
        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
        zip_entry_close($zip_entry);
    }
    zip_close($zip);
    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\n", $content);
    $striped_content = strip_tags($content);
    return $striped_content;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['word_file'])) {
    $file = $_FILES['word_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

    if ($ext !== 'docx') {
        $message = "لطفاً فقط فایل با پسوند .docx آپلود کنید.";
    } else {
        $text = read_docx($file['tmp_name']);
        if ($text) {
            $lines = explode("\n", $text);
            $imported_count = 0;

            foreach ($lines as $line) {
                $parts = explode("|", $line);
                // فرمت: سوال | گزینه1 | گزینه2 | گزینه3 | گزینه4 | پاسخ(a,b,c,d)
                if (count($parts) >= 6) {
                    $q_text = trim($parts[0]);
                    $a = trim($parts[1]);
                    $b = trim($parts[2]);
                    $c = trim($parts[3]);
                    $d = trim($parts[4]);
                    $correct = strtolower(trim($parts[5]));
                    $category = isset($parts[6]) ? trim($parts[6]) : 'Imported';

                    if (in_array($correct, ['a', 'b', 'c', 'd'])) {
                        $stmt = $pdo->prepare("INSERT INTO question_bank (teacher_id, question_text, option_a, option_b, option_c, option_d, correct_option, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$teacher_id, $q_text, $a, $b, $c, $d, $correct, $category]);
                        $imported_count++;
                    }
                }
            }
            $message = "تعداد $imported_count سوال با موفقیت ایمپورت شد.";
        } else {
            $message = "خطا در خواندن فایل ورد.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>ایمپورت سوالات از ورد</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>body { font-family: Tahoma; background-color: #f8f9fa; }</style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">ایمپورت سوالات از فایل Word (.docx)</div>
                    <div class="card-body">
                        <?php if ($message): ?> <div class="alert alert-info"><?php echo $message; ?></div> <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <strong>راهنما:</strong> هر سوال باید در یک خط مجزا و با فرمت زیر باشد: <br>
                            <code>متن سوال | گزینه الف | گزینه ب | گزینه ج | گزینه د | پاسخ صحیح (a یا b یا c یا d)</code>
                            <br><br>
                            <strong>مثال:</strong> <br>
                            <code>پایتخت ایران کدام است؟ | شیراز | تهران | اصفهان | تبریز | b</code>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">انتخاب فایل ورد (.docx)</label>
                                <input type="file" name="word_file" class="form-control" accept=".docx" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-success">شروع ایمپورت</button>
                                <a href="question_bank.php" class="btn btn-secondary">بازگشت به بانک سوالات</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php

/**
 * تنظیمات پایگاه داده
 * در محیط لاراگون معمولاً نام کاربری root و رمز عبور خالی است.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'atqusysdb');
define('DB_USER', 'root');
define('DB_PASS', '4562');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // تنظیم حالت خطا برای PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

// شروع سشن برای مدیریت ورود کاربران
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * تابع کمکی برای بررسی دسترسی نقش‌ها
 */
function checkRole($allowedRoles)
{
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowedRoles)) {
        header("Location: ../login.php?error=access_denied");
        exit();
    }
}

/**
 * تولید توکن CSRF
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * تایید توکن CSRF
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("خطای امنیتی: توکن CSRF نامعتبر است.");
    }
    return true;
}

/**
 * چاپ فیلد مخفی CSRF برای فرم‌ها
 */
function csrfField() {
    echo '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

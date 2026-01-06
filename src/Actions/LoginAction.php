<?php
namespace App\Actions;

use App\Domain\Database;

class LoginAction {
    public function handle($username, $password) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            return ['success' => true, 'role' => $user['role']];
        }

        return ['success' => false, 'error' => 'نام کاربری یا رمز عبور اشتباه است.'];
    }
}

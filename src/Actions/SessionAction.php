<?php
namespace App\Actions;

use App\Domain\Database;
use PDO;

class SessionAction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getCourseSessions($course_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM sessions WHERE course_id = ? ORDER BY created_at DESC");
        $stmt->execute([course_id]);
        return $stmt->fetchAll();
    }

    public function createSession($course_id) {
        $date = date('Y-m-d');
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("INSERT INTO sessions (course_id, session_date, qr_code_token) VALUES (?, ?, ?)");
        return $stmt->execute([$course_id, $date, $token]);
    }

    public function getQuizForSession($session_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM quizzes WHERE session_id = ?");
        $stmt->execute([$session_id]);
        return $stmt->fetch();
    }
}

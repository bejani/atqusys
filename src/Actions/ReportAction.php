<?php
namespace App\Actions;

use App\Domain\Database;
use PDO;

class ReportAction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getStudentAttendance($student_id) {
        $stmt = $this->pdo->prepare("
            SELECT s.session_date, c.course_name, a.scanned_at 
            FROM attendance a 
            JOIN sessions s ON a.session_id = s.id 
            JOIN courses c ON s.course_id = c.id 
            WHERE a.student_id = ? 
            ORDER BY s.session_date DESC
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll();
    }

    public function getStudentQuizResults($student_id) {
        $stmt = $this->pdo->prepare("
            SELECT q.title, c.course_name, qr.score, qr.submitted_at 
            FROM quiz_results qr 
            JOIN quizzes q ON qr.quiz_id = q.id 
            JOIN sessions s ON q.session_id = s.id 
            JOIN courses c ON s.course_id = c.id 
            WHERE qr.student_id = ? 
            ORDER BY qr.submitted_at DESC
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll();
    }

    public function getSessionAttendanceReport($session_id) {
        $stmt = $this->pdo->prepare("
            SELECT u.full_name, u.username, a.scanned_at 
            FROM users u 
            JOIN attendance a ON u.id = a.student_id 
            WHERE a.session_id = ? 
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([$session_id]);
        return $stmt->fetchAll();
    }

    public function getSessionQuizReport($session_id) {
        $stmt = $this->pdo->prepare("
            SELECT u.full_name, u.username, qr.score, qr.submitted_at 
            FROM users u 
            JOIN quiz_results qr ON u.id = qr.student_id 
            JOIN quizzes q ON qr.quiz_id = q.id 
            WHERE q.session_id = ? 
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([$session_id]);
        return $stmt->fetchAll();
    }
}

<?php
namespace App\Actions;

use App\Domain\Database;
use PDO;

class QuizAction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getSessionWithCourse($session_id, $teacher_id) {
        $stmt = $this->pdo->prepare("SELECT s.*, c.course_name FROM sessions s JOIN courses c ON s.course_id = c.id WHERE s.id = ? AND c.teacher_id = ?");
        $stmt->execute([$session_id, $teacher_id]);
        return $stmt->fetch();
    }

    public function getQuizBySession($session_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM quizzes WHERE session_id = ?");
        $stmt->execute([$session_id]);
        return $stmt->fetch();
    }

    public function getQuizQuestions($quiz_id) {
        $stmt = $this->pdo->prepare("SELECT question_id FROM quiz_questions WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function saveQuiz($session_id, $data, $selected_questions) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("INSERT INTO quizzes (session_id, title, duration_minutes, is_active) 
                                   VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=?, duration_minutes=?, is_active=?");
            $stmt->execute([
                $session_id, $data['title'], $data['duration'], $data['is_active'],
                $data['title'], $data['duration'], $data['is_active']
            ]);
            
            $quiz_id = $this->pdo->lastInsertId();
            if (!$quiz_id) {
                $stmt = $this->pdo->prepare("SELECT id FROM quizzes WHERE session_id = ?");
                $stmt->execute([$session_id]);
                $quiz_id = $stmt->fetchColumn();
            }

            $stmt = $this->pdo->prepare("DELETE FROM quiz_questions WHERE quiz_id = ?");
            $stmt->execute([$quiz_id]);
            
            if (!empty($selected_questions)) {
                $stmt = $this->pdo->prepare("INSERT INTO quiz_questions (quiz_id, question_id) VALUES (?, ?)");
                foreach ($selected_questions as $q_id) {
                    $stmt->execute([$quiz_id, $q_id]);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getQuizForStudent($quiz_id) {
        $stmt = $this->pdo->prepare("SELECT q.*, c.course_name FROM quizzes q 
                               JOIN sessions s ON q.session_id = s.id 
                               JOIN courses c ON s.course_id = c.id 
                               WHERE q.id = ?");
        $stmt->execute([$quiz_id]);
        return $stmt->fetch();
    }

    public function getFullQuizQuestions($quiz_id) {
        $stmt = $this->pdo->prepare("SELECT qb.* FROM question_bank qb 
                               JOIN quiz_questions qq ON qb.id = qq.question_id 
                               WHERE qq.quiz_id = ?");
        $stmt->execute([$quiz_id]);
        return $stmt->fetchAll();
    }

    public function submitQuizResult($student_id, $quiz_id, $score) {
        $stmt = $this->pdo->prepare("INSERT INTO quiz_results (student_id, quiz_id, score) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE score = ?");
        return $stmt->execute([$student_id, $quiz_id, $score, $score]);
    }
}

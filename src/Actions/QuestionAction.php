<?php
namespace App\Actions;

use App\Domain\Database;
use PDO;

class QuestionAction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getTeacherQuestions($teacher_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM question_bank WHERE teacher_id = ? ORDER BY created_at DESC");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    public function addQuestion($teacher_id, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO question_bank (teacher_id, question_text, option_a, option_b, option_c, option_d, correct_option, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $teacher_id, 
            $data['text'], 
            $data['a'], 
            $data['b'], 
            $data['c'], 
            $data['d'], 
            $data['correct'], 
            $data['category']
        ]);
    }

    public function deleteQuestion($question_id, $teacher_id) {
        $stmt = $this->pdo->prepare("DELETE FROM question_bank WHERE id = ? AND teacher_id = ?");
        return $stmt->execute([$question_id, $teacher_id]);
    }
}

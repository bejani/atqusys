<?php
namespace App\Actions;

use App\Domain\Database;
use PDO;

class StudentAction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAllStudents() {
        $stmt = $this->pdo->query("SELECT id, full_name, username FROM users WHERE role = 'student' ORDER BY full_name ASC");
        return $stmt->fetchAll();
    }

    public function getCourseStudentIds($course_id) {
        $stmt = $this->pdo->prepare("SELECT student_id FROM course_students WHERE course_id = ?");
        $stmt->execute([$course_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateCourseStudents($course_id, $student_ids) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("DELETE FROM course_students WHERE course_id = ?");
            $stmt->execute([$course_id]);
            
            if (!empty($student_ids)) {
                $stmt = $this->pdo->prepare("INSERT INTO course_students (course_id, student_id) VALUES (?, ?)");
                foreach ($student_ids as $s_id) {
                    $stmt->execute([$course_id, $s_id]);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}

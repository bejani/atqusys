<?php
namespace App\Actions;

use App\Domain\Database;
use PDO;

class CourseAction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getTeacherCourses($teacher_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    }

    public function addCourse($name, $code, $teacher_id) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO courses (course_name, course_code, teacher_id) VALUES (?, ?, ?)");
            return $stmt->execute([$name, $code, $teacher_id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getCourseById($course_id, $teacher_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$course_id, $teacher_id]);
        return $stmt->fetch();
    }
}

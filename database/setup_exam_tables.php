<?php
require_once 'config.php';

try {
    // 1. Create exams table (Removed FKs due to MyISAM mismatch)
    $sqlExams = "CREATE TABLE IF NOT EXISTS exams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_serial_no VARCHAR(20) NOT NULL UNIQUE,
        course_id INT NOT NULL,
        unit_no VARCHAR(10) DEFAULT NULL,
        subject_id INT NOT NULL,
        total_questions INT NOT NULL,
        marks_per_question DECIMAL(10,2) NOT NULL,
        total_marks DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_course (course_id),
        INDEX idx_subject (subject_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlExams);
    echo "Table 'exams' created or already exists.<br>";

    // 2. Create exam_questions table
    $sqlQuestions = "CREATE TABLE IF NOT EXISTS exam_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        exam_id INT NOT NULL,
        question_text TEXT NOT NULL,
        option_a VARCHAR(255) NOT NULL,
        option_b VARCHAR(255) NOT NULL,
        option_c VARCHAR(255) NOT NULL,
        option_d VARCHAR(255) NOT NULL,
        correct_option CHAR(1) NOT NULL,
        FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlQuestions);
    echo "Table 'exam_questions' created or already exists.<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

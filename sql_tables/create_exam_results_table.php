<?php
require_once '../database/config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS exam_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        exam_schedule_id INT NOT NULL,
        
        total_questions INT NOT NULL,
        attempted_questions INT DEFAULT 0,
        correct_answers INT DEFAULT 0,
        wrong_answers INT DEFAULT 0,
        score DECIMAL(10,2) NOT NULL,
        total_marks DECIMAL(10,2) NOT NULL,
        percentage DECIMAL(5,2) NOT NULL,
        
        result_status ENUM('Pass', 'Fail', 'Pending') DEFAULT 'Pending',
        unit_no INT DEFAULT NULL, /* Stores unit number if applicable */
        
        submission_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (exam_schedule_id) REFERENCES exam_schedules(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Table 'exam_results' created successfully.";

} catch (PDOException $e) {
    die("ERROR: Could not create table. " . $e->getMessage());
}
?>

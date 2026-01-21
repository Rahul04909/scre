<?php
require_once __DIR__ . '/../database/config.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS practical_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        practical_id INT NOT NULL,
        student_id INT NOT NULL,
        submission_file VARCHAR(255) NOT NULL,
        submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        marks_obtained DECIMAL(5,2) DEFAULT NULL,
        remarks TEXT,
        status ENUM('Submitted', 'Graded') DEFAULT 'Submitted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        -- FOREIGN KEY (practical_id) REFERENCES practicals(id) ON DELETE CASCADE,
        -- FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        UNIQUE KEY unique_submission (practical_id, student_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Table 'practical_submissions' created successfully.";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>

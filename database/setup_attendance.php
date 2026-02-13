<?php
require_once 'config.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS student_attendance");

    $sql = "CREATE TABLE student_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('Present', 'Absent', 'Leave') DEFAULT 'Present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (student_id),
        UNIQUE KEY unique_attendance (student_id, attendance_date)
    ) ENGINE=MyISAM";

    $pdo->exec($sql);
    echo "Table 'student_attendance' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

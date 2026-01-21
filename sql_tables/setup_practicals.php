<?php
require_once __DIR__ . '/../database/config.php';

try {
    // Drop if exists for clean state during dev (optional, maybe not unique constraints?)
    // $pdo->exec("DROP TABLE IF EXISTS practicals");

    $sql = "
    CREATE TABLE IF NOT EXISTS practicals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        course_id INT NOT NULL,
        session_id INT NOT NULL,
        unit_no INT DEFAULT 0, -- 0 for non-unit courses
        subject_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        submission_start_date DATE NOT NULL,
        submission_last_date DATE NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (center_id),
        INDEX (course_id),
        INDEX (session_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);
    echo "Table 'practicals' created successfully.";

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>

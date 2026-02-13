<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS study_materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        unit_no INT DEFAULT NULL,
        subject_id INT DEFAULT NULL,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (course_id),
        INDEX (subject_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "Table 'study_materials' created successfully.";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS live_classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        course_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        class_date DATE NOT NULL,
        class_time TIME NOT NULL,
        link TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (center_id),
        INDEX (course_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "Table 'live_classes' created successfully.";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

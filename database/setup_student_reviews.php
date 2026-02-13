<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS student_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";

    $pdo->exec($sql);
    echo "Table 'student_reviews' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

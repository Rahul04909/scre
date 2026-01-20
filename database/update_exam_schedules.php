<?php
require_once 'config.php';

try {
    // Check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM exam_schedules LIKE 'exam_id'");
    if ($check->rowCount() == 0) {
        // Add Column
        $sql = "ALTER TABLE exam_schedules 
                ADD COLUMN exam_id INT NULL DEFAULT NULL AFTER subject_id,
                ADD CONSTRAINT fk_exam_schedules_exams FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE";
        
        $pdo->exec($sql);
        echo "Column 'exam_id' added successfully.";
    } else {
        echo "Column 'exam_id' already exists.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

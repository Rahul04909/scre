<?php
require_once 'config.php';

try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM typing_test_results LIKE 'lesson_id'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $sql = "ALTER TABLE typing_test_results ADD COLUMN lesson_id INT DEFAULT NULL AFTER test_id";
        $pdo->exec($sql);
        echo "Column 'lesson_id' added successfully.<br>";
        
        // Add index
        $pdo->exec("CREATE INDEX idx_lesson_id ON typing_test_results(lesson_id)");
        echo "Index added.<br>";
    } else {
        echo "Column 'lesson_id' already exists.<br>";
    }

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

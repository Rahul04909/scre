<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Current Columns in 'students' table:\n";
    print_r($columns);
} catch (PDOException $e) {
    echo "Table 'students' likely does not exist. Error: " . $e->getMessage();
}
?>

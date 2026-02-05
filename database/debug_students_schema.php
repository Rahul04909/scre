<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SHOW CREATE TABLE students");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SHOW CREATE TABLE courses");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

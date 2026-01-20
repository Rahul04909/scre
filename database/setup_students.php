<?php
require_once 'config.php';

echo "<h2>Starting Student Database Setup...</h2>";

try {
    $sql = file_get_contents('sql_tables/student_tables.sql');
    
    // Split by semicolon to execute multiple statements if needed, 
    // but PDO execute can handle multiple if driver supports it. 
    // For safety, let's just run it. Using exec() for DDL.
    
    $pdo->exec($sql);
    echo "✅ Executed 'student_tables.sql' successfully.<br>";
    
    echo "<h3 style='color:green'>Student Tables Created!</h3>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
?>

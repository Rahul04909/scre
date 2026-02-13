<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        image VARCHAR(255) DEFAULT 'assets/img/default-admin.png',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "Table 'admins' created successfully.<br>";

    // Check if default admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_BCRYPT);
        $sqlInsert = "INSERT INTO admins (username, password, name, email) VALUES ('admin', '$password', 'Super Admin', 'admin@example.com')";
        $pdo->exec($sqlInsert);
        echo "Default admin user created (Username: admin, Password: admin123).";
    } else {
        echo "Default admin user already exists.";
    }

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

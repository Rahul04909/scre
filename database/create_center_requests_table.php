<?php
require_once 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS center_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_name VARCHAR(150) NOT NULL,
        owner_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        
        country_id INT,
        state_id INT,
        city_id INT,
        
        address TEXT,
        pincode VARCHAR(10),
        
        message TEXT,
        
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "Table 'center_requests' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>

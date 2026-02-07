<?php
require_once 'config.php';

try {
    // Gallery Categories Table
    $sqlCategories = "CREATE TABLE IF NOT EXISTS gallery_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlCategories);
    echo "Table 'gallery_categories' created successfully.<br>";

    // Gallery Images Table
    $sqlImages = "CREATE TABLE IF NOT EXISTS gallery_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        title VARCHAR(255),
        image_path VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES gallery_categories(id) ON DELETE CASCADE
    )";
    $pdo->exec($sqlImages);
    echo "Table 'gallery_images' created successfully.";

} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>

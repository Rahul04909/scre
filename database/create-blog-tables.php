<?php
require_once 'config.php';

try {
    // Blog Categories Table
    $sqlCategories = "CREATE TABLE IF NOT EXISTS blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlCategories);
    echo "Table 'blog_categories' created successfully.<br>";

    // Blogs Table
    $sqlBlogs = "CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        content TEXT,
        image_path VARCHAR(255),
        author VARCHAR(100) DEFAULT 'Admin',
        status ENUM('published', 'draft') DEFAULT 'published',
        views INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
    )";
    $pdo->exec($sqlBlogs);
    echo "Table 'blogs' created successfully.";

} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>

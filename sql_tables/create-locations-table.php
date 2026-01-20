<?php
require_once '../database/config.php';

try {
    // 1. Create 'countries' table
    $sqlCountries = "CREATE TABLE IF NOT EXISTS countries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sortname VARCHAR(3) NOT NULL, -- e.g., IN, US
        name VARCHAR(150) NOT NULL,
        phonecode INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sqlCountries);
    echo "Table 'countries' created successfully.<br>";

    // 2. Create 'states' table
    $sqlStates = "CREATE TABLE IF NOT EXISTS states (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        country_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
    )";
    $pdo->exec($sqlStates);
    echo "Table 'states' created successfully.<br>";

    // 3. Create 'cities' table
    $sqlCities = "CREATE TABLE IF NOT EXISTS cities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        state_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE CASCADE
    )";
    $pdo->exec($sqlCities);
    echo "Table 'cities' created successfully.<br>";

    // 4. Seed Default Country (India) if Empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM countries");
    if ($stmt->fetchColumn() == 0) {
        $sqlSeed = "INSERT INTO countries (sortname, name, phonecode) VALUES ('IN', 'India', 91)";
        $pdo->exec($sqlSeed);
        echo "Seeded default country: India.<br>";
    }

} catch (PDOException $e) {
    die("ERROR: Could not create tables. " . $e->getMessage());
}
?>

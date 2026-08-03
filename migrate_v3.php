<?php
$host = '127.0.0.1';
$dbname = 'hometutor_db';
$username = 'root';
$password = '';

echo "<h2>Database Migration v3 (Features Update)</h2>";

$ports = ['3307', '3306'];
$pdo = null;

foreach ($ports as $port) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<b style='color:green'>Connected to DB on port $port</b><br><br>";
        
        // Also update config.php automatically to the working port
        $config_content = file_get_contents('config.php');
        $config_content = preg_replace("/\\\$port = '330[67]';/", "\$port = '$port';", $config_content);
        file_put_contents('config.php', $config_content);
        break;
    } catch (PDOException $e) {
        // try next
    }
}

if (!$pdo) {
    die("<b>Could not connect to database on any port. Please start MySQL in XAMPP.</b>");
}

// 1. Add status to users
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('Active', 'Banned') DEFAULT 'Active'");
    echo "✅ Added status to users.<br>";
} catch (Exception $e) { echo "ℹ️ Users status: " . $e->getMessage() . "<br>"; }

// 2. Create tutor_reviews table
try {
    $pdo->exec("
        CREATE TABLE tutor_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tutor_id INT NOT NULL,
            student_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            review_text TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "✅ Created tutor_reviews table.<br>";
} catch (Exception $e) { echo "ℹ️ tutor_reviews table: " . $e->getMessage() . "<br>"; }

// 3. Add status to guardian_requests
try {
    $pdo->exec("ALTER TABLE guardian_requests ADD COLUMN status ENUM('Open', 'Closed') DEFAULT 'Open'");
    echo "✅ Added status to guardian_requests.<br>";
} catch (Exception $e) { echo "ℹ️ Guardian requests status: " . $e->getMessage() . "<br>"; }

echo "<br><b style='color:green'>Migrations complete! You can delete this file.</b>";
?>

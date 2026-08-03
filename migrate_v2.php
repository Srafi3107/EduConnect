<?php
$host = '127.0.0.1';
$dbname = 'hometutor_db';
$username = 'root';
$password = '';

echo "<h2>Database Migration</h2>";

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
        echo "<span style='color:red'>Failed on port $port: " . $e->getMessage() . "</span><br>";
    }
}

if (!$pdo) {
    die("<b>Could not connect to database on any port. Please start MySQL in XAMPP.</b>");
}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL");
    echo "✅ Added phone to users.<br>";
} catch (Exception $e) { echo "ℹ️ Users phone: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN gender ENUM('Male', 'Female') DEFAULT NULL");
    echo "✅ Added gender to users.<br>";
} catch (Exception $e) { echo "ℹ️ Users gender: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE tutor_profile CHANGE subject subject1 VARCHAR(255) DEFAULT NULL");
    echo "✅ Renamed subject to subject1 in tutor_profile.<br>";
} catch (Exception $e) { echo "ℹ️ Tutor profile subject1: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE tutor_profile ADD COLUMN subject2 VARCHAR(255) DEFAULT NULL");
    echo "✅ Added subject2 to tutor_profile.<br>";
} catch (Exception $e) { echo "ℹ️ Tutor profile subject2: " . $e->getMessage() . "<br>"; }

try {
    $pdo->exec("ALTER TABLE guardian_requests ADD COLUMN gender_preference ENUM('Male', 'Female', 'Any') DEFAULT 'Any'");
    echo "✅ Added gender_preference to guardian_requests.<br>";
} catch (Exception $e) { echo "ℹ️ Guardian requests gender: " . $e->getMessage() . "<br>"; }

echo "<br><b style='color:green'>Migrations complete! You can delete this file.</b>";
?>

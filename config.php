<?php
session_start();

$host = 'localhost';
$dbname = 'hometutor_db';
$username = 'root'; // default XAMPP/WAMP user
$password = ''; // default XAMPP/WAMP password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check user role
function checkRole($role) {
    if (!isLoggedIn() || $_SESSION['role'] !== $role) {
        header("Location: /HomeTutor/auth/login.php");
        exit();
    }
}
?>

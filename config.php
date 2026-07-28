<?php
session_start();

$host = '127.0.0.1';
$port = '3307';
$dbname = 'hometutor_db';
$username = 'root'; // default XAMPP/WAMP user
$password = ''; // default XAMPP/WAMP password

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Global Arrays for standardizing inputs across the application
$subjects = ['English', 'Math', 'Bangla', 'Physics', 'Chemistry', 'Biology', 'Arts', 'Commerce'];
$locations = ['Badda', 'Banani', 'Baridhara', 'Bashundhara', 'Dhanmondi', 'Gulshan', 'Khilgaon', 'Mirpur', 'Mohammadpur', 'Motijheel', 'New Market', 'Old Dhaka', 'Rampura', 'Tejgaon', 'Uttara'];
$classes = ['Grade 1-8', 'Grade 9-10', 'O-Level', 'A-Level', 'HSC'];

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check user role
function checkRole($role) {
    if (!isLoggedIn() || $_SESSION['role'] !== $role) {
        header("Location: /EduConnect/auth/login.php");
        exit();
    }
}
?>

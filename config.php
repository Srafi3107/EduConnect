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

// Cookie: Auto-login using "Remember Me" cookies if session has expired
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])) {
    // Read the saved email and password from cookies
    $cookie_email = $_COOKIE['remember_email'];
    $cookie_password = $_COOKIE['remember_password'];
    // Look up the user by email
    $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->execute([$cookie_email]);
    $cookie_user = $stmt->fetch();
    // Verify the password matches
    if ($cookie_user && ($cookie_password === $cookie_user['password'] || password_verify($cookie_password, $cookie_user['password']))) {
        // Password matches — restore the session automatically
        $_SESSION['user_id'] = $cookie_user['id'];
        $_SESSION['name'] = $cookie_user['name'];
        $_SESSION['role'] = $cookie_user['role'];
    }
}

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

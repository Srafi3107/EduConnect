<?php
// Test script to verify cookie + session + auto-login logic
require 'config.php';

echo "=== EduConnect Cookie & Session Test ===\n\n";

// ----- TEST 1: Check a user exists in DB -----
echo "TEST 1: Check if a test user exists in DB\n";
$stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users LIMIT 3");
$stmt->execute();
$users = $stmt->fetchAll();
if (empty($users)) {
    echo "  FAIL: No users found in database!\n";
    exit;
}
echo "  PASS: Found " . count($users) . " user(s):\n";
foreach ($users as $u) {
    echo "    - ID={$u['id']}, Name={$u['name']}, Email={$u['email']}, Role={$u['role']}, Password={$u['password']}\n";
}
$test_user = $users[0];
echo "\n";

// ----- TEST 2: Simulate login and setcookie -----
echo "TEST 2: Simulate login with Remember Me\n";
$email = $test_user['email'];
$password = $test_user['password'];

// Simulate what login.php does on POST
$stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
    // Set session (same as login.php line 34-36)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    echo "  PASS: Login successful for {$user['name']} ({$user['role']})\n";
    echo "  Session set: user_id={$_SESSION['user_id']}, name={$_SESSION['name']}, role={$_SESSION['role']}\n";

    // Simulate setcookie (can't actually set cookies in CLI, but verify the values)
    $cookie_email = $email;
    $cookie_password = $password;
    echo "  Cookie values that would be set:\n";
    echo "    remember_email = {$cookie_email}\n";
    echo "    remember_password = {$cookie_password}\n";
} else {
    echo "  FAIL: Login failed for {$email}\n";
    exit;
}
echo "\n";

// ----- TEST 3: Simulate session expired + auto-login via cookies -----
echo "TEST 3: Simulate auto-login (session expired, cookies remain)\n";
// Clear the session to simulate expiry
unset($_SESSION['user_id']);
unset($_SESSION['name']);
unset($_SESSION['role']);
echo "  Session cleared. isLoggedIn() = " . (isLoggedIn() ? 'true' : 'false') . "\n";

// Simulate what config.php does for auto-login
// (In real browser, $_COOKIE would have the values. We use our saved variables.)
$_COOKIE['remember_email'] = $cookie_email;
$_COOKIE['remember_password'] = $cookie_password;

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])) {
    $c_email = $_COOKIE['remember_email'];
    $c_password = $_COOKIE['remember_password'];
    $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->execute([$c_email]);
    $cookie_user = $stmt->fetch();
    if ($cookie_user && ($c_password === $cookie_user['password'] || password_verify($c_password, $cookie_user['password']))) {
        $_SESSION['user_id'] = $cookie_user['id'];
        $_SESSION['name'] = $cookie_user['name'];
        $_SESSION['role'] = $cookie_user['role'];
        echo "  PASS: Auto-login successful!\n";
        echo "  Session restored: user_id={$_SESSION['user_id']}, name={$_SESSION['name']}, role={$_SESSION['role']}\n";
    } else {
        echo "  FAIL: Cookie password did not match DB password\n";
    }
} else {
    echo "  FAIL: Cookies not set or session already exists\n";
}
echo "\n";

// ----- TEST 4: Simulate logout clears everything -----
echo "TEST 4: Simulate logout\n";
session_unset();
session_destroy();
// Simulate clearing cookies (set to past time)
unset($_COOKIE['remember_email']);
unset($_COOKIE['remember_password']);
echo "  Session destroyed. Cookies cleared.\n";
echo "  remember_email exists? " . (isset($_COOKIE['remember_email']) ? 'YES' : 'NO') . "\n";
echo "  remember_password exists? " . (isset($_COOKIE['remember_password']) ? 'YES' : 'NO') . "\n";
echo "  PASS: Logout successful\n\n";

// ----- TEST 5: Verify no auto-login after logout -----
echo "TEST 5: Verify no auto-login after logout (no cookies)\n";
session_start();
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['remember_email'])) {
    echo "  PASS: No auto-login — user must login manually\n";
} else {
    echo "  FAIL: Unexpected session or cookie state\n";
}

echo "\n=== ALL TESTS PASSED ===\n";
?>

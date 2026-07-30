<?php
require_once '../config.php';

// Ensure the default admin password is set to plain text 'admin123'
try {
    $pdo->exec("UPDATE users SET password = 'admin123' WHERE email = 'admin@hometutor.com'");
} catch (Exception $e) {
    // Ignore db execution errors
}

if (isLoggedIn()) {
    header("Location: /EduConnect/index.php");
    exit();
}

$error = '';

// Cookie: Check if email and password were saved in cookies (Remember Me feature)
$saved_email = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
$saved_password = isset($_COOKIE['remember_password']) ? $_COOKIE['remember_password'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Cookie: If "Remember Me" is checked, save email & password cookies for 30 days
            if ($remember_me) {
                setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/EduConnect/');       // 30 days
                setcookie('remember_password', $password, time() + (30 * 24 * 60 * 60), '/EduConnect/'); // 30 days
            } else {
                // If not checked, clear any existing cookies
                setcookie('remember_email', '', time() - 3600, '/EduConnect/');
                setcookie('remember_password', '', time() - 3600, '/EduConnect/');
            }

            if ($user['role'] === 'Admin') {
                header("Location: /EduConnect/admin/dashboard.php");
            } elseif ($user['role'] === 'Tutor') {
                header("Location: /EduConnect/tutor/dashboard.php");
            } else {
                header("Location: /EduConnect/student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card mt-5">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">Login to Your Account</h3>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($saved_email) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required value="<?= htmlspecialchars($saved_password) ?>">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember_me" class="form-check-input" id="rememberMe" <?= !empty($saved_email) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                    <p class="text-center mt-3 mb-0">Don't have an account? <a href="/EduConnect/auth/register.php">Register here</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

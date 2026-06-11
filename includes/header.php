<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Tutor Finding System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/HomeTutor/assets/css/style.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/HomeTutor/index.php"><i class="fa-solid fa-graduation-cap me-2"></i>TutorFinder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/HomeTutor/index.php">Home</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'Admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="/HomeTutor/admin/dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="/HomeTutor/admin/manage_users.php">Manage Users</a></li>
                        <?php elseif($_SESSION['role'] === 'Tutor'): ?>
                            <li class="nav-item"><a class="nav-link" href="/HomeTutor/tutor/dashboard.php">Requests</a></li>
                            <li class="nav-item"><a class="nav-link" href="/HomeTutor/tutor/profile.php">My Profile</a></li>
                        <?php elseif($_SESSION['role'] === 'Student'): ?>
                            <li class="nav-item"><a class="nav-link" href="/HomeTutor/student/dashboard.php">Find Tutors</a></li>
                            <li class="nav-item"><a class="nav-link" href="/HomeTutor/student/my_requests.php">My Requests</a></li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($_SESSION['name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="/HomeTutor/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2 px-3" href="/HomeTutor/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-light text-primary btn-sm ms-2 px-3 fw-bold" href="/HomeTutor/auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container my-5 min-vh-100">

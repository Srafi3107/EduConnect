<?php
require_once 'config.php';

// Fetch a few available tutors to show on the landing page
$stmt = $pdo->query("
    SELECT u.id as user_id, u.name, tp.subject, tp.location 
    FROM users u
    JOIN tutor_profile tp ON u.id = tp.user_id
    WHERE u.role = 'Tutor' AND tp.availability = 'Available'
    LIMIT 3
");
$recent_tutors = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="row align-items-center mb-5 mt-4">
    <div class="col-md-6">
        <h1 class="display-4 fw-bold mb-3">Find the Best Home Tutors Near You</h1>
        <p class="lead text-muted mb-4">Connect with experienced tutors in your area to achieve your academic goals. Simple, fast, and reliable.</p>
        <?php if(!isLoggedIn()): ?>
            <a href="/HomeTutor/auth/register.php" class="btn btn-primary btn-lg px-4 me-2">Get Started</a>
            <a href="/HomeTutor/auth/login.php" class="btn btn-outline-secondary btn-lg px-4">Login</a>
        <?php else: ?>
            <?php if($_SESSION['role'] === 'Student'): ?>
                <a href="/HomeTutor/student/dashboard.php" class="btn btn-primary btn-lg px-4">Find Tutors Now</a>
            <?php else: ?>
                <a href="/HomeTutor/<?= strtolower($_SESSION['role']) ?>/dashboard.php" class="btn btn-primary btn-lg px-4">Go to Dashboard</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="col-md-6 d-none d-md-block text-center">
        <!-- Placeholder image for hero section -->
        <img src="https://ui-avatars.com/api/?name=Education&background=0D8ABC&color=fff&size=400&font-size=0.33&bold=true" class="img-fluid rounded-circle shadow" alt="Education">
    </div>
</div>

<div class="row mb-5 text-center">
    <div class="col-12">
        <h2 class="fw-bold mb-4">How It Works</h2>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0">
            <div class="card-body">
                <i class="fa-solid fa-search fa-3x text-primary mb-3"></i>
                <h5 class="fw-bold">1. Search Tutors</h5>
                <p class="text-muted">Browse our extensive list of qualified tutors by subject and location.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0">
            <div class="card-body">
                <i class="fa-solid fa-paper-plane fa-3x text-success mb-3"></i>
                <h5 class="fw-bold">2. Send Request</h5>
                <p class="text-muted">Find a tutor you like and send them a hiring request directly.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0">
            <div class="card-body">
                <i class="fa-solid fa-graduation-cap fa-3x text-info mb-3"></i>
                <h5 class="fw-bold">3. Start Learning</h5>
                <p class="text-muted">Once accepted, start your learning journey and achieve great results!</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold text-center">Available Tutors</h2>
    </div>
    <?php if(empty($recent_tutors)): ?>
        <div class="col-12 text-center">
            <p class="text-muted">No tutors available at the moment. Please check back later.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
            <?php foreach($recent_tutors as $tutor): ?>
                <div class="col">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($tutor['name']) ?>&background=random" alt="Avatar" class="tutor-avatar mb-3">
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($tutor['name']) ?></h5>
                            <p class="card-text text-muted mb-1"><i class="fa-solid fa-book text-primary"></i> <?= htmlspecialchars($tutor['subject'] ?: 'Subject not specified') ?></p>
                            <p class="card-text text-muted mb-3"><i class="fa-solid fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($tutor['location'] ?: 'Location not specified') ?></p>
                            <?php if(isLoggedIn() && $_SESSION['role'] === 'Student'): ?>
                                <a href="/HomeTutor/student/view_tutor.php?id=<?= $tutor['user_id'] ?>" class="btn btn-outline-primary w-100 mt-auto">View Profile</a>
                            <?php elseif(!isLoggedIn()): ?>
                                <a href="/HomeTutor/auth/login.php" class="btn btn-outline-secondary w-100 mt-auto">Login to View</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

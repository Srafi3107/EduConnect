<?php
require_once '../config.php';
checkRole('Admin');

// Get total users by role
$stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roles_count = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$total_tutors = $roles_count['Tutor'] ?? 0;
$total_students = $roles_count['Student'] ?? 0;
$total_admins = $roles_count['Admin'] ?? 0;

// Get total requests
$stmt = $pdo->query("SELECT COUNT(*) FROM requests");
$total_requests = $stmt->fetchColumn();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Admin Dashboard</h2>
        <p class="text-muted">System overview and statistics.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card summary-card bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-white text-primary me-3">
                    <i class="fa-solid fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Tutors</h5>
                    <h3 class="fw-bold mb-0"><?= $total_tutors ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card bg-success text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-white text-success me-3">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Students</h5>
                    <h3 class="fw-bold mb-0"><?= $total_students ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card bg-info text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-white text-info me-3">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Total Requests</h5>
                    <h3 class="fw-bold mb-0"><?= $total_requests ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card bg-dark text-white h-100">
            <div class="card-body d-flex align-items-center">
                <div class="icon-box bg-white text-dark me-3">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Admins</h5>
                    <h3 class="fw-bold mb-0"><?= $total_admins ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <a href="/HomeTutor/admin/manage_users.php" class="btn btn-primary mt-3"><i class="fa-solid fa-users me-2"></i> Manage All Users</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

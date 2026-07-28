<?php
require_once '../config.php';
checkRole('Admin');

$success = '';
$error = '';

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_request'])) {
    $request_id_to_delete = $_POST['request_id'];
    
    $stmt = $pdo->prepare("DELETE FROM guardian_requests WHERE id = ?");
    if ($stmt->execute([$request_id_to_delete])) {
        $success = "Tuition request deleted successfully.";
    } else {
        $error = "Failed to delete tuition request.";
    }
}

// Fetch all guardian requests
$stmt = $pdo->query("
    SELECT r.*, u.name as student_name, u.email as student_email,
           (SELECT COUNT(*) FROM guardian_request_applications WHERE request_id = r.id) as app_count,
           (SELECT u2.name FROM guardian_request_applications gra JOIN users u2 ON gra.tutor_id = u2.id WHERE gra.request_id = r.id AND gra.status = 'Accepted' LIMIT 1) as filled_by
    FROM guardian_requests r
    JOIN users u ON r.student_id = u.id
    ORDER BY r.created_at DESC
");
$requests = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold">Manage Tuition Requests</h2>
            <p class="text-muted">View and manage all public tuition requests posted by guardians/students.</p>
        </div>
        <a href="/EduConnect/admin/dashboard.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to Dashboard</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if(empty($requests)): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No tuition requests found.</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Guardian / Student</th>
                            <th>Subject</th>
                            <th>Class / Location</th>
                            <th>Salary (BDT)</th>
                            <th>Status</th>
                            <th>Applications</th>
                            <th>Date Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requests as $r): ?>
                            <tr>
                                <td><?= $r['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($r['student_email']) ?></small>
                                </td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($r['subject']) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($r['class_level']) ?></strong><br>
                                    <small class="text-muted"><i class="fa-solid fa-map-marker-alt text-danger me-1"></i><?= htmlspecialchars($r['location']) ?><?= !empty($r['additional_address']) ? ' (' . htmlspecialchars($r['additional_address']) . ')' : '' ?></small>
                                </td>
                                <td><strong>BDT <?= htmlspecialchars(number_format($r['salary'], 2)) ?></strong></td>
                                <td>
                                    <?php if($r['filled_by']): ?>
                                        <span class="badge bg-success">Filled</span><br>
                                        <small class="text-muted">by <?= htmlspecialchars($r['filled_by']) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Open</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= $r['app_count'] ?> <?= $r['app_count'] == 1 ? 'applicant' : 'applicants' ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                                <td>
                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this tuition request? This will also delete all applications for it. This action cannot be undone.');" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="delete_request" value="1">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash me-1"></i>Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

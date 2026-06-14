<?php
require_once '../config.php';
checkRole('Tutor');

$tutor_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle application counter-offer / accept / reject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['app_action'])) {
    $app_id = $_POST['application_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    // Verify application belongs to this tutor
    $verify_stmt = $pdo->prepare("
        SELECT id, request_id 
        FROM guardian_request_applications 
        WHERE id = ? AND tutor_id = ?
    ");
    $verify_stmt->execute([$app_id, $tutor_id]);
    $app = $verify_stmt->fetch();

    if ($app) {
        if ($action === 'accept') {
            $pdo->beginTransaction();
            try {
                // Update application status to Accepted
                $stmt = $pdo->prepare("UPDATE guardian_request_applications SET status = 'Accepted' WHERE id = ?");
                $stmt->execute([$app_id]);
                
                // Reject other applications for the same request
                $stmt = $pdo->prepare("UPDATE guardian_request_applications SET status = 'Rejected' WHERE request_id = ? AND id != ?");
                $stmt->execute([$app['request_id'], $app_id]);
                
                $pdo->commit();
                $success = "You have accepted the offer!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to accept offer: " . $e->getMessage();
            }
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE guardian_request_applications SET status = 'Rejected' WHERE id = ?");
            if ($stmt->execute([$app_id])) {
                $success = "You have rejected the offer.";
            } else {
                $error = "Failed to reject offer.";
            }
        } elseif ($action === 'counter') {
            $counter_salary = trim($_POST['counter_salary'] ?? 0);
            if ($counter_salary <= 0) {
                $error = "Counter salary must be a positive number.";
            } else {
                $stmt = $pdo->prepare("
                    UPDATE guardian_request_applications SET 
                        proposed_salary = ?, 
                        status = 'Negotiating', 
                        offered_by = 'Tutor' 
                    WHERE id = ?
                ");
                if ($stmt->execute([$counter_salary, $app_id])) {
                    $success = "Counter-offer of $" . number_format($counter_salary, 2) . " sent successfully!";
                } else {
                    $error = "Failed to send counter-offer.";
                }
            }
        }
    } else {
        $error = "Invalid application action.";
    }
}

// Fetch all public requests applications submitted by this tutor
$stmt = $pdo->prepare("
    SELECT a.id as app_id, a.proposed_salary, a.message as app_message, a.status as app_status, a.offered_by, a.created_at as applied_at,
           r.subject, r.class_level, r.location, r.additional_address, r.salary as original_budget,
           u.name as guardian_name, u.email as guardian_email
    FROM guardian_request_applications a
    JOIN guardian_requests r ON a.request_id = r.id
    JOIN users u ON r.student_id = u.id
    WHERE a.tutor_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$tutor_id]);
$applications = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Tutor Dashboard</h2>
        <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>! Track your job applications and negotiations here.</p>
    </div>
</div>

<?php if($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-4"><i class="fa-solid fa-file-signature text-primary me-2"></i>My Applications & Negotiations</h5>
        <?php if(empty($applications)): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">You have not submitted any job applications yet.</p>
                <a href="/EduConnect/tutor/job_board.php" class="btn btn-primary mt-3">Browse Job Board</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Job Details</th>
                            <th>Guardian</th>
                            <th>Original Budget</th>
                            <th>Current Bid</th>
                            <th>Status</th>
                            <th style="min-width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($applications as $app): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary"><?= htmlspecialchars($app['subject']) ?></strong><br>
                                    <small class="text-muted">Class: <?= htmlspecialchars($app['class_level']) ?></small><br>
                                    <small class="text-muted"><i class="fa-solid fa-map-marker-alt"></i> <?= htmlspecialchars($app['location']) ?><?= !empty($app['additional_address']) ? ' (' . htmlspecialchars($app['additional_address']) . ')' : '' ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($app['guardian_name']) ?></strong>
                                    <?php if ($app['app_status'] === 'Accepted'): ?>
                                        <br>
                                        <small class="text-success"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($app['guardian_email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>$<?= htmlspecialchars(number_format($app['original_budget'], 2)) ?></td>
                                <td>
                                    <span class="fw-bold text-success">$<?= htmlspecialchars(number_format($app['proposed_salary'], 2)) ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        if($app['app_status'] == 'Pending') $badgeClass = 'badge-status-pending';
                                        if($app['app_status'] == 'Accepted') $badgeClass = 'badge-status-accepted';
                                        if($app['app_status'] == 'Rejected') $badgeClass = 'badge-status-rejected';
                                        if($app['app_status'] == 'Negotiating') $badgeClass = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $app['app_status'] ?></span>
                                </td>
                                <td>
                                    <?php if ($app['app_status'] == 'Negotiating' && $app['offered_by'] == 'Guardian'): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex gap-1">
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="app_action" value="1">
                                                    <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Accept</button>
                                                </form>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="app_action" value="1">
                                                    <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-times"></i> Reject</button>
                                                </form>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-warning text-white w-100 mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#counterForm<?= $app['app_id'] ?>">
                                                    <i class="fa-solid fa-comments-dollar"></i> Counter
                                                </button>
                                                <div class="collapse mt-2" id="counterForm<?= $app['app_id'] ?>">
                                                    <form method="POST" action="" class="d-flex gap-1">
                                                        <input type="hidden" name="app_action" value="1">
                                                        <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                                        <input type="hidden" name="action" value="counter">
                                                        <input type="number" step="0.01" name="counter_salary" class="form-control form-control-sm" style="min-width: 80px;" placeholder="Counter Amount" required>
                                                        <button type="submit" class="btn btn-sm btn-warning text-white"><i class="fa-solid fa-paper-plane"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($app['app_status'] == 'Negotiating' && $app['offered_by'] == 'Tutor'): ?>
                                        <small class="text-muted"><i class="fa-solid fa-clock"></i> Waiting for guardian response (Counter sent)</small>
                                    <?php elseif ($app['app_status'] == 'Pending'): ?>
                                        <small class="text-muted"><i class="fa-solid fa-clock"></i> Application pending review</small>
                                    <?php elseif ($app['app_status'] == 'Accepted'): ?>
                                        <span class="text-success"><i class="fa-solid fa-circle-check"></i> Offer Accepted</span>
                                    <?php else: ?>
                                        <span class="text-muted">No actions available</span>
                                    <?php endif; ?>
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

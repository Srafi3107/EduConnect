<?php
require_once '../config.php';
checkRole('Tutor');

$tutor_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle submitting application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_job'])) {
    $request_id = $_POST['request_id'] ?? 0;
    $proposed_salary = trim($_POST['proposed_salary'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (empty($proposed_salary) || $proposed_salary <= 0) {
        $error = "Please enter a valid proposed salary.";
    } else {
        // Double check if already applied
        $check_stmt = $pdo->prepare("SELECT id FROM guardian_request_applications WHERE request_id = ? AND tutor_id = ?");
        $check_stmt->execute([$request_id, $tutor_id]);
        if ($check_stmt->fetch()) {
            $error = "You have already applied to this job request.";
        } else {
            $insert_stmt = $pdo->prepare("
                INSERT INTO guardian_request_applications (request_id, tutor_id, proposed_salary, message)
                VALUES (?, ?, ?, ?)
            ");
            if ($insert_stmt->execute([$request_id, $tutor_id, $proposed_salary, $message])) {
                $success = "Application submitted successfully!";
            } else {
                $error = "Failed to submit application.";
            }
        }
    }
}

// Fetch all public requests with information about this tutor's application if they applied
$stmt = $pdo->prepare("
    SELECT r.*, u.name as guardian_name, u.email as guardian_email,
           a.status as my_app_status, a.proposed_salary as my_app_salary, a.message as my_app_message, a.created_at as my_applied_at
    FROM guardian_requests r
    JOIN users u ON r.student_id = u.id
    LEFT JOIN guardian_request_applications a ON r.id = a.request_id AND a.tutor_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$tutor_id]);
$jobs = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Tutor Job Board</h2>
        <p class="text-muted">Browse public tutor requests posted by guardians and apply with your salary proposal.</p>
    </div>
</div>

<?php if($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <?php if(empty($jobs)): ?>
            <div class="card p-5 text-center shadow-sm">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No public job requests available right now.</h5>
                <p class="text-muted mb-0">Check back later for new tutoring opportunities.</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 g-4">
                <?php foreach($jobs as $job): ?>
                    <div class="col">
                        <div class="card shadow-sm h-100 border-start border-4 <?= $job['my_app_status'] ? 'border-secondary' : 'border-primary' ?>">
                            <div class="card-body p-4">
                                <div class="row">
                                    <!-- Job details -->
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-2">
                                            <h4 class="fw-bold mb-0 text-primary me-3"><?= htmlspecialchars($job['subject']) ?></h4>
                                            <?php if ($job['my_app_status']): ?>
                                                <?php 
                                                    $badgeClass = 'bg-secondary';
                                                    if($job['my_app_status'] == 'Pending') $badgeClass = 'badge-status-pending';
                                                    if($job['my_app_status'] == 'Accepted') $badgeClass = 'badge-status-accepted';
                                                    if($job['my_app_status'] == 'Rejected') $badgeClass = 'badge-status-rejected';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><i class="fa-solid fa-file-signature me-1"></i>Applied - <?= $job['my_app_status'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-2">
                                            <span class="me-3"><i class="fa-solid fa-user text-muted"></i> Guardian: <strong><?= htmlspecialchars($job['guardian_name']) ?></strong></span>
                                            <span class="me-3"><i class="fa-solid fa-graduation-cap text-muted"></i> Class: <strong><?= htmlspecialchars($job['class_level']) ?></strong></span>
                                            <span class="me-3"><i class="fa-solid fa-map-marker-alt text-muted"></i> Location: <strong><?= htmlspecialchars($job['location']) ?></strong><?= !empty($job['additional_address']) ? ' (' . htmlspecialchars($job['additional_address']) . ')' : '' ?></span>
                                            <span><i class="fa-solid fa-money-bill-wave text-muted"></i> Budget: <strong>$<?= htmlspecialchars(number_format($job['salary'], 2)) ?>/month</strong></span>
                                        </p>
                                        <?php if ($job['description']): ?>
                                            <p class="text-muted bg-light p-3 rounded" style="font-size: 0.95rem;"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
                                        <?php endif; ?>
                                        
                                        <small class="text-muted">Posted on <?= date('M d, Y', strtotime($job['created_at'])) ?></small>
                                    </div>
                                    
                                    <!-- Apply / Applied actions -->
                                    <div class="col-md-4 border-start d-flex flex-column justify-content-center">
                                        <?php if ($job['my_app_status']): ?>
                                            <div class="bg-light p-3 rounded text-center">
                                                <h6 class="fw-bold mb-2 text-dark">Your Application</h6>
                                                <div class="mb-2">
                                                    <span class="text-muted">Proposed Salary:</span> 
                                                    <strong class="text-success">$<?= htmlspecialchars(number_format($job['my_app_salary'], 2)) ?></strong>
                                                </div>
                                                <?php if($job['my_app_message']): ?>
                                                    <p class="text-muted mb-2 bg-white p-2 border rounded" style="font-size: 0.85rem; text-align: left; max-height: 80px; overflow-y: auto;">
                                                        <?= htmlspecialchars($job['my_app_message']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <small class="text-muted d-block">Submitted on <?= date('M d, Y', strtotime($job['my_applied_at'])) ?></small>
                                                
                                                <?php if($job['my_app_status'] === 'Accepted'): ?>
                                                    <div class="alert alert-success mt-2 mb-0 py-2" style="font-size: 0.85rem;">
                                                        <i class="fa-solid fa-envelope me-1"></i> Contact guardian at:<br>
                                                        <strong><?= htmlspecialchars($job['guardian_email']) ?></strong>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-2">
                                                <h6 class="fw-bold mb-3"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Apply for this job</h6>
                                                <form method="POST" action="">
                                                    <input type="hidden" name="apply_job" value="1">
                                                    <input type="hidden" name="request_id" value="<?= $job['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label mb-1" style="font-size: 0.85rem;">Proposed Salary</label>
                                                        <input type="number" name="proposed_salary" class="form-control form-control-sm" value="<?= htmlspecialchars($job['salary']) ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label mb-1" style="font-size: 0.85rem;">Message to Guardian</label>
                                                        <textarea name="message" class="form-control form-control-sm" rows="3" placeholder="Describe your credentials or teaching schedule..." required></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary w-100 py-2"><i class="fa-solid fa-check me-1"></i>Submit Application</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

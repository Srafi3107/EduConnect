<?php
require_once '../config.php';
checkRole('Student');

$student_id = $_SESSION['user_id'];
$success = '';
$error = '';



// Handle posting new public tutor request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_request'])) {
    $subject = trim($_POST['subject'] ?? '');
    $class_level = trim($_POST['class_level'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $salary = trim($_POST['salary'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $additional_address = trim($_POST['additional_address'] ?? '');
    $gender_preference = $_POST['gender_preference'] ?? 'Any';

    if (empty($subject) || empty($class_level) || empty($location) || empty($salary)) {
        $error = "All mandatory fields (Subject, Class Level, Location, Salary) are required.";
    } elseif ($salary <= 0) {
        $error = "Salary must be a positive number.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO guardian_requests (student_id, subject, class_level, location, salary, description, additional_address, gender_preference)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$student_id, $subject, $class_level, $location, $salary, $description, $additional_address, $gender_preference])) {
            $success = "Tutor request posted successfully! Tutors can now view and apply to this request.";
        } else {
            $error = "Failed to post tutor request.";
        }
    }
}

// Handle application accept/reject/counter actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['app_action'])) {
    $app_id = $_POST['application_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    // Verify application is for a request owned by this student
    $verify_stmt = $pdo->prepare("
        SELECT a.id, a.request_id, a.tutor_id 
        FROM guardian_request_applications a
        JOIN guardian_requests r ON a.request_id = r.id
        WHERE a.id = ? AND r.student_id = ?
    ");
    $verify_stmt->execute([$app_id, $student_id]);
    $app = $verify_stmt->fetch();

    if ($app) {
        if ($action === 'accept') {
            // Check if tutor already has 2 accepted tuitions
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM guardian_request_applications WHERE tutor_id = ? AND status = 'Accepted'");
            $count_stmt->execute([$app['tutor_id']]);
            $accepted_count = $count_stmt->fetchColumn();

            if ($accepted_count >= 2) {
                $error = "This tutor has already reached their maximum limit of 2 active tuitions.";
            } else {
                $pdo->beginTransaction();
                try {
                    // Update application status
                    $stmt = $pdo->prepare("UPDATE guardian_request_applications SET status = 'Accepted' WHERE id = ?");
                    $stmt->execute([$app_id]);
                    
                    // If accepted, reject all other applications for the same request
                    $stmt = $pdo->prepare("UPDATE guardian_request_applications SET status = 'Rejected' WHERE request_id = ? AND id != ?");
                    $stmt->execute([$app['request_id'], $app_id]);
                    
                    // Check again and update tutor availability if they just reached 2
                    if ($accepted_count + 1 >= 2) {
                        $update_tutor = $pdo->prepare("UPDATE tutor_profile SET availability = 'Not Available' WHERE user_id = ?");
                        $update_tutor->execute([$app['tutor_id']]);
                    }
                    
                    $pdo->commit();
                    $success = "Application status updated to Accepted!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Failed to accept application: " . $e->getMessage();
                }
            }
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE guardian_request_applications SET status = 'Rejected' WHERE id = ?");
            if ($stmt->execute([$app_id])) {
                $success = "Application status updated to Rejected.";
            } else {
                $error = "Failed to reject application.";
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
                        offered_by = 'Guardian' 
                    WHERE id = ?
                ");
                if ($stmt->execute([$counter_salary, $app_id])) {
                    $success = "Counter-offer of BDT " . number_format($counter_salary, 2) . " sent successfully!";
                } else {
                    $error = "Failed to send counter-offer.";
                }
            }
        }
    } else {
        $error = "Invalid application action.";
    }
}

// Handle Request Actions (Close / Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_action'])) {
    $req_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    // Verify ownership
    $stmt = $pdo->prepare("SELECT id FROM guardian_requests WHERE id = ? AND student_id = ?");
    $stmt->execute([$req_id, $student_id]);
    if ($stmt->fetch()) {
        if ($action === 'close') {
            $stmt = $pdo->prepare("UPDATE guardian_requests SET status = 'Closed' WHERE id = ?");
            if ($stmt->execute([$req_id])) {
                $success = "Tutor request closed successfully. It will no longer receive applications.";
            } else {
                $error = "Failed to close request.";
            }
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM guardian_requests WHERE id = ?");
            if ($stmt->execute([$req_id])) {
                $success = "Tutor request deleted successfully.";
            } else {
                $error = "Failed to delete request.";
            }
        }
    }
}

// Fetch all public requests posted by this student, including count of applications
$stmt = $pdo->prepare("
    SELECT r.*, COUNT(a.id) as app_count 
    FROM guardian_requests r
    LEFT JOIN guardian_request_applications a ON r.id = a.request_id
    WHERE r.student_id = ?
    GROUP BY r.id
    ORDER BY r.created_at DESC
");
$stmt->execute([$student_id]);
$my_posts = $stmt->fetchAll();

// For each post, fetch applications details
$posts_with_apps = [];
foreach ($my_posts as $post) {
    $app_stmt = $pdo->prepare("
        SELECT a.id as app_id, a.proposed_salary, a.message, a.status as app_status, a.offered_by, a.created_at as applied_at,
               u.name as tutor_name, u.email as tutor_email, u.phone, u.gender, tp.experience, tp.picture
        FROM guardian_request_applications a
        JOIN users u ON a.tutor_id = u.id
        JOIN tutor_profile tp ON u.id = tp.user_id
        WHERE a.request_id = ?
        ORDER BY a.created_at DESC
    ");
    $app_stmt->execute([$post['id']]);
    $post['applications'] = $app_stmt->fetchAll();
    $posts_with_apps[] = $post;
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold">Post Tutor Request</h2>
        <p class="text-muted">Create a public post describing your tutoring needs so that any registered tutor can apply.</p>
    </div>
</div>

<?php if($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row">
    <!-- Posting Form -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-3"><i class="fa-solid fa-plus-circle text-primary me-2"></i>New Tutor Request</h5>
                <form method="POST" action="">
                    <input type="hidden" name="post_request" value="1">
                    <div class="mb-3">
                        <label class="form-label">Tutor Gender Preference</label>
                        <select name="gender_preference" class="form-select" required>
                            <option value="Any">Any Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <select name="subject" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub ?>"><?= $sub ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Class Level</label>
                        <select name="class_level" class="form-select" required>
                            <option value="">Select Class Level</option>
                            <?php foreach ($classes as $cls): ?>
                                <option value="<?= $cls ?>"><?= $cls ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location (Dhaka)</label>
                        <select name="location" class="form-select" required>
                            <option value="">Select Location</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc ?>"><?= $loc ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Address (Guardian)</label>
                        <input type="text" name="additional_address" class="form-control" placeholder="e.g. House 12, Road 4, Sector 3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget / Salary (BDT per month)</label>
                        <input type="number" name="salary" class="form-control" placeholder="e.g. 5000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detailed Requirements</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Detail class times, days per week, and tutor qualities desired..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa-solid fa-paper-plane me-2"></i>Post Job Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Posts and Applications -->
    <div class="col-md-8">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>My Posted Requests</h5>
        <?php if(empty($posts_with_apps)): ?>
            <div class="card p-5 text-center">
                <p class="text-muted mb-0">You have not posted any public tutor requests yet. Use the form on the left to get started!</p>
            </div>
        <?php else: ?>
            <?php foreach($posts_with_apps as $post): ?>
                <div class="card mb-4 border-start border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0 text-primary">
                                <?= htmlspecialchars($post['subject']) ?>
                                <?php if(($post['status'] ?? 'Open') == 'Closed'): ?>
                                    <span class="badge bg-secondary ms-2" style="font-size: 0.75rem;">Closed</span>
                                <?php endif; ?>
                            </h5>
                            <div>
                                <span class="badge bg-light text-dark me-2 border"><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="request_action" value="1">
                                    <input type="hidden" name="request_id" value="<?= $post['id'] ?>">
                                    <?php if(($post['status'] ?? 'Open') == 'Open'): ?>
                                        <input type="hidden" name="action" value="close">
                                        <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2" title="Close Request" onclick="return confirm('Are you sure you want to close this request? No more applications can be sent.');"><i class="fa-solid fa-lock"></i></button>
                                    <?php endif; ?>
                                </form>
                                <form method="POST" action="" class="d-inline">
                                    <input type="hidden" name="request_action" value="1">
                                    <input type="hidden" name="request_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete Request" onclick="return confirm('Are you sure you want to delete this request permanently?');"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                        <p class="mb-2">
                            <span class="me-3"><i class="fa-solid fa-graduation-cap text-muted"></i> Class: <strong><?= htmlspecialchars($post['class_level']) ?></strong></span>
                            <span class="me-3"><i class="fa-solid fa-map-marker-alt text-muted"></i> Location: <strong><?= htmlspecialchars($post['location']) ?></strong><?= !empty($post['additional_address']) ? ' (' . htmlspecialchars($post['additional_address']) . ')' : '' ?></span>
                            <span class="me-3"><i class="fa-solid fa-venus-mars text-muted"></i> Gender: <strong><?= htmlspecialchars($post['gender_preference'] ?? 'Any') ?></strong></span>
                            <span><i class="fa-solid fa-money-bill-wave text-muted"></i> Budget: <strong>BDT <?= htmlspecialchars(number_format($post['salary'], 2)) ?></strong></span>
                        </p>
                        <?php if($post['description']): ?>
                            <p class="text-muted mb-3 bg-light p-2 rounded" style="font-size: 0.9rem;"><?= nl2br(htmlspecialchars($post['description'])) ?></p>
                        <?php endif; ?>

                        <hr>
                        
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-users-viewfinder text-success me-1"></i> Tutor Applications (<?= count($post['applications']) ?>)</h6>
                        
                        <?php if(empty($post['applications'])): ?>
                            <p class="text-muted mb-0" style="font-size: 0.9rem;">No applications received yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach($post['applications'] as $app): ?>
                                    <div class="list-group-item list-group-item-action p-3 mb-2 border rounded shadow-xs">
                                        <div class="d-flex align-items-center mb-3">
                                            <?php 
                                                $tutor_pic = !empty($app['picture']) ? htmlspecialchars($app['picture']) : "https://ui-avatars.com/api/?name=" . urlencode($app['tutor_name']) . "&background=random";
                                            ?>
                                            <img src="<?= $tutor_pic ?>" class="tutor-avatar-sm me-3" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($app['tutor_name']) ?>&background=random'">
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($app['tutor_name']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($app['tutor_email']) ?> | 
                                                    <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($app['phone'] ?: 'N/A') ?> <br>
                                                    <i class="fa-solid fa-venus-mars"></i> <?= htmlspecialchars($app['gender'] ?: 'Unspecified') ?> | 
                                                    <i class="fa-solid fa-briefcase"></i> Exp: <?= htmlspecialchars($app['experience'] ?: 'None') ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="d-block fw-bold text-success">BDT <?= htmlspecialchars(number_format($app['proposed_salary'], 2)) ?></span>
                                                <small class="text-muted"><?= ($app['app_status'] === 'Negotiating') ? 'Current Offer' : 'Proposed Salary' ?></small>
                                            </div>
                                        </div>
                                        <?php if($app['message']): ?>
                                            <p class="mb-3 text-secondary" style="font-size: 0.9rem; border-left: 3px solid #dee2e6; padding-left: 10px;"><?= nl2br(htmlspecialchars($app['message'])) ?></p>
                                        <?php endif; ?>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Applied on <?= date('M d, Y', strtotime($app['applied_at'])) ?></small>
                                            <div>
                                                <?php 
                                                    $badgeClass = 'bg-secondary';
                                                    if($app['app_status'] == 'Pending') $badgeClass = 'badge-status-pending';
                                                    if($app['app_status'] == 'Accepted') $badgeClass = 'badge-status-accepted';
                                                    if($app['app_status'] == 'Rejected') $badgeClass = 'badge-status-rejected';
                                                    if($app['app_status'] == 'Negotiating') $badgeClass = 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?= $badgeClass ?> me-2"><?= $app['app_status'] ?></span>
                                                
                                                <?php if($app['app_status'] == 'Pending' || ($app['app_status'] == 'Negotiating' && $app['offered_by'] == 'Tutor')): ?>
                                                    <div class="d-inline-block">
                                                        <div class="d-flex gap-1">
                                                            <form method="POST" action="" class="d-inline">
                                                                <input type="hidden" name="app_action" value="1">
                                                                <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                                                <input type="hidden" name="action" value="accept">
                                                                <button type="submit" class="btn btn-sm btn-success px-2"><i class="fa-solid fa-check"></i> Accept</button>
                                                            </form>
                                                            <form method="POST" action="" class="d-inline">
                                                                <input type="hidden" name="app_action" value="1">
                                                                <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2"><i class="fa-solid fa-times"></i> Reject</button>
                                                            </form>
                                                            <button class="btn btn-sm btn-warning text-white px-2" type="button" data-bs-toggle="collapse" data-bs-target="#counterForm<?= $app['app_id'] ?>">
                                                                <i class="fa-solid fa-comments-dollar"></i> Counter
                                                            </button>
                                                        </div>
                                                        <div class="collapse mt-2" id="counterForm<?= $app['app_id'] ?>">
                                                            <form method="POST" action="" class="d-flex gap-1">
                                                                <input type="hidden" name="app_action" value="1">
                                                                <input type="hidden" name="application_id" value="<?= $app['app_id'] ?>">
                                                                <input type="hidden" name="action" value="counter">
                                                                <input type="number" step="0.01" name="counter_salary" class="form-control form-control-sm" placeholder="Counter Amount" style="max-width: 120px;" required>
                                                                <button type="submit" class="btn btn-sm btn-warning text-white"><i class="fa-solid fa-paper-plane"></i></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                <?php elseif($app['app_status'] == 'Negotiating' && $app['offered_by'] == 'Guardian'): ?>
                                                    <span class="text-muted"><small><i class="fa-solid fa-clock"></i> Waiting for tutor response (Counter sent)</small></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

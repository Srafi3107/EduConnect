<?php
require_once '../config.php';
checkRole('Student');

$tutor_id = $_GET['id'] ?? 0;
$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Check if valid tutor
$stmt = $pdo->prepare("
    SELECT u.name, u.email, tp.* 
    FROM users u 
    JOIN tutor_profile tp ON u.id = tp.user_id 
    WHERE u.id = ? AND u.role = 'Tutor'
");
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch();

if (!$tutor) {
    die("Tutor not found.");
}

// Handle sending request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_request'])) {
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message)) {
        $error = "Message cannot be empty.";
    } else {
        // Check if a pending request already exists
        $check_stmt = $pdo->prepare("SELECT id FROM requests WHERE student_id = ? AND tutor_id = ? AND status = 'Pending'");
        $check_stmt->execute([$student_id, $tutor_id]);
        
        if ($check_stmt->fetch()) {
            $error = "You already have a pending request for this tutor.";
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO requests (student_id, tutor_id, message) VALUES (?, ?, ?)");
            if ($insert_stmt->execute([$student_id, $tutor_id, $message])) {
                $success = "Request sent successfully!";
            } else {
                $error = "Failed to send request.";
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4 text-center">
            <div class="card-body">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($tutor['name']) ?>&background=random" class="tutor-avatar mb-3" alt="Avatar">
                <h4 class="card-title fw-bold"><?= htmlspecialchars($tutor['name']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($tutor['subject']) ?></p>
                <?php if($tutor['availability'] == 'Available'): ?>
                    <span class="badge bg-success">Available</span>
                <?php else: ?>
                    <span class="badge bg-danger">Not Available</span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if($tutor['availability'] == 'Available'): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Send Tutoring Request</h5>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Your Message</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Hello, I would like to hire you for..." required></textarea>
                        </div>
                        <button type="submit" name="send_request" class="btn btn-primary w-100">Send Request</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-4">
                <h4 class="card-title fw-bold mb-4">Tutor Details</h4>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Class Level:</div>
                    <div class="col-sm-9"><?= htmlspecialchars($tutor['class_level'] ?: 'Not specified') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Location:</div>
                    <div class="col-sm-9"><?= htmlspecialchars($tutor['location'] ?: 'Not specified') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Experience:</div>
                    <div class="col-sm-9"><?= htmlspecialchars($tutor['experience'] ?: 'Not specified') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Expected Salary:</div>
                    <div class="col-sm-9">$<?= htmlspecialchars($tutor['salary'] ?: '0.00') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">About:</div>
                    <div class="col-sm-9"><?= nl2br(htmlspecialchars($tutor['description'] ?: 'No description provided.')) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

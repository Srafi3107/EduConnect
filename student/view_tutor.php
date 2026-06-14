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

// Direct request sending logic removed

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4 text-center">
            <div class="card-body">
                <?php 
                    $tutor_pic = !empty($tutor['picture']) ? htmlspecialchars($tutor['picture']) : "https://ui-avatars.com/api/?name=" . urlencode($tutor['name']) . "&background=random";
                ?>
                <img src="<?= $tutor_pic ?>" class="tutor-avatar mb-3" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($tutor['name']) ?>&background=random'">
                <h4 class="card-title fw-bold"><?= htmlspecialchars($tutor['name']) ?></h4>
                <p class="text-muted"><?= htmlspecialchars($tutor['subject']) ?></p>
                <?php if($tutor['availability'] == 'Available'): ?>
                    <span class="badge bg-success">Available</span>
                <?php else: ?>
                    <span class="badge bg-danger">Not Available</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tutoring request form removed as direct requests are disabled -->
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

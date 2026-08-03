<?php
require_once '../config.php';
checkRole('Student');

$tutor_id = $_GET['id'] ?? 0;
$student_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Check if valid tutor
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.phone, u.gender, tp.* 
    FROM users u 
    JOIN tutor_profile tp ON u.id = tp.user_id 
    WHERE u.id = ? AND u.role = 'Tutor'
");
$stmt->execute([$tutor_id]);
$tutor = $stmt->fetch();

if (!$tutor) {
    die("Tutor not found.");
}

// Check if student has hired this tutor
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM guardian_request_applications gra
    JOIN guardian_requests gr ON gra.request_id = gr.id
    WHERE gra.tutor_id = ? AND gr.student_id = ? AND gra.status = 'Accepted'
");
$stmt->execute([$tutor_id, $student_id]);
$has_hired = $stmt->fetchColumn() > 0;

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review']) && $has_hired) {
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("
            INSERT INTO tutor_reviews (tutor_id, student_id, rating, review_text) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), review_text = VALUES(review_text)
        ");
        if ($stmt->execute([$tutor_id, $student_id, $rating, $review_text])) {
            $success = "Review submitted successfully!";
        } else {
            $error = "Failed to submit review.";
        }
    } else {
        $error = "Rating must be between 1 and 5.";
    }
}

// Fetch Reviews
$stmt = $pdo->prepare("
    SELECT tr.*, u.name as student_name 
    FROM tutor_reviews tr 
    JOIN users u ON tr.student_id = u.id 
    WHERE tr.tutor_id = ? 
    ORDER BY tr.created_at DESC
");
$stmt->execute([$tutor_id]);
$reviews = $stmt->fetchAll();

$avg_rating = 0;
if (count($reviews) > 0) {
    $total = 0;
    foreach($reviews as $r) $total += $r['rating'];
    $avg_rating = number_format($total / count($reviews), 1);
}

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
                <p class="text-muted">
                    <?= htmlspecialchars($tutor['subject1'] ?: 'Not specified') ?>
                    <?= !empty($tutor['subject2']) ? ' & ' . htmlspecialchars($tutor['subject2']) : '' ?>
                </p>
                <?php if($tutor['availability'] == 'Available'): ?>
                    <span class="badge bg-success">Available</span>
                <?php else: ?>
                    <span class="badge bg-danger">Not Available</span>
                <?php endif; ?>
                
                <div class="mt-3">
                    <span class="fs-4 text-warning">
                        <?php for($i=1; $i<=5; $i++) { echo $i <= round($avg_rating) ? '★' : '☆'; } ?>
                    </span>
                    <br>
                    <span class="fw-bold"><?= $avg_rating ?></span> <small class="text-muted">(<?= count($reviews) ?> reviews)</small>
                </div>
            </div>
        </div>
        
        <!-- Tutoring request form removed as direct requests are disabled -->
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-body p-4">
                <h4 class="card-title fw-bold mb-4">Tutor Details</h4>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Gender:</div>
                    <div class="col-sm-9"><?= htmlspecialchars($tutor['gender'] ?: 'Unspecified') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">Phone:</div>
                    <div class="col-sm-9"><?= htmlspecialchars($tutor['phone'] ?: 'N/A') ?></div>
                </div>
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
                    <div class="col-sm-9">BDT <?= htmlspecialchars(number_format($tutor['salary'] ?: 0, 2)) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3 fw-bold">About:</div>
                    <div class="col-sm-9"><?= nl2br(htmlspecialchars($tutor['description'] ?: 'No description provided.')) ?></div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="card mt-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-star text-warning me-2"></i>Reviews</h5>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if($has_hired): ?>
                    <?php
                        // Check if student already reviewed to pre-fill form
                        $my_review = null;
                        foreach($reviews as $r) {
                            if($r['student_id'] == $student_id) {
                                $my_review = $r;
                                break;
                            }
                        }
                    ?>
                    <div class="bg-light p-3 rounded mb-4">
                        <h6 class="fw-bold"><?= $my_review ? 'Update your review' : 'Write a review' ?></h6>
                        <form method="POST" action="">
                            <input type="hidden" name="submit_review" value="1">
                            <div class="mb-2">
                                <label class="form-label mb-1">Rating</label>
                                <select name="rating" class="form-select w-auto" required>
                                    <option value="">Select Rating</option>
                                    <option value="5" <?= ($my_review['rating'] ?? 0) == 5 ? 'selected' : '' ?>>5 - Excellent</option>
                                    <option value="4" <?= ($my_review['rating'] ?? 0) == 4 ? 'selected' : '' ?>>4 - Good</option>
                                    <option value="3" <?= ($my_review['rating'] ?? 0) == 3 ? 'selected' : '' ?>>3 - Average</option>
                                    <option value="2" <?= ($my_review['rating'] ?? 0) == 2 ? 'selected' : '' ?>>2 - Poor</option>
                                    <option value="1" <?= ($my_review['rating'] ?? 0) == 1 ? 'selected' : '' ?>>1 - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label mb-1">Review</label>
                                <textarea name="review_text" class="form-control" rows="2" placeholder="Share your experience with this tutor..."><?= htmlspecialchars($my_review['review_text'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Submit Review</button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if(empty($reviews)): ?>
                    <p class="text-muted">No reviews yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($reviews as $review): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($review['student_name']) ?></h6>
                                    <small class="text-muted"><?= date('M d, Y', strtotime($review['created_at'])) ?></small>
                                </div>
                                <div class="text-warning mb-2" style="font-size: 0.9rem;">
                                    <?php for($i=1; $i<=5; $i++) { echo $i <= $review['rating'] ? '★' : '☆'; } ?>
                                </div>
                                <?php if(!empty($review['review_text'])): ?>
                                    <p class="mb-0 text-secondary" style="font-size: 0.95rem;"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

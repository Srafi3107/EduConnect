<?php
require_once '../config.php';
checkRole('Tutor');

$tutor_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $class_level = trim($_POST['class_level'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $salary = trim($_POST['salary'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $availability = $_POST['availability'] ?? 'Available';

    $stmt = $pdo->prepare("
        UPDATE tutor_profile SET 
            subject = ?, class_level = ?, location = ?, 
            experience = ?, salary = ?, description = ?, availability = ? 
        WHERE user_id = ?
    ");
    
    if ($stmt->execute([$subject, $class_level, $location, $experience, $salary, $description, $availability, $tutor_id])) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Failed to update profile.";
    }
}

// Fetch current profile
$stmt = $pdo->prepare("SELECT * FROM tutor_profile WHERE user_id = ?");
$stmt->execute([$tutor_id]);
$profile = $stmt->fetch();

require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card mt-4">
            <div class="card-body p-4">
                <h4 class="card-title mb-4">Edit Profile</h4>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Subject(s)</label>
                            <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($profile['subject'] ?? '') ?>" placeholder="e.g. Math, Physics">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Class Level</label>
                            <input type="text" name="class_level" class="form-control" value="<?= htmlspecialchars($profile['class_level'] ?? '') ?>" placeholder="e.g. Grade 1-5, O-Level">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($profile['location'] ?? '') ?>" placeholder="e.g. New York, Online">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expected Salary (per month/hour)</label>
                            <input type="number" step="0.01" name="salary" class="form-control" value="<?= htmlspecialchars($profile['salary'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Experience</label>
                            <input type="text" name="experience" class="form-control" value="<?= htmlspecialchars($profile['experience'] ?? '') ?>" placeholder="e.g. 5 Years">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Availability Status</label>
                            <select name="availability" class="form-select">
                                <option value="Available" <?= ($profile['availability'] == 'Available') ? 'selected' : '' ?>>Available</option>
                                <option value="Not Available" <?= ($profile['availability'] == 'Not Available') ? 'selected' : '' ?>>Not Available</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">About Me (Description)</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Write something about your teaching style..."><?= htmlspecialchars($profile['description'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

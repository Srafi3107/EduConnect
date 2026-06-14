<?php
require_once '../config.php';
checkRole('Tutor');

$subjects = ['English', 'Math', 'Bangla', 'Physics', 'Chemistry', 'Biology', 'Arts', 'Commerce'];
$locations = ['Badda', 'Banani', 'Baridhara', 'Bashundhara', 'Dhanmondi', 'Gulshan', 'Khilgaon', 'Mirpur', 'Mohammadpur', 'Motijheel', 'New Market', 'Old Dhaka', 'Rampura', 'Tejgaon', 'Uttara'];

$tutor_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current profile
$stmt = $pdo->prepare("SELECT * FROM tutor_profile WHERE user_id = ?");
$stmt->execute([$tutor_id]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $class_level = trim($_POST['class_level'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $salary = trim($_POST['salary'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $availability = $_POST['availability'] ?? 'Available';
    $picture_path = $profile['picture'] ?? null;

    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['picture']['tmp_name'];
        $file_name = $_FILES['picture']['name'];
        $file_size = $_FILES['picture']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_exts)) {
            if ($file_size <= 2 * 1024 * 1024) { // 2MB Limit
                $new_file_name = "tutor_" . $tutor_id . "_" . time() . "." . $file_ext;
                $upload_dir = '../assets/uploads/tutors/';
                $dest_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Delete old picture if it exists
                    if (!empty($profile['picture'])) {
                        // Resolve local filepath from relative directory
                        $old_file = '../assets/uploads/tutors/' . basename($profile['picture']);
                        if (file_exists($old_file)) {
                            @unlink($old_file);
                        }
                    }
                    $picture_path = '/EduConnect/assets/uploads/tutors/' . $new_file_name;
                } else {
                    $error = "Failed to save uploaded image.";
                }
            } else {
                $error = "Profile picture must be under 2MB.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG and GIF files are allowed.";
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("
            UPDATE tutor_profile SET 
                subject = ?, class_level = ?, location = ?, 
                experience = ?, salary = ?, description = ?, availability = ?, picture = ?
            WHERE user_id = ?
        ");
        
        if ($stmt->execute([$subject, $class_level, $location, $experience, $salary, $description, $availability, $picture_path, $tutor_id])) {
            $success = "Profile updated successfully!";
            // Fetch updated profile
            $stmt = $pdo->prepare("SELECT * FROM tutor_profile WHERE user_id = ?");
            $stmt->execute([$tutor_id]);
            $profile = $stmt->fetch();
        } else {
            $error = "Failed to update profile.";
        }
    }
}

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

                 <form method="POST" action="" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <?php 
                            $avatar_url = !empty($profile['picture']) ? htmlspecialchars($profile['picture']) : "https://ui-avatars.com/api/?name=" . urlencode($_SESSION['name']) . "&background=random";
                        ?>
                        <img src="<?= $avatar_url ?>" alt="Profile Picture" class="tutor-avatar-lg mb-3 d-block mx-auto" id="avatar-preview" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['name']) ?>&background=random'">
                        <label class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-camera me-1"></i> Upload Picture
                            <input type="file" name="picture" class="d-none" accept="image/*" onchange="document.getElementById('avatar-preview').src = window.URL.createObjectURL(this.files[0])">
                        </label>
                        <div class="form-text mt-1">Upload a professional photo (JPG, PNG, max 2MB).</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $sub): ?>
                                    <option value="<?= $sub ?>" <?= ($profile['subject'] ?? '') === $sub ? 'selected' : '' ?>><?= $sub ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Class Level</label>
                            <input type="text" name="class_level" class="form-control" value="<?= htmlspecialchars($profile['class_level'] ?? '') ?>" placeholder="e.g. Grade 1-5, O-Level">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Location (Dhaka)</label>
                            <select name="location" class="form-select" required>
                                <option value="">Select Location</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc ?>" <?= ($profile['location'] ?? '') === $loc ? 'selected' : '' ?>><?= $loc ?></option>
                                <?php endforeach; ?>
                            </select>
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

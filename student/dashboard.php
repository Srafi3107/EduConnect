<?php
require_once '../config.php';
checkRole('Student');

$subjects = ['English', 'Math', 'Bangla', 'Physics', 'Chemistry', 'Biology', 'Arts', 'Commerce'];
$locations = ['Badda', 'Banani', 'Baridhara', 'Bashundhara', 'Dhanmondi', 'Gulshan', 'Khilgaon', 'Mirpur', 'Mohammadpur', 'Motijheel', 'New Market', 'Old Dhaka', 'Rampura', 'Tejgaon', 'Uttara'];

$search_subject = trim($_GET['subject'] ?? '');
$search_location = trim($_GET['location'] ?? '');
$search_class = trim($_GET['class'] ?? '');

$query = "
    SELECT u.id as user_id, u.name, tp.subject, tp.location, tp.class_level, tp.salary, tp.experience, tp.availability, tp.picture
    FROM users u
    JOIN tutor_profile tp ON u.id = tp.user_id
    WHERE u.role = 'Tutor'
";
$params = [];

if ($search_subject) {
    $query .= " AND tp.subject = ?";
    $params[] = $search_subject;
}
if ($search_location) {
    $query .= " AND tp.location = ?";
    $params[] = $search_location;
}
if ($search_class) {
    $query .= " AND tp.class_level LIKE ?";
    $params[] = "%$search_class%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tutors = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Find a Tutor</h2>
        <p class="text-muted">Search for the perfect tutor by subject, location, or class level.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <select name="subject" class="form-select">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub ?>" <?= $search_subject === $sub ? 'selected' : '' ?>><?= $sub ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="location" class="form-select">
                    <option value="">All Locations (Dhaka)</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc ?>" <?= $search_location === $loc ? 'selected' : '' ?>><?= $loc ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="class" class="form-control" placeholder="Class Level" value="<?= htmlspecialchars($search_class) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search"></i> Search</button>
            </div>
        </form>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php if(empty($tutors)): ?>
        <div class="col-12 text-center py-5">
            <h5 class="text-muted">No tutors found matching your criteria.</h5>
        </div>
    <?php else: ?>
        <?php foreach($tutors as $tutor): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <?php 
                            $tutor_pic = !empty($tutor['picture']) ? htmlspecialchars($tutor['picture']) : "https://ui-avatars.com/api/?name=" . urlencode($tutor['name']) . "&background=random";
                        ?>
                        <img src="<?= $tutor_pic ?>" alt="Avatar" class="tutor-avatar mb-3" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($tutor['name']) ?>&background=random'">
                        <h5 class="card-title fw-bold"><?= htmlspecialchars($tutor['name']) ?></h5>
                        <p class="card-text text-muted mb-1"><i class="fa-solid fa-book text-primary"></i> <?= htmlspecialchars($tutor['subject'] ?: 'Not specified') ?></p>
                        <p class="card-text text-muted mb-2"><i class="fa-solid fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($tutor['location'] ?: 'Not specified') ?></p>
                        
                        <?php if($tutor['availability'] == 'Available'): ?>
                            <span class="badge bg-success mb-3">Available</span>
                        <?php else: ?>
                            <span class="badge bg-danger mb-3">Not Available</span>
                        <?php endif; ?>
                        
                        <a href="/EduConnect/student/view_tutor.php?id=<?= $tutor['user_id'] ?>" class="btn btn-outline-primary w-100 mt-auto">View Profile</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

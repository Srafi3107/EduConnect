<?php
require_once '../config.php';
checkRole('Student');



$search_subject = trim($_GET['subject'] ?? '');
$search_location = trim($_GET['location'] ?? '');
$search_class = trim($_GET['class'] ?? '');

$query = "
    SELECT u.id as user_id, u.name, tp.subject1, tp.subject2, tp.location, tp.class_level, tp.salary, tp.experience, tp.availability, tp.picture
    FROM users u
    JOIN tutor_profile tp ON u.id = tp.user_id
    WHERE u.role = 'Tutor'
";
$params = [];

if ($search_subject) {
    $query .= " AND (tp.subject1 = ? OR tp.subject2 = ?)";
    $params[] = $search_subject;
    $params[] = $search_subject;
}
if ($search_location) {
    $query .= " AND tp.location = ?";
    $params[] = $search_location;
}
if ($search_class) {
    $query .= " AND tp.class_level = ?";
    $params[] = $search_class;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tutors = $stmt->fetchAll();

// Fetch active hired tutors
$stmt_active = $pdo->prepare("
    SELECT u.id as user_id, u.name, u.email, u.phone, tp.subject1, tp.subject2, tp.class_level, tp.location, tp.picture
    FROM guardian_request_applications gra
    JOIN guardian_requests gr ON gra.request_id = gr.id
    JOIN users u ON gra.tutor_id = u.id
    JOIN tutor_profile tp ON u.id = tp.user_id
    WHERE gr.student_id = ? AND gra.status = 'Accepted'
    GROUP BY u.id
");
$stmt_active->execute([$_SESSION['user_id']]);
$active_tutors = $stmt_active->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Find a Tutor</h2>
        <p class="text-muted">Search for the perfect tutor by subject, location, or class level.</p>
    </div>
</div>

<?php if (!empty($active_tutors)): ?>
<div class="card shadow-sm mb-5 border-primary border-2">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-4 text-primary"><i class="fa-solid fa-chalkboard-user me-2"></i>My Hired Tutors</h5>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach($active_tutors as $active_tutor): ?>
                <div class="col">
                    <div class="border rounded p-3 bg-light d-flex align-items-center">
                        <?php 
                            $a_tutor_pic = !empty($active_tutor['picture']) ? htmlspecialchars($active_tutor['picture']) : "https://ui-avatars.com/api/?name=" . urlencode($active_tutor['name']) . "&background=random";
                        ?>
                        <img src="<?= $a_tutor_pic ?>" alt="Avatar" class="tutor-avatar-sm me-3" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($active_tutor['name']) ?>&background=random'">
                        
                        <div class="flex-grow-1">
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($active_tutor['name']) ?></h6>
                            <p class="mb-1 text-muted small"><i class="fa-solid fa-book text-primary"></i> <?= htmlspecialchars($active_tutor['subject1']) ?></p>
                            <div class="d-flex gap-2 mt-2">
                                <a href="mailto:<?= htmlspecialchars($active_tutor['email']) ?>" class="btn btn-sm btn-outline-primary py-0"><i class="fa-solid fa-envelope"></i> Email</a>
                                <?php if(!empty($active_tutor['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($active_tutor['phone']) ?>" class="btn btn-sm btn-outline-success py-0"><i class="fa-solid fa-phone"></i> Call</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <a href="/EduConnect/student/view_tutor.php?id=<?= $active_tutor['user_id'] ?>" class="btn btn-sm btn-primary">Profile & Review</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

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
                <select name="class" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?= $cls ?>" <?= $search_class === $cls ? 'selected' : '' ?>><?= $cls ?></option>
                    <?php endforeach; ?>
                </select>
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
                        <p class="card-text text-muted mb-1">
                            <i class="fa-solid fa-book text-primary"></i> 
                            <?= htmlspecialchars($tutor['subject1'] ?: 'Not specified') ?>
                            <?= !empty($tutor['subject2']) ? ' & ' . htmlspecialchars($tutor['subject2']) : '' ?>
                        </p>
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

<?php
require_once '../config.php';
checkRole('Student');

$student_id = $_SESSION['user_id'];

// Fetch requests made by this student
$stmt = $pdo->prepare("
    SELECT r.id, r.message, r.status, r.created_at, u.name as tutor_name, tp.subject
    FROM requests r 
    JOIN users u ON r.tutor_id = u.id 
    JOIN tutor_profile tp ON u.id = tp.user_id
    WHERE r.student_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$student_id]);
$requests = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">My Requests</h2>
        <p class="text-muted">Track the status of the tutoring requests you have sent.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if(empty($requests)): ?>
            <p>You haven't sent any tutoring requests yet. <a href="/HomeTutor/student/dashboard.php">Find a tutor</a></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tutor</th>
                            <th>Subject</th>
                            <th>My Message</th>
                            <th>Date Sent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requests as $req): ?>
                            <tr>
                                <td><span class="fw-bold"><?= htmlspecialchars($req['tutor_name']) ?></span></td>
                                <td><?= htmlspecialchars($req['subject']) ?></td>
                                <td><?= nl2br(htmlspecialchars($req['message'])) ?></td>
                                <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        if($req['status'] == 'Pending') $badgeClass = 'badge-status-pending';
                                        if($req['status'] == 'Accepted') $badgeClass = 'badge-status-accepted';
                                        if($req['status'] == 'Rejected') $badgeClass = 'badge-status-rejected';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $req['status'] ?></span>
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

<?php
require_once '../config.php';
checkRole('Tutor');

$tutor_id = $_SESSION['user_id'];

// Fetch requests for this tutor
$stmt = $pdo->prepare("
    SELECT r.id, r.message, r.status, r.created_at, u.name as student_name, u.email as student_email 
    FROM requests r 
    JOIN users u ON r.student_id = u.id 
    WHERE r.tutor_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$tutor_id]);
$requests = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Tutor Dashboard</h2>
        <p class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>! Here are your tutoring requests.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4">Recent Requests</h5>
        <?php if(empty($requests)): ?>
            <p>No tutoring requests yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requests as $req): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($req['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($req['student_email']) ?></small>
                                </td>
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
                                <td>
                                    <?php if($req['status'] == 'Pending'): ?>
                                        <form method="POST" action="/HomeTutor/tutor/handle_request.php" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-sm btn-success" title="Accept"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="/HomeTutor/tutor/handle_request.php" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="fa-solid fa-times"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">No actions</span>
                                    <?php endif; ?>
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

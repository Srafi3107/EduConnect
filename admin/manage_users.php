<?php
require_once '../config.php';
checkRole('Admin');

$success = '';
$error = '';

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id_to_delete = $_POST['user_id'];
    
    // Don't allow admin to delete themselves
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        // Fetch and delete profile picture from disk if it exists
        $stmt_pic = $pdo->prepare("SELECT picture FROM tutor_profile WHERE user_id = ?");
        $stmt_pic->execute([$user_id_to_delete]);
        $pic = $stmt_pic->fetchColumn();
        if (!empty($pic)) {
            $old_file = '../assets/uploads/tutors/' . basename($pic);
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id_to_delete])) {
            $success = "User deleted successfully.";
        } else {
            $error = "Failed to delete user.";
        }
    }
}

// Handle Ban User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ban_user'])) {
    $user_id_to_ban = $_POST['user_id'];
    if ($user_id_to_ban == $_SESSION['user_id']) {
        $error = "You cannot ban your own account.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET status = 'Banned' WHERE id = ?");
        if ($stmt->execute([$user_id_to_ban])) {
            $success = "User has been banned.";
        } else {
            $error = "Failed to ban user.";
        }
    }
}

// Handle Unban User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['unban_user'])) {
    $user_id_to_unban = $_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET status = 'Active' WHERE id = ?");
    if ($stmt->execute([$user_id_to_unban])) {
        $success = "User has been unbanned.";
    } else {
        $error = "Failed to unban user.";
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold">Manage Users</h2>
        <p class="text-muted">View and manage all registered users in the system.</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php 
                                    $roleClass = 'bg-secondary';
                                    if($u['role'] == 'Admin') $roleClass = 'bg-dark';
                                    if($u['role'] == 'Tutor') $roleClass = 'bg-primary';
                                    if($u['role'] == 'Student') $roleClass = 'bg-success';
                                ?>
                                <span class="badge <?= $roleClass ?>"><?= $u['role'] ?></span>
                            </td>
                            <td>
                                <?php if(($u['status'] ?? 'Active') === 'Banned'): ?>
                                    <span class="badge bg-danger">Banned</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <?php if($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="delete_user" value="1">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                    
                                    <?php if(($u['status'] ?? 'Active') === 'Active'): ?>
                                        <form method="POST" action="" class="d-inline" onsubmit="return confirm('Ban this user? They will not be able to log in.');">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="ban_user" value="1">
                                            <button type="submit" class="btn btn-sm btn-warning"><i class="fa-solid fa-ban"></i> Ban</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="unban_user" value="1">
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i> Unban</button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

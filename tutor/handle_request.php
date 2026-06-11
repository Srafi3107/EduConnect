<?php
require_once '../config.php';
checkRole('Tutor');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $tutor_id = $_SESSION['user_id'];

    if ($action === 'accept' || $action === 'reject') {
        $status = ($action === 'accept') ? 'Accepted' : 'Rejected';
        
        // Update request status, ensuring it belongs to this tutor
        $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ? AND tutor_id = ?");
        $stmt->execute([$status, $request_id, $tutor_id]);
    }
}

header("Location: /HomeTutor/tutor/dashboard.php");
exit();
?>

<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE guardian_request_applications ADD COLUMN offered_by VARCHAR(20) DEFAULT NULL");
    echo "Column 'offered_by' added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'offered_by' already exists — no action needed.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>

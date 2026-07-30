<?php
require 'config.php';
// Check current DB name
$row = $pdo->query("SELECT DATABASE() as db")->fetch();
echo "Connected to database: " . $row['db'] . "\n\n";

// Describe the table
$cols = $pdo->query("DESCRIBE guardian_request_applications")->fetchAll();
echo "Columns in guardian_request_applications:\n";
foreach ($cols as $col) {
    echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
?>

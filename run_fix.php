<?php
// One-time fix: Adds missing columns to guardian_request_applications
// Run this by visiting: http://localhost/EduConnect/run_fix.php
// DELETE this file after running it.

$host = '127.0.0.1';
$port = '3306'; // Try default Apache MySQL port
$dbname = 'hometutor_db';

try {
    $pdo2 = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", 'root', '');
    $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db_row = $pdo2->query("SELECT DATABASE() as db")->fetch(PDO::FETCH_ASSOC);
    echo "<b>Connected to DB:</b> " . $db_row['db'] . " on port 3306<br><br>";

    // Show existing columns
    $cols = $pdo2->query("DESCRIBE guardian_request_applications")->fetchAll(PDO::FETCH_ASSOC);
    echo "<b>Existing columns:</b><br>";
    $existing = [];
    foreach ($cols as $col) {
        echo " - " . $col['Field'] . "<br>";
        $existing[] = $col['Field'];
    }
    echo "<br>";

    // Add offered_by if missing
    if (!in_array('offered_by', $existing)) {
        $pdo2->exec("ALTER TABLE guardian_request_applications ADD COLUMN offered_by ENUM('Tutor','Guardian') DEFAULT NULL");
        echo "<span style='color:green'>✅ Column <b>offered_by</b> added successfully.</span><br>";
    } else {
        echo "<span style='color:blue'>ℹ️ Column <b>offered_by</b> already exists — no change needed.</span><br>";
    }

    // Add proposed_salary if missing
    if (!in_array('proposed_salary', $existing)) {
        $pdo2->exec("ALTER TABLE guardian_request_applications ADD COLUMN proposed_salary DECIMAL(10,2) DEFAULT 0");
        echo "<span style='color:green'>✅ Column <b>proposed_salary</b> added successfully.</span><br>";
    } else {
        echo "<span style='color:blue'>ℹ️ Column <b>proposed_salary</b> already exists.</span><br>";
    }

    echo "<br><b style='color:green'>All done! You can now delete this file and visit the tutor dashboard.</b>";

} catch (PDOException $e) {
    echo "<b style='color:red'>Error on port 3306:</b> " . $e->getMessage() . "<br><br>";

    // Try port 3307 as fallback
    try {
        $pdo3 = new PDO("mysql:host=127.0.0.1;port=3307;dbname=hometutor_db;charset=utf8mb4", 'root', '');
        $pdo3->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_row = $pdo3->query("SELECT DATABASE() as db")->fetch(PDO::FETCH_ASSOC);
        echo "<b>Fallback — Connected on port 3307:</b> " . $db_row['db'] . "<br><br>";

        $cols = $pdo3->query("DESCRIBE guardian_request_applications")->fetchAll(PDO::FETCH_ASSOC);
        $existing = array_column($cols, 'Field');
        echo "<b>Existing columns:</b> " . implode(', ', $existing) . "<br><br>";

        if (!in_array('offered_by', $existing)) {
            $pdo3->exec("ALTER TABLE guardian_request_applications ADD COLUMN offered_by ENUM('Tutor','Guardian') DEFAULT NULL");
            echo "<span style='color:green'>✅ offered_by added on port 3307.</span><br>";
        } else {
            echo "<span style='color:blue'>ℹ️ offered_by already exists on port 3307.</span><br>";
        }

        echo "<br><b style='color:green'>Done on port 3307!</b>";
    } catch (PDOException $e2) {
        echo "<b style='color:red'>Also failed on port 3307:</b> " . $e2->getMessage();
    }
}
?>

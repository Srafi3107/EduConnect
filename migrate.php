<?php
require_once 'config.php';

if (php_sapi_name() !== 'cli') {
    die("This script can only be run via CLI.");
}

echo "Starting database migrations...\n";

// 1. Add picture to tutor_profile
try {
    $pdo->exec("ALTER TABLE tutor_profile ADD COLUMN picture VARCHAR(255) DEFAULT NULL;");
    echo "Added 'picture' column to 'tutor_profile'.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Column already exists
        echo "'picture' column already exists in 'tutor_profile'. Skipping.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}

// 2. Modify status in requests table
try {
    $pdo->exec("ALTER TABLE requests MODIFY COLUMN status ENUM('Pending', 'Accepted', 'Rejected', 'Negotiating') DEFAULT 'Pending';");
    echo "Updated 'status' ENUM in 'requests'.\n";
} catch (PDOException $e) {
    echo "Error modifying requests status ENUM: " . $e->getMessage() . "\n";
}

// 3. Add proposed_salary to requests
try {
    $pdo->exec("ALTER TABLE requests ADD COLUMN proposed_salary DECIMAL(10,2) DEFAULT NULL;");
    echo "Added 'proposed_salary' column to 'requests'.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "'proposed_salary' column already exists in 'requests'. Skipping.\n";
    } else {
        echo "Error adding proposed_salary: " . $e->getMessage() . "\n";
    }
}

// 4. Add offered_by to requests
try {
    $pdo->exec("ALTER TABLE requests ADD COLUMN offered_by ENUM('Student', 'Tutor') DEFAULT 'Student';");
    echo "Added 'offered_by' column to 'requests'.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "'offered_by' column already exists in 'requests'. Skipping.\n";
    } else {
        echo "Error adding offered_by: " . $e->getMessage() . "\n";
    }
}

// 5. Create guardian_requests table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS guardian_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            class_level VARCHAR(100) NOT NULL,
            location VARCHAR(255) NOT NULL,
            salary DECIMAL(10,2) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ");
    echo "Created 'guardian_requests' table.\n";
} catch (PDOException $e) {
    echo "Error creating guardian_requests: " . $e->getMessage() . "\n";
}

// 6. Create guardian_request_applications table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS guardian_request_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT NOT NULL,
            tutor_id INT NOT NULL,
            proposed_salary DECIMAL(10,2) NOT NULL,
            message TEXT,
            status ENUM('Pending', 'Accepted', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (request_id) REFERENCES guardian_requests(id) ON DELETE CASCADE,
            FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_application (request_id, tutor_id)
        );
    ");
    echo "Created 'guardian_request_applications' table.\n";
} catch (PDOException $e) {
    echo "Error creating guardian_request_applications: " . $e->getMessage() . "\n";
}

// 7. Modify status in guardian_request_applications table to allow Negotiating
try {
    $pdo->exec("ALTER TABLE guardian_request_applications MODIFY COLUMN status ENUM('Pending', 'Accepted', 'Rejected', 'Negotiating') DEFAULT 'Pending';");
    echo "Updated 'status' ENUM in 'guardian_request_applications'.\n";
} catch (PDOException $e) {
    echo "Error modifying guardian_request_applications status ENUM: " . $e->getMessage() . "\n";
}

// 8. Add offered_by column to guardian_request_applications
try {
    $pdo->exec("ALTER TABLE guardian_request_applications ADD COLUMN offered_by ENUM('Tutor', 'Guardian') DEFAULT 'Tutor';");
    echo "Added 'offered_by' column to 'guardian_request_applications'.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "'offered_by' column already exists in 'guardian_request_applications'. Skipping.\n";
    } else {
        echo "Error adding offered_by: " . $e->getMessage() . "\n";
    }
}

// 9. Add additional_address column to guardian_requests
try {
    $pdo->exec("ALTER TABLE guardian_requests ADD COLUMN additional_address TEXT DEFAULT NULL;");
    echo "Added 'additional_address' column to 'guardian_requests'.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "'additional_address' column already exists in 'guardian_requests'. Skipping.\n";
    } else {
        echo "Error adding additional_address: " . $e->getMessage() . "\n";
    }
}

echo "Migrations completed successfully!\n";
?>

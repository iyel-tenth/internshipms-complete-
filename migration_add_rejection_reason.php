<?php
/**
 * Database Migration: Add rejection_reason column to student_users table
 * Instructions: Visit this file in your browser (once) to run the migration
 * Then delete this file for security
 */

include 'db_connect.php';

// Check if column already exists
$column_check = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'internship_db' AND TABLE_NAME = 'student_users' AND COLUMN_NAME = 'rejection_reason'");

if ($column_check && $column_check->num_rows > 0) {
    echo "<h3 style='color: green;'>✅ Migration already completed!</h3>";
    echo "<p>The 'rejection_reason' column already exists in the student_users table.</p>";
} else {
    // Add the migration column
    $sql = "ALTER TABLE student_users ADD COLUMN rejection_reason VARCHAR(500) DEFAULT NULL AFTER agency_status";
    
    if ($conn->query($sql) === TRUE) {
        echo "<h3 style='color: green;'>✅ Migration successful!</h3>";
        echo "<p>The 'rejection_reason' column has been added to the student_users table.</p>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Delete this file (migration_add_rejection_reason.php) from your server for security</li>";
        echo "<li>Admin can now reject applications with a reason</li>";
        echo "<li>Students will see the rejection reason in their dashboard</li>";
        echo "</ol>";
    } else {
        echo "<h3 style='color: red;'>❌ Migration failed!</h3>";
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

$conn->close();
?>

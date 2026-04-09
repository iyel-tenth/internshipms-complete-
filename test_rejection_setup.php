<?php
/**
 * Diagnostic Script for Rejection Feature
 * This script tests if everything is set up correctly for the rejection feature
 */

include 'db_connect.php';

echo "<h2>Rejection Feature Diagnostic Report</h2>";

// Get database name
$db_result = $conn->query("SELECT DATABASE() as db_name");
if ($db_result) {
    $db_row = $db_result->fetch_assoc();
    $db_name = $db_row['db_name'];
    echo "<p><strong>Database Name:</strong> $db_name</p>";
} else {
    echo "<p style='color:red;'><strong>ERROR:</strong> Could not get database name</p>";
}

// Check if rejection_reason column exists
echo "<h3>Checking for 'rejection_reason' column...</h3>";
$column_check = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$db_name' AND TABLE_NAME = 'student_users' AND COLUMN_NAME = 'rejection_reason'");

if ($column_check && $column_check->num_rows > 0) {
    echo "<p style='color:green;'><strong>✅ Column EXISTS</strong> - rejection_reason column is present in student_users table</p>";
} else {
    echo "<p style='color:orange;'><strong>⚠️ Column NOT FOUND</strong> - Creating rejection_reason column...</p>";
    
    $alter_result = $conn->query("ALTER TABLE student_users ADD COLUMN rejection_reason VARCHAR(500) DEFAULT NULL AFTER agency_status");
    
    if ($alter_result) {
        echo "<p style='color:green;'><strong>✅ SUCCESS</strong> - rejection_reason column has been created</p>";
    } else {
        echo "<p style='color:red;'><strong>❌ FAILED</strong> - Could not create column: " . $conn->error . "</p>";
    }
}

// Check table structure
echo "<h3>student_users Table Structure:</h3>";
$structure = $conn->query("DESCRIBE student_users");
echo "<table border='1' cellpadding='8'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . ($row['Key'] ?? '') . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test a sample rejection query
echo "<h3>Testing Rejection Query:</h3>";
$test_query = "UPDATE student_users SET agency_status = 'rejected', rejection_reason = ?, agency_name = NULL WHERE student_id = ?";
$test_stmt = $conn->prepare($test_query);

if ($test_stmt) {
    echo "<p style='color:green;'><strong>✅ Query prepares successfully</strong></p>";
} else {
    echo "<p style='color:red;'><strong>❌ Query preparation failed:</strong> " . $conn->error . "</p>";
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #0A1D56; }
h3 { color: #0A1D56; margin-top: 20px; }
table { background: #f9f9f9; border-collapse: collapse; }
td, th { text-align: left; }
p { line-height: 1.6; }
</style>

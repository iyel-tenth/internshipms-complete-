<?php
session_start();
include 'db_connect.php';

// Only update last_logout if user is logged in
if (!empty($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']); // ensure it's an integer to prevent SQL injection
    $role = $_SESSION['role'] ?? '';

    // Update last logout timestamp based on role
    if ($role === 'Student') {
        // Update student_users table
        $sql = "UPDATE student_users SET last_login = NOW() WHERE student_id = $user_id";
        if (!mysqli_query($conn, $sql)) {
            error_log("Failed to update student logout time: " . mysqli_error($conn));
        }
    } else {
        // Update users table (Admin, Instructor, Employer)
        $sql = "UPDATE users SET last_logout = NOW() WHERE id = $user_id";
        if (!mysqli_query($conn, $sql)) {
            error_log("Failed to update user logout time: " . mysqli_error($conn));
        }
    }
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>

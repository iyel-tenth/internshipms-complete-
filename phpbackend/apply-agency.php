<?php
session_start();
include(__DIR__ . '/../db_connect.php');

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if agency is provided
if (!isset($_POST['agency'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Agency not provided']);
    exit();
}

$student_id = $_SESSION['user_id'];
$agency = trim($_POST['agency']);

// Check if student already has a pending or approved agency application
$check_query = "SELECT agency_name FROM student_users WHERE student_id = ? AND agency_name IS NOT NULL AND agency_status IN ('pending', 'approved')";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("s", $student_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You have already applied for an agency. Please wait for admin approval.']);
    exit();
}

// Update student_users with the agency (status will be pending by default)
$update_query = "UPDATE student_users SET agency_name = ?, agency_status = 'pending', rejection_reason = NULL WHERE student_id = ?";
$update_stmt = $conn->prepare($update_query);

if (!$update_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$update_stmt->bind_param("ss", $agency, $student_id);


if ($update_stmt->execute()) {
    // Decrement updated_slots for the agency
    $update_slots_query = "UPDATE agency_slots SET updated_slots = GREATEST(updated_slots - 1, 0) WHERE agency = ?";
    $update_slots_stmt = $conn->prepare($update_slots_query);
    if ($update_slots_stmt) {
        $update_slots_stmt->bind_param("s", $agency);
        $update_slots_stmt->execute();
        $update_slots_stmt->close();
    }
    echo json_encode(['success' => true, 'message' => 'Your application has been submitted! Waiting for admin approval.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit application: ' . $update_stmt->error]);
}

$update_stmt->close();
$check_stmt->close();
$conn->close();
?>

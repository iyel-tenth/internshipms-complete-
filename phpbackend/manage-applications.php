<?php
session_start();
include(__DIR__ . '/../db_connect.php');

// Set header for JSON response
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check required parameters
if (!isset($_POST['action']) || !isset($_POST['student_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$action = trim($_POST['action']);
$student_id = trim($_POST['student_id']);

// Check if rejection_reason column exists (for reject action)
if ($action === 'reject') {
    // Get the current database name
    $db_name_result = $conn->query("SELECT DATABASE() as db_name");
    $db_name_row = $db_name_result->fetch_assoc();
    $db_name = $db_name_row['db_name'];
    
    $column_check = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $db_name . "' AND TABLE_NAME = 'student_users' AND COLUMN_NAME = 'rejection_reason' LIMIT 1");
    
    if (!$column_check || $column_check->num_rows === 0) {
        // Column doesn't exist, create it
        $alter_result = $conn->query("ALTER TABLE student_users ADD COLUMN rejection_reason VARCHAR(500) DEFAULT NULL AFTER agency_status");
        if (!$alter_result) {
            // Log the error for debugging
            error_log('ALTER TABLE failed: ' . $conn->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database setup error: ' . $conn->error]);
            exit();
        }
    }
}

if ($action === 'approve') {
    // Check if agency_name is provided for approval
    if (!isset($_POST['agency_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Agency name not provided']);
        exit();
    }
    
    $agency_name = trim($_POST['agency_name']);
    
    // Start transaction for data consistency
    mysqli_begin_transaction($conn);
    
    try {
        // Lock the agency row and ensure updated_slots is initialized
        $slot_check_sql = "SELECT slots, updated_slots FROM agency_slots WHERE agency = ? FOR UPDATE";
        $slot_stmt = $conn->prepare($slot_check_sql);
        if (!$slot_stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        $slot_stmt->bind_param("s", $agency_name);
        $slot_stmt->execute();
        $slot_result = $slot_stmt->get_result();
        $slot_row = $slot_result->fetch_assoc();
        
        if (!$slot_row) {
            throw new Exception('Agency not found: ' . $agency_name);
        }

        $slots_total = (int)$slot_row['slots'];
        $slots_available = isset($slot_row['updated_slots']) ? (int)$slot_row['updated_slots'] : $slots_total;

        // If updated_slots was never initialized (null or zero while total > 0), set it to slots
        if (($slot_row['updated_slots'] === null || (int)$slot_row['updated_slots'] === 0) && $slots_total > 0) {
            $reset_sql = "UPDATE agency_slots SET updated_slots = slots WHERE agency = ?";
            $reset_stmt = $conn->prepare($reset_sql);
            if (!$reset_stmt) {
                throw new Exception('Database error: ' . $conn->error);
            }
            $reset_stmt->bind_param("s", $agency_name);
            if (!$reset_stmt->execute()) {
                throw new Exception('Failed to initialize updated slots: ' . $reset_stmt->error);
            }
            $slots_available = $slots_total;
        }

        if ($slots_available <= 0) {
            throw new Exception('No available slots for this agency.');
        }

        // Update student status to approved
        $update_student = "UPDATE student_users SET agency_status = 'approved' WHERE student_id = ?";
        $stmt1 = $conn->prepare($update_student);
        
        if (!$stmt1) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $stmt1->bind_param("s", $student_id);
        
        if (!$stmt1->execute()) {
            throw new Exception('Failed to approve application: ' . $stmt1->error);
        }
        
        // Decrease updated_slots by 1 (tracks approved students)
        $update_slots = "UPDATE agency_slots SET updated_slots = updated_slots - 1 WHERE agency = ? AND updated_slots > 0";
        $stmt2 = $conn->prepare($update_slots);
        
        if (!$stmt2) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $stmt2->bind_param("s", $agency_name);
        
        if (!$stmt2->execute()) {
            throw new Exception('Failed to update agency slots: ' . $stmt2->error);
        }
        
        // Check if any rows were actually updated
        if ($stmt2->affected_rows === 0) {
            throw new Exception('No matching agency found or updated slots already at 0. Agency: ' . $agency_name . ' Please verify the agency name exists in the system.');
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'message' => 'Application approved successfully! Slots decreased by 1.']);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} elseif ($action === 'reject') {
    // Check if rejection_reason is provided
    $rejection_reason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : '';
    
    if (empty($rejection_reason)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Rejection reason is required']);
        exit();
    }
    
    // Update student status to rejected, clear agency_name, and store rejection reason
    $reject_student = "UPDATE student_users SET agency_name = NULL, agency_status = 'rejected', rejection_reason = ? WHERE student_id = ?";
    $stmt = $conn->prepare($reject_student);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("ss", $rejection_reason, $student_id);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to reject application: ' . $stmt->error]);
        $stmt->close();
        exit();
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Application rejected successfully! Student has been notified.']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Student not found or already rejected']);
    }
    
    $stmt->close();
    
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>

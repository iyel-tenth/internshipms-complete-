<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = $_SESSION['role'];

if (!isset($_POST['id']) || !isset($_POST['step'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing POST data']);
    exit;
}

$id = intval($_POST['id']);
$step = intval($_POST['step']);

// Role-based allowed steps
$allowedSteps = ($role === 'Admin') ? [1,2,3] : (($role === 'Student') ? [4,5,6,7] : []);

if (!in_array($step, $allowedSteps)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not allowed for this role']);
    exit;
}

$column = "step" . $step;

// Fetch row to validate existence and sequencing
$rowSql = "SELECT step1, step2, step3, step4, step5, step6, step7 FROM moa_tracking WHERE id = ?";
$rowStmt = $conn->prepare($rowSql);
if (!$rowStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed (fetch): ' . $conn->error]);
    exit;
}

$rowStmt->bind_param("i", $id);
$rowStmt->execute();
$rowResult = $rowStmt->get_result();
$row = $rowResult->fetch_assoc();
$rowStmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'ID not found']);
    exit;
}

// Prevent re-setting the same step
if ((int)$row[$column] === 1) {
    echo json_encode(['success' => true, 'message' => 'Already completed']);
    exit;
}

// Enforce sequential completion: all previous steps must be done
for ($i = 1; $i < $step; $i++) {
    if ((int)$row['step'.$i] !== 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Previous steps not completed']);
        exit;
    }
}

// Update the step column
$sql = "UPDATE moa_tracking SET $column = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
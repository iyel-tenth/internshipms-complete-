<?php
// Clean output buffer to prevent any accidental output
ob_clean();

// Set JSON header first
header('Content-Type: application/json', true);

// Include database connection
if (!file_exists(__DIR__ . '/../db_connect.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection file not found']);
    exit();
}

include(__DIR__ . '/../db_connect.php');

// Check database connection
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if agency parameter is provided
if (!isset($_GET['agency']) || empty(trim($_GET['agency']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Agency name is required']);
    exit();
}

$agency = trim($_GET['agency']);

// Fetch students who have applied to this agency (approved or pending)
$query = "SELECT 
            student_id, 
            first_name, 
            last_name, 
            email, 
            agency_status 
        FROM student_users 
        WHERE agency_name = ? AND agency_status IN ('approved', 'pending')
        ORDER BY agency_status DESC, last_name ASC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param("s", $agency);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    exit();
}

$result = $stmt->get_result();
$interns = [];

while ($row = $result->fetch_assoc()) {
    $interns[] = $row;
}

http_response_code(200);
echo json_encode(['success' => true, 'interns' => $interns]);

$stmt->close();
$conn->close();
?>

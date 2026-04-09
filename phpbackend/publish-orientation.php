<?php
include '../db_connect.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid orientation ID']);
        exit();
    }

    // Update orientation to published status
    $update_query = $conn->prepare("UPDATE orientation_records SET is_published = 1 WHERE id = ?");
    $update_query->bind_param("i", $id);

    if ($update_query->execute()) {
        echo json_encode(['success' => true, 'message' => 'Orientation published successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to publish orientation: ' . $conn->error]);
    }

    $update_query->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

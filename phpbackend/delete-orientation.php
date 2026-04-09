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

    // Delete from database
    $delete_query = $conn->prepare("DELETE FROM orientation_records WHERE id = ?");
    $delete_query->bind_param("i", $id);

    if ($delete_query->execute()) {
        echo json_encode(['success' => true, 'message' => 'Orientation deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete orientation: ' . $conn->error]);
    }

    $delete_query->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

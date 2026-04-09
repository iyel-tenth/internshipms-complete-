<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id'])) {
        echo 'Missing ID';
        exit;
    }

    $id = intval($_POST['id']);

    // Check if the ID exists
    $checkSql = "SELECT id FROM moa_tracking WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        echo 'Record not found';
        exit;
    }

    $checkStmt->close();

    // Delete the record
    $deleteSql = "DELETE FROM moa_tracking WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $id);

    if ($deleteStmt->execute()) {
        echo 'success';
    } else {
        echo 'Error: ' . $deleteStmt->error;
    }

    $deleteStmt->close();
    $conn->close();
} else {
    echo 'Invalid request method';
}
?>
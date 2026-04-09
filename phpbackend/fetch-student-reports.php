<?php
include '../db_connect.php';

header('Content-Type: application/json');

$doc_type = isset($_GET['doc_type']) ? $_GET['doc_type'] : '';

if (!$doc_type) {
    echo json_encode(['error' => 'Missing doc_type']);
    exit;
}

// Fetch student reports for the given doc_type with course_major
$query = "SELECT sr.student_id, sr.file_path, su.first_name, su.last_name, su.course_major FROM student_reports sr JOIN student_users su ON sr.student_id = su.student_id WHERE sr.doc_type = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $doc_type);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = [
        'name' => $row['first_name'] . ' ' . $row['last_name'],
        'major' => $row['course_major'],
        'file_name' => basename($row['file_path'])
    ];
}

$stmt->close();

// Return as JSON
echo json_encode(['reports' => $reports]);

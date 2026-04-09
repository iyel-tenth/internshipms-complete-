<?php
session_start();
include '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$student_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$doc_type = $_POST['doc_type'] ?? $_GET['doc_type'] ?? '';

// Define upload directory
$upload_dir = '../uploads/reports_student/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Set max file size (10MB)
$max_size = 10 * 1024 * 1024; // 10MB

if ($action === 'upload' && isset($_FILES['pdf'])) {
    $file = $_FILES['pdf'];
    if ($file['type'] !== 'application/pdf') {
        echo json_encode(['success' => false, 'error' => 'Only PDF files allowed.']);
        exit();
    }
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'error' => 'PDF file size must not exceed 10MB.']);
        exit();
    }
    $filename = $student_id . '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $doc_type) . '_' . time() . '.pdf';
    $target = $upload_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Save or update in DB
        $stmt = $conn->prepare("REPLACE INTO student_reports (student_id, doc_type, file_path, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('sss', $student_id, $doc_type, $filename);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true, 'file' => $filename]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload failed.']);
    }
    exit();
}

if ($action === 'delete') {
    // Get file path from DB
    $stmt = $conn->prepare("SELECT file_path FROM student_reports WHERE student_id = ? AND doc_type = ?");
    $stmt->bind_param('ss', $student_id, $doc_type);
    $stmt->execute();
    $stmt->bind_result($file_path);
    if ($stmt->fetch() && $file_path) {
        $file = $upload_dir . $file_path;
        if (file_exists($file)) {
            unlink($file);
        }
    }
    $stmt->close();
    // Remove from DB
    $stmt = $conn->prepare("DELETE FROM student_reports WHERE student_id = ? AND doc_type = ?");
    $stmt->bind_param('ss', $student_id, $doc_type);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid request.']);

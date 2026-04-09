<?php
include 'db_connect.php';
session_start();

$user_id = $_SESSION['user_id'];
$date = date('Y-m-d');
$total = (int) $_POST['total'];

if ($total > 8) {
    $total = 8; // cap daily hours if needed
}

$stmt = $conn->prepare(
    "INSERT INTO attendance (user_id, attendance_date, total_hours)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE total_hours = VALUES(total_hours)"
);

$stmt->bind_param("isi", $user_id, $date, $total);
$stmt->execute();

echo "Attendance saved successfully";

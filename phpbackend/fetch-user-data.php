<?php
include '../db_connect.php';

// Fetch user data grouped by date
$query = "SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch data"]);
    exit;
}

$data = [
    "labels" => [],
    "values" => []
];

while ($row = mysqli_fetch_assoc($result)) {
    $data["labels"][] = $row["date"];
    $data["values"][] = $row["count"];
}

header('Content-Type: application/json');
echo json_encode($data);
?>
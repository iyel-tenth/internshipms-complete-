<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "internship_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // make sure it’s an integer (security)

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ..orientationrec.php?msg=UserDeleted");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "❌ No user ID provided.";
}

$conn->close();
?>

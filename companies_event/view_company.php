<?php
include '../db_connect.php';  // Adjust this path to your actual db_connect.php location

$query = "SELECT * FROM companies";  // your table name here
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['company_name'] . "<br>";
}
?>

<?php
include(__DIR__ . '/../db_connect.php');

$id = intval($_POST['id']);
$source = $_POST['source'] === 'student_users' ? 'student_users' : 'users';

if ($source === 'student_users') {
    $query = "DELETE FROM student_users WHERE id=$id";
} else {
    $query = "DELETE FROM users WHERE id=$id";
}

if (mysqli_query($conn, $query)) {
    echo 'success';
} else {
    echo 'error: ' . mysqli_error($conn);
}
?>
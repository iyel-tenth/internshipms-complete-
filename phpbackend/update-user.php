<?php
include '../../db_connect.php';

$id = intval($_POST['id']);
$source = $_POST['source'] === 'student_users' ? 'student_users' : 'users';
$first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
$last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$role = mysqli_real_escape_string($conn, $_POST['role']);
$course_major = mysqli_real_escape_string($conn, $_POST['course_major']);

if ($source === 'student_users') {
    $query = "UPDATE student_users SET first_name='$first_name', last_name='$last_name', email='$email', role='$role', course_major='$course_major' WHERE id=$id";
} else {
    $query = "UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email', role='$role', course_major='$course_major' WHERE id=$id";
}

if (mysqli_query($conn, $query)) {
    echo 'success';
} else {
    echo 'error: ' . mysqli_error($conn);
}
?>
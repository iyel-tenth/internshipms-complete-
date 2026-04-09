<?php
include '../../db_connect.php';

$id = intval($_POST['id']);
$name = mysqli_real_escape_string($conn, $_POST['template_name']);
$desc = mysqli_real_escape_string($conn, $_POST['template_description']);

$query = "UPDATE form_templates 
          SET template_name='$name', template_description='$desc'
          WHERE id=$id";

if (mysqli_query($conn, $query)) {
    echo "success";
} else {
    echo "error";
}
?>
<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $template_name = mysqli_real_escape_string($conn, $_POST['template_name']);
    $template_description = mysqli_real_escape_string($conn, $_POST['template_description']);
    
    // Update the form template
    $query = "UPDATE form_templates 
              SET template_name = '$template_name', 
                  template_description = '$template_description' 
              WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request";
}

mysqli_close($conn);
?>

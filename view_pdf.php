<?php
include 'db_connect.php';

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $query = "SELECT pdf_file, pdf_type, pdf_name 
              FROM form_templates 
              WHERE id = $id";

    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {

        header("Content-Type: " . $row['pdf_type']);
        header("Content-Disposition: inline; filename=\"" . $row['pdf_name'] . "\"");

        echo $row['pdf_file'];
    } else {
        echo "PDF not found.";
    }
}
?>

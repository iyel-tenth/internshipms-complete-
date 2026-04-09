<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connect.php';

if (!isset($_FILES['pdf_file'])) {
    die("No file uploaded.");
}

$template_name = mysqli_real_escape_string($conn, $_POST['template_name']);
$template_description = mysqli_real_escape_string($conn, $_POST['template_description']);

$fileTmp  = $_FILES['pdf_file']['tmp_name'];
$fileName = $_FILES['pdf_file']['name'];
$fileType = $_FILES['pdf_file']['type'];
$fileSize = $_FILES['pdf_file']['size'];

if ($fileType !== 'application/pdf') {
    die("Only PDF files are allowed.");
}

if ($fileSize <= 0) {
    die("Invalid file size.");
}

$pdfData = file_get_contents($fileTmp);
$pdfData = mysqli_real_escape_string($conn, $pdfData);

$sql = "INSERT INTO form_templates 
        (template_name, template_description, pdf_file, pdf_name, pdf_type)
        VALUES 
        ('$template_name', '$template_description', '$pdfData', '$fileName', '$fileType')";

if (!mysqli_query($conn, $sql)) {
    die("Database error: " . mysqli_error($conn));
}

header("Location: ../admin/saforms.php");
exit;

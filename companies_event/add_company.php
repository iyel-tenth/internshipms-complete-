<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $company_address = mysqli_real_escape_string($conn, $_POST['company_address']);
    $job_listings = mysqli_real_escape_string($conn, $_POST['job_listings']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO companies (company_name, company_address, job_listings, status) VALUES ('$company_name', '$company_address', '$job_listings', '$status')";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to company_profile.php after successful insert
        header("Location: ../company_profile.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Add Company</title>
  <link rel="stylesheet" type="text/css" href="css/adm.css" />
</head>
<body>
  <h1>Add New Company</h1>

  <?php if (isset($error)) { ?>
      <p style="color:red;"><?php echo $error; ?></p>
  <?php } ?>

  <form method="post" action="add_company.php">
    <label>Company Name:</label><br>
    <input type="text" name="company_name" required><br><br>

    <label>Company Address:</label><br>
    <textarea name="company_address" rows="3" required></textarea><br><br>

    <label>Job Listings:</label><br>
    <input type="text" name="job_listings" required><br><br>

    <label>Status:</label><br>
    <select name="status" required>
      <option value="">Select status</option>
      <option value="Active">Active</option>
      <option value="Inactive">Inactive</option>
    </select><br><br>

    <button type="submit">Add Company</button>
  </form>

  <br>
  <a href="../company_profile.php">Back to Company List</a>
</body>
</html>

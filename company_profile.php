
<?php
include 'db_connect.php';

$sql = "SELECT * FROM companies";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title> Company Profile | Admin</title>
  <link rel="stylesheet" type="text/css" href="css/adm.css">
</head>

<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
  <nav>
    <label class="logo">INTERNBUDDY.IMS</label>

      <ul>
      <li><a href="admin_page.php">HOME</a></li>
      <li class="dropdown">
        <a href="#">INTERNSHIP PORTAL <i class="fas fa-caret-down"></i></a>
        <div class="dropdown__menu">
            <ul>
                <li><a href="orientationrec.php">Orientation Record</a> </li>
                <li><a href="templates.php">Templates</a> </li>
            </ul>
        </div>
        </li>
      <li><a href="company_profile.php">JOB PORTAL</a></li>
      <li><a href="configuration.php">MANAGE PROFILE</a></li>
      <li><a href="index.php">LOGOUT</a></li>
    </ul>
  </nav>
   <body>
    <br> <br> <br> <br>
    <h1>Company Profile</h1><br>
    <p>Displays the list of affiliated industry partners</p>
    <br>
    <a href="companies_event/add_company.php" class="add-button">+ Add Company</a>
    <table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Company Name</th>
            <th>Company Address</th>
            <th>Job Listings</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $i = 1;
        while ($row = mysqli_fetch_assoc($result)):
        ?>
        <tr>
            <td><?= $i++; ?></td>
            <td><?= $row['company_name']; ?></td>
            <td><?= $row['company_address']; ?></td>
            <td><?= $row['job_listings']; ?></td>
            <td><?= $row['status']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
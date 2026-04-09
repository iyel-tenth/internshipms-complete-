<?php
session_start();

if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) {

 ?>
<!DOCTYPE html>
<html>
<head>
  <title>INTERNBUDDY>IMS</title>
  <link rel="stylesheet" type="text/css" href="css/login.css">
</head>
<body>
    <h1>Hello, <?php echo $_SESSION['name']; ?></h1>
    <a href="logout.php" class="logout-btn">Log out</a>
</body>
</html>

<?php 
}else{
    header("Location: login.php");
    exit();
}
 ?>
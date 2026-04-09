<?php
include 'db_connect.php';
$id = $_GET['id'];

if (isset($_POST['update'])) {
    $name = $_POST['company_name'];
    $address = $_POST['company_address'];

    mysqli_query($conn, "UPDATE companies SET company_name='$name', company_address='$address' WHERE id=$id");
    header("Location: company_list.php");
}
?>

<?php
$result = mysqli_query($conn, "SELECT * FROM companies WHERE id=$id");
$row = mysqli_fetch_assoc($result);
?>

<form method="post">
    <label>Name</label>
    <input type="text" name="company_name" value="<?= $row['company_name']; ?>" required>

    <label>Address</label>
    <input type="text" name="company_address" value="<?= $row['company_address']; ?>" required>

    <button name="update">Update</button>
</form>

<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agency = mysqli_real_escape_string($conn, $_POST['agency']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $slots = mysqli_real_escape_string($conn, $_POST['slots']);
    $starting_date = $_POST['starting_date'];
    $ending_date = $_POST['ending_date'];

$sql = "INSERT INTO agency_slots
        (agency, address, slots, updated_slots, starting_date, ending_date, published)
        VALUES 
        ('$agency', '$address', '$slots', '$slots', '$starting_date', '$ending_date', 0)";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../admin/internslots.php?msg=added");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Agency</title>
    <link rel="stylesheet" href="../css/addevent.css">
</head>
<body>

<?php include_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

<form method="POST" onsubmit="return confirmAdd()">
    <h2>Add Agency</h2>

    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

    <label>Academic Term:</label>
    <input type="text" name="academic_term" required>

    <label>Agency:</label>
    <input type="text" name="agency" required>

    <label>Slots:</label>
    <input type="text" name="slots" required>

    <label>Address:</label>
    <input type="text" name="address" required>

    <label>Starting Date:</label>
    <input type="date" name="starting_date" required>

    <label>End Date:</label>
    <input type="date" name="ending_date" required>

    <button type="submit" class="btn btn-success">Add Agency</button>
    <a href="../admin/internslots.php" class="btn btn-secondary" onclick="return confirmCancel()">Cancel</a>
</form>

<script>
function confirmAdd() {
    return confirm("Are you sure you want to add this orientation event?");
}

function confirmCancel() {
    return confirm("Are you sure you want to cancel?");
}
</script>

</body>
</html>

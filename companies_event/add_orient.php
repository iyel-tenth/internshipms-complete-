<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $academic_term = mysqli_real_escape_string($conn, $_POST['academic_term']);
    $orientation_title = mysqli_real_escape_string($conn, $_POST['orientation_title']);
    $venue = mysqli_real_escape_string($conn, $_POST['venue']);
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $sql = "INSERT INTO orientation_records 
            (academic_term, orientation_title, venue, event_date, start_time, end_time)
            VALUES 
            ('$academic_term', '$orientation_title', '$venue', '$event_date', '$start_time', '$end_time')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../orientationrec.php?msg=added");
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
    <title>Add Orientation Event</title>
    <link rel="stylesheet" href="../css/addevent.css">
</head>
<body>

<?php include_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

<form method="POST" onsubmit="return confirmAdd()">
    <h2>Add Orientation Event</h2>

    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

    <label>Academic Term:</label>
    <input type="text" name="academic_term" required>

    <label>Orientation Title:</label>
    <input type="text" name="orientation_title" required>

    <label>Venue:</label>
    <input type="text" name="venue" required>

    <label>Date:</label>
    <input type="date" name="event_date" required>

    <label>Start Time:</label>
    <input type="time" name="start_time" required>

    <label>End Time:</label>
    <input type="time" name="end_time" required>

    <button type="submit" class="btn btn-success">Add Event</button>
    <a href="../orientationrec.php" class="btn btn-secondary" onclick="return confirmCancel()">Cancel</a>
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

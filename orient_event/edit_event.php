<?php
include '../db_connect.php';

// ------------------------
// 1. Get Event by ID
// ------------------------
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "SELECT * FROM orientation_records WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $event = mysqli_fetch_assoc($result);

    if (!$event) {
        die("Event not found.");
    }
} else {
    die("ID not provided.");
}

// ------------------------
// 2. Update Event on Submit
// ------------------------
if (isset($_POST['update'])) {
    $academic_term = $_POST['academic_term'];
    $orientation_title = $_POST['orientation_title'];
    $venue = $_POST['venue'];
    $event_date = $_POST['event_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $update_query = "
        UPDATE orientation_records 
        SET 
            academic_term = '$academic_term',
            orientation_title = '$orientation_title',
            venue = '$venue',
            event_date = '$event_date',
            start_time = '$start_time',
            end_time = '$end_time'
        WHERE id = $id
    ";

    if (mysqli_query($conn, $update_query)) {
        header("Location: ../orientationrec.php?updated=1");
        exit();
    } else {
        echo "Error updating event: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
</head>
<body>

<h2>Edit Orientation Event</h2>

<form method="POST">

    <label>Academic Term:</label><br>
    <input type="text" name="academic_term" value="<?php echo $event['academic_term']; ?>" required>
    <br><br>

    <label>Orientation Title:</label><br>
    <input type="text" name="orientation_title" value="<?php echo $event['orientation_title']; ?>" required>
    <br><br>

    <label>Venue:</label><br>
    <input type="text" name="venue" value="<?php echo $event['venue']; ?>" required>
    <br><br>

    <label>Date:</label><br>
    <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
    <br><br>

    <label>Start Time:</label><br>
    <input type="time" name="start_time" value="<?php echo $event['start_time']; ?>" required>
    <br><br>

    <label>End Time:</label><br>
    <input type="time" name="end_time" value="<?php echo $event['end_time']; ?>" required>
    <br><br>

    <button type="submit" name="update">Update Event</button>
</form>

</body>
</html>
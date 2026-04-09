<?php
include '../db_connect.php';

$query = "SELECT * FROM orientation_records ORDER BY event_date ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <title>Orientation Record</title>
</head>
<body>

    <?php include ('../includes/sidebar_student.php'); ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'EventDeleted') { ?>

    <p style="color: green; font-weight: bold;">Orientation event has been deleted successfully!</p>
<?php } ?>

    <h1>Internship Orientations</h1>
    <p>Shows the records of the  current and past orientation</p>
  <br>
    <table id="orientationTable" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Academic Term</th>
            <th>Orientation Title</th>
            <th>Venue</th>
            <th>Date</th>
            <th>Time</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            
            <td><?php echo $row['academic_term']; ?></td>

            <td><?php echo $row['orientation_title']; ?></td>

            <td><?php echo $row['venue']; ?></td>

            <!-- Display date as October 13, 2005 -->
            <td><?php echo date("F j, Y", strtotime($row['event_date'])); ?></td>

            <!-- Display time as 8:00 AM - 5:00 PM -->
            <td>
                <?php 
                    echo date("g:i A", strtotime($row['start_time'])) . " - " . 
                         date("g:i A", strtotime($row['end_time']));
                ?>
            </td>
            <td>
            </td>
        </tr>
        </div>
        <?php } ?>
    </tbody>
</table>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
$(document).ready(function () {
    $('#orientationTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[4, 'asc']], // sort by Date column
        columnDefs: [
            { orderable: false, targets: 6 } // disable sorting on Actions
        ]
    });
});
</script>
<iframe 
    src="http://127.0.0.1:5000"
    allowtransparency="true"
    style="
        width: 450px;
        height: 580px;
        position: fixed;
        bottom: 20px;
        right: 20px;
        border: none;
        background: transparent;
        z-index: 9999;">
</iframe>
</body>
</html>
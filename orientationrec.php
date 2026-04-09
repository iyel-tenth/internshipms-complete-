<?php
include 'db_connect.php';

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
    <?php include ('includes/sidebar_admin.php'); ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'EventDeleted') { ?>
    <p style="color: green; font-weight: bold;">Orientation event has been deleted successfully!</p>
<?php } ?>

    <h1>Internship Orientations</h1>
    <p>Manages the records of the  current and past orientation</p>
  <br>
  <form>
  <a href="companies_event/add_orient.php" class="btn-add">+ Add Orientation Schedule</a></form><br>
  <div class="table-container">
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
                <a href="orient_event/edit_event.php?id=<?php echo $row['id']; ?>">Publish</a> 

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

</body>
</html>
<?php
ini_set('session.gc_maxlifetime', 28800);
session_set_cookie_params(28800);
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$query = "SELECT student_id, attendance_date, am_timeIn, am_timeOut, pm_timeIn, pm_timeOut, total_hours
          FROM attendance
          WHERE student_id = ?
          ORDER BY attendance_date ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <title>Daily Time Record</title>

    <style>
    .top-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    .action-buttons button,
    .btn-add {
        padding: 10px 18px;
        border: none;
        background: #0A1D56;
        color: #ffffff;
        font-size: 14px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .action-buttons button:hover,
    .btn-add:hover {
        background: #112b85;
        transform: translateY(-2px);
    }

    .selected-row {
        background-color: #d6e4ff !important;
    }
    </style>
</head>
<body>

<?php include ('sidebar_student.php'); ?>

<h1>Daily Time Record</h1>
<p>Track your daily attendance and hours rendered</p>
<br>
<div class="top-controls">
    <a href="../companies_event/file_dtr.php" class="btn-add">+ Time In/Out</a>
    <a href="dtr_student_pdf.php" class="btn-add" target="_blank" style="background: #27ae60;">📄 Print DTR (PDF)</a>
</div>
<div class="table-container">
<table id="dtrTable" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Date</th>
            <th>AM Time In</th>
            <th>AM Time Out</th>
            <th>PM Time In</th>
            <th>PM Time Out</th>
            <th>Total Hours</th>
        </tr>
    </thead>

    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
            <td><?php echo htmlspecialchars($row['attendance_date']); ?></td>
            <td><?php echo htmlspecialchars($row['am_timeIn']); ?></td>
            <td><?php echo htmlspecialchars($row['am_timeOut']); ?></td>
            <td><?php echo htmlspecialchars($row['pm_timeIn']); ?></td>
            <td><?php echo htmlspecialchars($row['pm_timeOut']); ?></td>
            <td><?php echo htmlspecialchars($row['total_hours']); ?></td>
        </tr>
        <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="7" style="text-align:center; padding:20px;">
                No records found
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#dtrTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[1, 'asc']]
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
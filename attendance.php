<?php
// Include database if needed
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" type="text/css" href="css/attendance.css">
    
    <title>Attendance</title>
</head>
<body>

<?php include ('includes/sidebar.php'); ?>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <h1>Attendance</h1>
    <p>Enter the number of hours rendered</p>
    <br>

    <div class="table-container">
        <table id="attendanceTable" class="display" cellspacing="0" width="100%">
            <thead>
                <!-- First header row -->
                <tr>
                    <th rowspan="2">Name</th>
                    <th colspan="25">Number of Hours Rendered</th>
                    <th rowspan="2">Total Hours</th>
                </tr>
                <!-- Second header row -->
                <tr>
                    <?php for($d=1; $d<=25; $d++): ?>
                        <th>Day <?php echo $d; ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>LASTNAME, FIRSTNAME</td>

                    <!-- Hour cells (25 columns) -->
                    <?php for($h=1; $h<=25; $h++): ?>
                        <td><input type="number" class="hour" min="0" max="8"></td>
                    <?php endfor; ?>

                    <!-- Total -->
                    <td><input type="text" id="total" readonly></td>
                </tr>
            </tbody>
        </table>

        <button class="save-btn" onclick="saveAttendance()">Save</button>
    </div>

</div>

<!-- ===== JS ===== -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const hourInputs = document.querySelectorAll('.hour');
const totalField = document.getElementById('total');

hourInputs.forEach(input => {
    input.addEventListener('input', calculateTotal);
});

function calculateTotal() {
    let total = 0;
    hourInputs.forEach(input => {
        total += Number(input.value) || 0;
    });
    totalField.value = total;
}

function saveAttendance() {
    const total = document.getElementById('total').value;

    fetch('save_attendance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'total=' + total
    })
    .then(res => res.text())
    .then(msg => alert(msg));
}
</script>

</body>
</html>

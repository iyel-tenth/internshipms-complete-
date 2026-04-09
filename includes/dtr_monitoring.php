<?php
session_start();
include '../db_connect.php';

// Access control - only Admin and Instructor can view this
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Instructor'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch all students' DTR records
$query = "
    SELECT 
        a.student_id,
        su.first_name,
        su.last_name,
        su.email,
        a.attendance_date,
        a.am_timeIn,
        a.am_timeOut,
        a.pm_timeIn,
        a.pm_timeOut,
        a.total_hours
    FROM attendance a
    JOIN student_users su ON a.student_id = su.student_id
    ORDER BY a.attendance_date DESC, a.student_id ASC
";

$result = $conn->query($query);

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR Monitoring</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">

    <style>
    .monitoring-container {
        padding: 20px;
        background-color: #f5f5f5;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .monitoring-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .monitoring-header h2 {
        margin: 0;
        color: #0A1D56;
    }

    .filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    .filter-group input,
    .filter-group select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 0.95rem;
    }

    .table-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        background-color: #0A1D56;
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }

    tbody td {
        padding: 12px;
        border-bottom: 1px solid #eee;
    }

    tbody tr:hover {
        background-color: #f9f9f9;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-complete {
        background-color: #27ae60;
        color: white;
    }

    .status-partial {
        background-color: #f39c12;
        color: white;
    }

    .status-pending {
        background-color: #e74c3c;
        color: white;
    }

    .status-na {
        background-color: #95a5a6;
        color: white;
    }

    .hours-display {
        font-weight: 600;
        color: #0A1D56;
    }

    .time-display {
        font-family: monospace;
        font-size: 0.9rem;
    }

    .dataTables_wrapper {
        margin-top: 20px;
    }

    .export-btn {
        padding: 10px 18px;
        background: #0A1D56;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .export-btn:hover {
        background: #112b85;
        transform: translateY(-2px);
    }

    .stats-box {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #0A1D56;
    }

    .stat-label {
        color: #666;
        margin-top: 5px;
        font-size: 0.9rem;
    }
    
    /* ================= MODAL STYLES ================= */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        font-size: 1.5rem;
        font-weight: 600;
        color: #0A1D56;
        margin-bottom: 20px;
        border-bottom: 2px solid #0A1D56;
        padding-bottom: 10px;
    }

    .modal-body {
        margin: 20px 0;
    }

    .time-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }

    .time-item {
        background: #f5f5f5;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
    }

    .time-label {
        font-size: 0.85rem;
        color: #666;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .time-value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #0A1D56;
        font-family: monospace;
    }

    .time-empty {
        color: #95a5a6;
        font-style: italic;
    }

    .modal-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .modal-close:hover {
        color: #000;
    }

    .close-btn {
        background: #0A1D56;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        margin-top: 15px;
        width: 100%;
        transition: all 0.3s;
    }

    .close-btn:hover {
        background: #112b85;
    }

    .status-badge {
        cursor: pointer;
        transition: all 0.2s;
    }

    .status-badge:hover {
        opacity: 0.8;
        transform: scale(1.05);
    }
    /* ================================================= */
    </style>
</head>
<body>

<?php include_once 'sidebar_admin.php'; ?>

<div class="monitoring-container">
    <div class="monitoring-header">
        <h2>📊 DTR Monitoring Dashboard</h2>
        <div style="display: flex; gap: 10px;">
            <button class="export-btn" onclick="exportToCSV()">📥 Export to CSV</button>
            <button class="export-btn" onclick="openPrintModal()" style="background: #27ae60;">📄 Print DTR (PDF)</button>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="stats-box">
        <?php
        $total_records = mysqli_num_rows($result);
        $today = date('Y-m-d');
        
        // Count today's records
        $today_query = "SELECT COUNT(*) as count FROM attendance WHERE attendance_date = ?";
        $today_stmt = $conn->prepare($today_query);
        $today_stmt->bind_param("s", $today);
        $today_stmt->execute();
        $today_result = $today_stmt->get_result();
        $today_row = $today_result->fetch_assoc();
        $today_count = $today_row['count'];
        ?>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $total_records; ?></div>
            <div class="stat-label">Total DTR Records</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $today_count; ?></div>
            <div class="stat-label">Records Today</div>
        </div>

        <?php
        $all_students = "SELECT COUNT(DISTINCT student_id) as count FROM student_users";
        $all_result = $conn->query($all_students);
        $all_row = $all_result->fetch_assoc();
        $total_students = $all_row['count'];
        ?>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $total_students; ?></div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filters">
        <div class="filter-group">
            <label for="filterDate">Filter by Date:</label>
            <input type="date" id="filterDate" onchange="filterTable()">
        </div>
        <div class="filter-group">
            <label for="filterStudent">Filter by Student ID:</label>
            <input type="text" id="filterStudent" placeholder="Enter Student ID" onkeyup="filterTable()">
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container">
        <table id="dtrTable" class="display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>AM Status</th>
                    <th>PM Status</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Reset result pointer
                mysqli_data_seek($result, 0);
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $student_id = htmlspecialchars($row['student_id']);
                    $student_name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                    $email = htmlspecialchars($row['email']);
                    $date = htmlspecialchars($row['attendance_date']);
                    $total_hours = $row['total_hours'] ?? 0;

                    // Get time values
                    $am_timeIn = $row['am_timeIn'] ?? '';
                    $am_timeOut = $row['am_timeOut'] ?? '';
                    $pm_timeIn = $row['pm_timeIn'] ?? '';
                    $pm_timeOut = $row['pm_timeOut'] ?? '';

                    // Determine AM Status
                    $am_status = '';
                    $am_class = '';
                    if (!empty($row['am_timeIn']) && !empty($row['am_timeOut'])) {
                        $am_status = '✓ Complete';
                        $am_class = 'status-complete';
                    } elseif (!empty($row['am_timeIn']) && empty($row['am_timeOut'])) {
                        $am_status = '◐ In Progress';
                        $am_class = 'status-partial';
                    } else {
                        $am_status = '✗ Pending';
                        $am_class = 'status-pending';
                    }

                    // Determine PM Status
                    $pm_status = '';
                    $pm_class = '';
                    if (!empty($row['pm_timeIn']) && !empty($row['pm_timeOut'])) {
                        $pm_status = '✓ Complete';
                        $pm_class = 'status-complete';
                    } elseif (!empty($row['pm_timeIn']) && empty($row['pm_timeOut'])) {
                        $pm_status = '◐ In Progress';
                        $pm_class = 'status-partial';
                    } else {
                        $pm_status = '✗ Not Yet';
                        $pm_class = 'status-na';
                    }
                ?>
                <tr>
                    <td><?php echo $student_id; ?></td>
                    <td><?php echo $student_name; ?></td>
                    <td><?php echo $email; ?></td>
                    <td><?php echo $date; ?></td>
                    <td>
                        <span class="status-badge <?php echo $am_class; ?>" 
                              onclick="showTimeModal('AM', '<?php echo htmlspecialchars($student_name); ?>', '<?php echo htmlspecialchars($date); ?>', '<?php echo htmlspecialchars($am_timeIn); ?>', '<?php echo htmlspecialchars($am_timeOut); ?>')">
                            <?php echo $am_status; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $pm_class; ?>" 
                              onclick="showTimeModal('PM', '<?php echo htmlspecialchars($student_name); ?>', '<?php echo htmlspecialchars($date); ?>', '<?php echo htmlspecialchars($pm_timeIn); ?>', '<?php echo htmlspecialchars($pm_timeOut); ?>')">
                            <?php echo $pm_status; ?>
                        </span>
                    </td>
                    <td><span class="hours-display"><?php echo number_format($total_hours, 2); ?> hrs</span></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- TIME DETAILS MODAL -->
<div id="timeModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeTimeModal()">&times;</span>
        <div class="modal-header" id="modalTitle">AM Time Details</div>
        <div class="modal-body">
            <p><strong>Student:</strong> <span id="modalStudent"></span></p>
            <p><strong>Date:</strong> <span id="modalDate"></span></p>
            <div class="time-info">
                <div class="time-item">
                    <div class="time-label">Time In</div>
                    <div class="time-value" id="modalTimeIn">--</div>
                </div>
                <div class="time-item">
                    <div class="time-label">Time Out</div>
                    <div class="time-value" id="modalTimeOut">--</div>
                </div>
            </div>
        </div>
        <button class="close-btn" onclick="closeTimeModal()">Close</button>
    </div>
</div>

<!-- PRINT DTR MODAL -->
<div id="printModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <span class="modal-close" onclick="closePrintModal()">&times;</span>
        <div class="modal-header">Print DTR Report</div>
        <div class="modal-body">
            <div class="filter-group">
                <label for="printStartDate">Start Date:</label>
                <input type="date" id="printStartDate" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="filter-group">
                <label for="printEndDate">End Date:</label>
                <input type="date" id="printEndDate" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="filter-group">
                <label for="printStudentId">Filter by Student ID (Optional):</label>
                <input type="text" id="printStudentId" placeholder="Leave empty for all students">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button class="close-btn" style="flex: 1; background: #27ae60;" onclick="generatePrintPDF()">Generate PDF</button>
                <button class="close-btn" style="flex: 1; background: #e74c3c;" onclick="closePrintModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
    
function formatTime(timeString) {
    if (!timeString || timeString.trim() === '') {
        return '-- : --';
    }

    // If datetime format, extract only the time part
    if (timeString.includes(' ')) {
        timeString = timeString.split(' ')[1]; // Get HH:MM:SS
    }

    const parts = timeString.split(':');
    if (parts.length < 2) {
        return '-- : --';
    }

    let hours = parseInt(parts[0], 10);
    const minutes = parts[1];

    const period = hours >= 12 ? 'PM' : 'AM';

    hours = hours % 12;
    hours = hours ? hours : 12; // Convert 0 to 12

    return hours + ':' + minutes + ' ' + period;
}

function showTimeModal(period, student, date, timeIn, timeOut) {
    const modal = document.getElementById('timeModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalStudent = document.getElementById('modalStudent');
    const modalDate = document.getElementById('modalDate');
    const modalTimeIn = document.getElementById('modalTimeIn');
    const modalTimeOut = document.getElementById('modalTimeOut');

    // Set modal title
    modalTitle.textContent = period + ' Time Details';

    // Set modal content
    modalStudent.textContent = student;
    modalDate.textContent = date;

    // Format times using custom formatter
    const formattedTimeIn = formatTime(timeIn);
    const formattedTimeOut = formatTime(timeOut);
    
    modalTimeIn.textContent = formattedTimeIn;
    modalTimeOut.textContent = formattedTimeOut;
    
    // Add time-empty class if times are missing
    if (!timeIn || timeIn.trim() === '') {
        modalTimeIn.classList.add('time-empty');
    } else {
        modalTimeIn.classList.remove('time-empty');
    }
    
    if (!timeOut || timeOut.trim() === '') {
        modalTimeOut.classList.add('time-empty');
    } else {
        modalTimeOut.classList.remove('time-empty');
    }

    // Show modal
    modal.style.display = 'block';
}

function closeTimeModal() {
    const modal = document.getElementById('timeModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('timeModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<script>
$(document).ready(function () {
    $('#dtrTable').DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[3, 'desc'], [0, 'asc']], // Sort by date descending, then by student ID
        columnDefs: [
            { orderable: false, targets: [4, 5] } // Disable sorting on status columns
        ]
    });
});

function filterTable() {
    const dateFilter = document.getElementById('filterDate').value;
    const studentFilter = document.getElementById('filterStudent').value.toUpperCase();

    const table = document.getElementById('dtrTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        const studentId = cells[0].textContent;
        const date = cells[3].textContent;

        let show = true;

        if (dateFilter && date !== dateFilter) {
            show = false;
        }

        if (studentFilter && !studentId.includes(studentFilter)) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    }
}

function exportToCSV() {
    const table = document.getElementById('dtrTable');
    const rows = table.getElementsByTagName('tr');
    let csv = [];

    // Add headers
    const headers = [];
    const headerCells = rows[0].getElementsByTagName('th');
    for (let cell of headerCells) {
        headers.push('"' + cell.textContent.replace(/"/g, '""') + '"');
    }
    csv.push(headers.join(','));

    // Add data rows
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].style.display === 'none') continue;

        const rowData = [];
        const cells = rows[i].getElementsByTagName('td');
        for (let j = 0; j < cells.length; j++) {
            let text = cells[j].textContent.trim();
            rowData.push('"' + text.replace(/"/g, '""') + '"');
        }
        csv.push(rowData.join(','));
    }

    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'DTR_Monitoring_' + new Date().toISOString().split('T')[0] + '.csv';
    link.click();
}

// Print Modal Functions
function openPrintModal() {
    document.getElementById('printModal').style.display = 'block';
}

function closePrintModal() {
    document.getElementById('printModal').style.display = 'none';
}

function generatePrintPDF() {
    const startDate = document.getElementById('printStartDate').value;
    const endDate = document.getElementById('printEndDate').value;
    const studentId = document.getElementById('printStudentId').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }
    
    let url = 'dtr_monitoring_pdf.php?start_date=' + startDate + '&end_date=' + endDate;
    if (studentId) {
        url += '&student_id=' + encodeURIComponent(studentId);
    }
    
    window.open(url, '_blank');
    closePrintModal();
}

// Close print modal when clicking outside of it
window.addEventListener('click', function(event) {
    const printModal = document.getElementById('printModal');
    const timeModal = document.getElementById('timeModal');
    
    if (event.target == printModal) {
        printModal.style.display = 'none';
    }
    if (event.target == timeModal) {
        timeModal.style.display = 'none';
    }
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

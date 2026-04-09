<?php
session_start();
include '../db_connect.php';

// Access control - only Admin and Instructor can view this
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Instructor'])) {
    header("Location: ../login.php");
    exit();
}

// Get date range filters if provided
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$filter_student = isset($_GET['student_id']) ? $_GET['student_id'] : '';

// Build query with optional filters
$query = "
    SELECT 
        a.student_id,
        su.first_name,
        su.last_name,
        a.attendance_date,
        a.am_timeIn,
        a.am_timeOut,
        a.pm_timeIn,
        a.pm_timeOut,
        a.total_hours
    FROM attendance a
    JOIN student_users su ON a.student_id = su.student_id
    WHERE a.attendance_date BETWEEN ? AND ?
";

$params = array($start_date, $end_date);
$types = 'ss';

if (!empty($filter_student)) {
    $query .= " AND a.student_id = ?";
    $params[] = $filter_student;
    $types .= 's';
}

$query .= " ORDER BY a.student_id ASC, a.attendance_date ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$total_hours = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR Monitoring Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #fff;
            color: #333;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }

        .print-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0.5in;
            background: white;
            font-size: 9pt;
            line-height: 1.4;
        }

        .dtr-header {
            text-align: center;
            border-bottom: 3px solid #0A1D56;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .dtr-header h1 {
            font-size: 14pt;
            margin-bottom: 4px;
            color: #0A1D56;
        }

        .dtr-header p {
            font-size: 8pt;
            color: #666;
            margin: 2px 0;
        }

        .report-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 12px;
            font-size: 8pt;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
        }

        .info-label {
            font-weight: bold;
            color: #0A1D56;
        }

        .info-value {
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th {
            background-color: #0A1D56;
            color: white;
            padding: 5px 3px;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #333;
        }

        td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            font-size: 8pt;
            text-align: center;
        }

        tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .total-row {
            background-color: #e8e8e8;
            font-weight: bold;
            border-top: 2px solid #0A1D56;
        }

        .total-row td {
            padding: 6px 3px;
            border: 1px solid #333;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            background: #0A1D56;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11pt;
            font-weight: bold;
        }

        .btn:hover {
            background: #112b85;
        }

        .btn-print {
            background: #27ae60;
        }

        .btn-print:hover {
            background: #229954;
        }

        .no-records {
            text-align: center;
            padding: 40px;
            font-style: italic;
            color: #999;
        }

        .generated-date {
            text-align: right;
            font-size: 7pt;
            color: #999;
            margin-top: 10px;
        }

        .page-break {
            page-break-after: always;
            margin-bottom: 20px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 0.5in;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="btn" onclick="history.back()">← Back</button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="dtr-header">
            <h1>DTR MONITORING REPORT - ALL STUDENTS</h1>
            <p>Internship Management System</p>
        </div>

        <!-- Report Information -->
        <div class="report-info">
            <div class="info-item">
                <span class="info-label">Date Range:</span>
                <span class="info-value"><?php echo date('m/d/Y', strtotime($start_date)) . ' to ' . date('m/d/Y', strtotime($end_date)); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Generated:</span>
                <span class="info-value"><?php echo date('m/d/Y H:i A'); ?></span>
            </div>
            <?php if (!empty($filter_student)): ?>
            <div class="info-item">
                <span class="info-label">Student ID Filter:</span>
                <span class="info-value"><?php echo htmlspecialchars($filter_student); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- DTR Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Student ID</th>
                    <th style="width: 10%;">First Name</th>
                    <th style="width: 10%;">Last Name</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 8%;">AM In</th>
                    <th style="width: 8%;">AM Out</th>
                    <th style="width: 8%;">PM In</th>
                    <th style="width: 8%;">PM Out</th>
                    <th style="width: 12%;">Remarks</th>
                    <th style="width: 8%;">Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $am_in = !empty($row['am_timeIn']) ? substr($row['am_timeIn'], 0, 5) : '--:--';
                        $am_out = !empty($row['am_timeOut']) ? substr($row['am_timeOut'], 0, 5) : '--:--';
                        $pm_in = !empty($row['pm_timeIn']) ? substr($row['pm_timeIn'], 0, 5) : '--:--';
                        $pm_out = !empty($row['pm_timeOut']) ? substr($row['pm_timeOut'], 0, 5) : '--:--';
                        
                        $date = date('m/d/Y', strtotime($row['attendance_date']));
                        $hours = !empty($row['total_hours']) ? number_format($row['total_hours'], 2) : '0.00';
                        $total_hours += floatval($hours);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td><?php echo substr(htmlspecialchars($row['first_name']), 0, 12); ?></td>
                            <td><?php echo substr(htmlspecialchars($row['last_name']), 0, 12); ?></td>
                            <td><?php echo $date; ?></td>
                            <td><?php echo $am_in; ?></td>
                            <td><?php echo $am_out; ?></td>
                            <td><?php echo $pm_in; ?></td>
                            <td><?php echo $pm_out; ?></td>
                            <td></td>
                            <td><?php echo $hours; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="10" class="no-records">No DTR records found for the selected criteria</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="9" style="text-align: right; padding-right: 8px;">TOTAL HOURS RENDERED:</td>
                    <td><?php echo number_format($total_hours, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="generated-date">
            Report generated on <?php echo date('F d, Y \a\t H:i A'); ?> by <?php echo htmlspecialchars($_SESSION['role']); ?>
        </div>
    </div>
</body>
</html>


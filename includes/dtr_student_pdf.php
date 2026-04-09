<?php
session_start();
include '../db_connect.php';

// Access control - only Students can view their own DTR
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student info
$student_query = "SELECT first_name, last_name, email FROM student_users WHERE student_id = ?";
$student_stmt = $conn->prepare($student_query);
$student_stmt->bind_param("s", $student_id);
$student_stmt->execute();
$student_result = $student_stmt->get_result();
$student_info = $student_result->fetch_assoc();

// Fetch DTR records
$query = "SELECT student_id, attendance_date, am_timeIn, am_timeOut, pm_timeIn, pm_timeOut, total_hours
          FROM attendance
          WHERE student_id = ?
          ORDER BY attendance_date ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$total_hours = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTR - <?php echo $student_id; ?></title>
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
            .page {
                page-break-after: always;
            }
        }

        .print-container {
            max-width: 8.5in;
            height: 11in;
            margin: 0 auto;
            padding: 0.5in;
            background: white;
            font-size: 10pt;
        }

        .dtr-header {
            text-align: center;
            border-bottom: 3px solid #0A1D56;
            padding-bottom: 12px;
            margin-bottom: 8px;
        }

        .dtr-header h1 {
            font-size: 16pt;
            margin-bottom: 4px;
            color: #0A1D56;
        }

        .dtr-header p {
            font-size: 9pt;
            color: #666;
            margin: 2px 0;
        }

        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 9pt;
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
            padding: 6px;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #333;
        }

        td {
            padding: 5px 6px;
            border: 1px solid #ddd;
            font-size: 9pt;
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
            padding: 8px 6px;
            border: 1px solid #333;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
            font-size: 9pt;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 4px;
            margin-top: 30px;
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
            font-size: 8pt;
            color: #999;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="action-buttons no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="btn" onclick="window.location.href='dtr_student.php'">← Back</button>
    </div>

    <div class="print-container page">
        <!-- Header -->
        <div class="dtr-header">
            <h1>DAILY TIME RECORD (DTR)</h1>
            <p>Internship Management System</p>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <div class="info-item">
                <span class="info-label">Student Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Student ID:</span>
                <span class="info-value"><?php echo htmlspecialchars($student_id); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($student_info['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Generated:</span>
                <span class="info-value"><?php echo date('m/d/Y H:i A'); ?></span>
            </div>
        </div>

        <!-- DTR Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 11%;">AM In</th>
                    <th style="width: 11%;">AM Out</th>
                    <th style="width: 11%;">PM In</th>
                    <th style="width: 11%;">PM Out</th>
                    <th style="width: 16%;">Remarks</th>
                    <th style="width: 10%;">Hours</th>
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
                        <td colspan="7" class="no-records">No DTR records found</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" style="text-align: right; padding-right: 10px;">TOTAL HOURS RENDERED:</td>
                    <td><?php echo number_format($total_hours, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div>
                <div class="signature-line">Student Signature</div>
            </div>
            <div>
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <div class="generated-date">
            Document generated on <?php echo date('F d, Y \a\t H:i A'); ?>
        </div>
    </div>
</body>
</html>


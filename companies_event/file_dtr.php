<?php
session_start();
// Set timezone to Philippine Standard Time
date_default_timezone_set('Asia/Manila');

require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = $_SESSION['user_id'];
    $current_datetime = date('Y-m-d H:i:s');
    $current_date = date('Y-m-d');
    $current_period = (date('A') === 'AM') ? 'AM' : 'PM';

    // Check if record exists today
    $check = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND attendance_date = ?");
    $check->bind_param("ss", $student_id, $current_date);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows == 0) {

        // FIRST RECORD TODAY
        if ($current_period === 'AM') {
            $insert = $conn->prepare("
                INSERT INTO attendance 
                (student_id, attendance_date, am_timeIn) 
                VALUES (?, ?, ?)
            ");
        } else {
            $insert = $conn->prepare("
                INSERT INTO attendance 
                (student_id, attendance_date, pm_timeIn) 
                VALUES (?, ?, ?)
            ");
        }

        $insert->bind_param("sss", $student_id, $current_date, $current_datetime);
        $insert->execute();
        $insert->close();

    } else {

        $row = $result->fetch_assoc();

        if ($current_period === 'AM') {

            // AM TIME OUT
            if (!empty($row['am_timeIn']) && empty($row['am_timeOut'])) {

                $update = $conn->prepare("
                    UPDATE attendance 
                    SET am_timeOut = ?
                    WHERE student_id = ? AND attendance_date = ?
                ");
                $update->bind_param("sss", $current_datetime, $student_id, $current_date);
                $update->execute();
                $update->close();
            }

        } else {

            // PM TIME IN
            if (empty($row['pm_timeIn'])) {

                $update = $conn->prepare("
                    UPDATE attendance 
                    SET pm_timeIn = ?
                    WHERE student_id = ? AND attendance_date = ?
                ");
                $update->bind_param("sss", $current_datetime, $student_id, $current_date);
                $update->execute();
                $update->close();

            }
            // PM TIME OUT
            elseif (!empty($row['pm_timeIn']) && empty($row['pm_timeOut'])) {

                $update = $conn->prepare("
                    UPDATE attendance 
                    SET pm_timeOut = ?
                    WHERE student_id = ? AND attendance_date = ?
                ");
                $update->bind_param("sss", $current_datetime, $student_id, $current_date);
                $update->execute();
                $update->close();
            }
            else {
                echo "<script>alert('PM attendance already completed for today.');</script>";
            }
        }

        // 🔥 ALWAYS RECALCULATE TOTAL HOURS AFTER ANY UPDATE
        $recalculate = $conn->prepare("
            UPDATE attendance
            SET total_hours = (
                IF(am_timeIn IS NOT NULL AND am_timeOut IS NOT NULL,
                    TIMESTAMPDIFF(MINUTE, am_timeIn, am_timeOut),
                    0
                )
                +
                IF(pm_timeIn IS NOT NULL AND pm_timeOut IS NOT NULL,
                    TIMESTAMPDIFF(MINUTE, pm_timeIn, pm_timeOut),
                    0
                )
            ) / 60
            WHERE student_id = ? AND attendance_date = ?
        ");
        $recalculate->bind_param("ss", $student_id, $current_date);
        $recalculate->execute();
        $recalculate->close();
    }

    $check->close();
    
    // Redirect to DTR student page after successful filing
    header("Location: ../includes/dtr_student.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File DTR</title>
<link rel="stylesheet" href="../css/addevent.css">

<style>
/* ================= CLOCK UI ENHANCEMENT ================= */
.clock-container {
    display: flex;
    justify-content: center;
    margin: 25px 0;
}

.clock-box {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: #ffffff;
    padding: 20px 40px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    text-align: center;
    min-width: 280px;
}

.clock-date {
    font-size: 1rem;
    letter-spacing: 1px;
    opacity: 0.8;
}

.clock-time {
    font-size: 2.5rem;
    font-weight: bold;
    margin-top: 5px;
    letter-spacing: 2px;
}

.clock-label {
    font-size: 0.9rem;
    margin-top: 8px;
    color: #1abc9c;
    text-transform: uppercase;
    letter-spacing: 1px;
}
/* ======================================================== */

/* ================= NOTIFIER STYLES ================= */
.notifier-container {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

.notifier {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: #ffffff;
    padding: 15px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.notifier::before {
    content: "✓";
    font-size: 1.3rem;
    font-weight: bold;
}

/* ================= WARNING NOTIFIER STYLES ================= */
.notifier-warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    color: #ffffff;
    padding: 15px 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.notifier-warning::before {
    content: "⚠";
    font-size: 1.3rem;
    font-weight: bold;
}
/* ========================================================== */

/* ================= BUTTON STYLES ================= */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #ccc !important;
}

button:disabled:hover {
    transform: none;
}
/* ================================================= */
</style>

<script>
function updateClock() {
    const now = new Date();

    // Convert to Philippine Standard Time (UTC+8)
    const options = { timeZone: 'Asia/Manila', hour12: true };
    const timeString = now.toLocaleTimeString('en-US', options);
    const dateString = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        timeZone: 'Asia/Manila'
    });

    document.getElementById('clock-date').innerText = dateString;
    document.getElementById('clock-time').innerText = timeString;
}

setInterval(updateClock, 1000);
window.onload = updateClock;
</script>

</head>
<body>

<?php include_once __DIR__ . '/../includes/sidebar_student.php'; ?>

<form method="POST" onsubmit="return confirm('Are you sure you want to file this DTR?')">
    <h2>File Daily Time Record</h2>

    <!-- <label>Student ID:</label> -->

    <!-- Enhanced Clock UI -->
    <div class="clock-container">
        <div class="clock-box">
            <div id="clock-date" class="clock-date">Loading date...</div>
            <div id="clock-time" class="clock-time">Loading time...</div>
            <div class="clock-label">Current Time</div>
        </div>
    </div>

    <?php
    // Check if AM filing is complete for today
    $current_date = date('Y-m-d');
    $current_period = (date('A') === 'AM') ? 'AM' : 'PM';
    
    $check_am = $conn->prepare("SELECT am_timeIn, am_timeOut FROM attendance WHERE student_id = ? AND attendance_date = ?");
    $check_am->bind_param("ss", $_SESSION['user_id'], $current_date);
    $check_am->execute();
    $result_am = $check_am->get_result();
    
    $am_complete = false;
    $button_disabled = '';
    
    if ($result_am->num_rows > 0) {
        $row_am = $result_am->fetch_assoc();
        
        // Check if AM is complete
        if (!empty($row_am['am_timeIn']) && !empty($row_am['am_timeOut'])) {
            $am_complete = true;
            
            // Show warning if AM is complete but it's still AM period
            if ($current_period === 'AM') {
                echo '<div class="notifier-container">
                        <div class="notifier-warning">
                            Your AM filing is already complete - Please wait until PM to file again
                        </div>
                      </div>';
                $button_disabled = 'disabled';
            } else {
                // Show success message during PM
                echo '<div class="notifier-container">
                        <div class="notifier">
                            AM Filing Complete - Your morning attendance has been recorded
                        </div>
                      </div>';
            }
        }
    }
    $check_am->close();
    ?>

    <button type="submit" class="btn btn-success" <?php echo $button_disabled; ?>>Time In/Out</button>
    <a href="../includes/dtr_student.php" class="btn btn-secondary">Cancel</a>
</form>

</body>
</html>
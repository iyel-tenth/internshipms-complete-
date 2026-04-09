<?php
ini_set('session.gc_maxlifetime', 28800);
session_set_cookie_params(28800);
session_start();
include(__DIR__ . '/../db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM student_users WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$student_name = $student['first_name'] . " " . $student['last_name'];
$student_agency_name = $student['agency_name'] ?? null;
$student_agency_status = $student['agency_status'] ?? null;

// Fetch MOA tracking data linked to student's agency
$moa_tracking = null;
if ($student_agency_name) {
    // Use flexible matching: check if student agency contains MOA agency OR MOA agency contains student agency
    $search_term1 = '%' . $student_agency_name . '%';
    $search_term2 = '%' . $student_agency_name . '%';
    $moa_query = $conn->prepare("SELECT id, agency_name, step1, step2, step3, step4, step5, step6, step7 FROM moa_tracking WHERE agency_name LIKE ? OR ? LIKE CONCAT('%', agency_name, '%') LIMIT 1");
    $moa_query->bind_param("ss", $search_term1, $student_agency_name);
    $moa_query->execute();
    $moa_result = $moa_query->get_result();
    if ($moa_result->num_rows > 0) {
        $moa_tracking = $moa_result->fetch_assoc();
    }
    $moa_query->close();
}

// Fetch agency slots from database
$slots_query = "SELECT agency, address, slots, starting_date, ending_date FROM agency_slots ORDER BY starting_date DESC";
$slots_result = $conn->query($slots_query);
$agency_slots = [];
if ($slots_result) {
    while ($slot = $slots_result->fetch_assoc()) {
        $agency_slots[] = $slot;
    }
}

// Fetch orientation records from database
$orient_query = "SELECT academic_term, orientation_title, venue, event_date, start_time, end_time FROM orientation_records ORDER BY event_date DESC";
$orient_result = $conn->query($orient_query);
$orientation_records = [];
if ($orient_result) {
    while ($orient = $orient_result->fetch_assoc()) {
        $orientation_records[] = $orient;
    }
}

// Fetch DTR (attendance) hours for current student
$dtr_query = "SELECT COALESCE(SUM(total_hours), 0) as total_hours FROM attendance WHERE student_id = ?";
$dtr_stmt = $conn->prepare($dtr_query);
$dtr_stmt->bind_param("s", $student_id);
$dtr_stmt->execute();
$dtr_result = $dtr_stmt->get_result();
$dtr_data = $dtr_result->fetch_assoc();
$total_ojt_hours = $dtr_data['total_hours'] ?? 0;
$dtr_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Your CSS -->

    <link rel="stylesheet" href="../css/dashboard.css">

    <style>
     /* ================= PAGE BACKGROUND ================= */
    body {
        background-color: #c9c9c9;
    }

    .main-content {
        background-color: #c9c9c9;
    }

     /* ================= DASHBOARD CONTAINER ================= */
    .dashboard-container {
        padding: 2000px;
        border-radius: 10px;
        margin-bottom: 20px;
        background-color: #ffffff;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .dashboard-header h2 {
        margin: 0;
        color: #ffffff;
        font-size: 1.8rem;
    }

    .dashboard-header p {
        margin: 5px 0 0 0;
        color: #ffffff;
    }

    /* ================= NOTIFICATION SYSTEM ================= */
    .notification-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-top: 20px;
    }

    .notification {
        background: white;
        border-left: 4px solid #e69b00;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 12px;
        cursor: pointer;
    }

    .notification:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }

    .notification-icon {
        font-size: 1.5rem;
    }

    .notification.info {
        border-left-color: #ffc756;
    }

    .notification.info .notification-icon {
        color: #ffc756;
    }

    .notification.success {
        border-left-color: #27ae60;
    }

    .notification.success .notification-icon {
        color: #27ae60;
    }

    .notification.warning {
        border-left-color: #f39c12;
    }

    .notification.warning .notification-icon {
        color: #f39c12;
    }

    .notification-title {
        font-weight: 600;
        color: #e69b00;
        margin: 0;
        font-size: 1.1rem;
    }

    .notification-message {
        color: #666;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .notification-info {
        background: #f9f9f9;
        padding: 12px;
        border-radius: 6px;
        font-size: 0.9rem;
        color: #333;
    }

    .notification-info strong {
        color: #e69b00;
    }

    .notification-date {
        font-size: 0.85rem;
        color: #999;
        margin-top: 8px;
    }

    /* Dashboard card grid */
    .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .card {
        background: #ffffff;
        border: 1px solid rgba(10, 29, 86, 0.08);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        min-height: 190px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.12);
    }

    .card i {
        font-size: 1.4rem;
        color: #e69b00;
        margin-bottom: 18px;
    }

    .card h3 {
        margin: 0 0 12px;
        color: #e69b00;
        font-size: 1.15rem;
    }

    .card p {
        margin: 0;
        color: #555;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .card strong {
        color: #e69b00;
    }

    /* Application Status Card */
    .application-status-card {
        background: white;
        border-left: 5px solid #e69b00;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .app-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .app-header h4 {
        margin: 0;
        color: #e69b00;
        font-size: 1.2rem;
    }

    .app-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }

    .app-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .app-approved {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .app-rejected {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .app-message {
        color: #666;
        margin: 0;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .notification-container {
            grid-template-columns: 1fr;
        }

        .app-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
    /* ======================================================== */
    </style>

</head>
<body id="body-pd">

<!-- NOTIFICATION CONTAINER -->
<div id="notificationContainer" class="notification-container"></div>

<?php include(__DIR__ . '/../includes/sidebar_student.php'); ?>

<div class="main-content">

    <div class="welcome">
        Welcome, <strong><?php echo htmlspecialchars($student_name); ?></strong> 👋
    </div>

    <div class="card-container">

        <!-- <div class="card">
            <i class="fas fa-briefcase"></i>
            <h3>Internship Application</h3>
            <p>Status: Pending</p>
        </div> -->

        <div class="card" id="requirementsCard">
            <i class="fas fa-file-alt"></i>
            <h3>Requirements</h3>
            <p id="requirementsText">3 of 5 Submitted</p>
        </div>

        <div class="card">
            <i class="fas fa-star"></i>
            <h3>Evaluation</h3>
            <p>Not Yet Evaluated</p>
        </div>

        <div class="card" id="ojtHoursCard">
            <i class="fas fa-clock"></i>
            <h3>OJT Hours</h3>
            <p id="ojtHoursText">0 / 200 Hours</p>
        </div>

    </div>

    <?php if ($student_agency_name && $student_agency_status) { ?>
    <div class="updates">
        <h3>🏢 Your Application Status</h3>
        <div class="application-status-card" style="<?php echo ($student_agency_status === 'rejected') ? 'border-left: 5px solid #dc3545;' : ''; ?>">
            <div class="app-header">
                <h4><?php echo htmlspecialchars($student_agency_name); ?></h4>
                <span class="app-badge app-<?php echo $student_agency_status; ?>">
                    <?php 
                    if ($student_agency_status === 'pending') {
                        echo '⏳ Pending';
                    } elseif ($student_agency_status === 'approved') {
                        echo '✅ Approved';
                    } elseif ($student_agency_status === 'rejected') {
                        echo '❌ Rejected';
                    }
                    ?>
                </span>
            </div>
            <p class="app-message">
                <?php 
                if ($student_agency_status === 'pending') {
                    echo 'Your application is being reviewed by the administrator. Please check back later.';
                } elseif ($student_agency_status === 'approved') {
                    echo 'Congratulations! Your application has been approved. You may now proceed with your internship.';
                } elseif ($student_agency_status === 'rejected') {
                    echo 'Your application has been rejected. You can apply for another agency below.';
                    if (!empty($student['rejection_reason'])) {
                        echo '<br><br><strong style="color: #dc3545;">Reason for rejection:</strong><br><div style="background-color: #fff5f5; padding: 12px; border-radius: 6px; margin-top: 8px; font-style: italic; color: #555; border-left: 4px solid #dc3545;">' . htmlspecialchars($student['rejection_reason']) . '</div>';
                    }
                }
                ?>
            </p>
        </div>
    </div>
    <?php } ?>

    <div class="updates">
        <h3>📋 Available Agency Slots</h3>
        <div id="slotsContainer" class="notification-container"></div>
    </div>

    <div class="updates">
        <h3>📅 Latest Orientation Activities</h3>
        <div id="orientationContainer" class="notification-container"></div>
    </div>

</div>

<script>
// Notification system function
function showNotification(message, type = 'info') {
    // Create notification element
    const notif = document.createElement('div');
    notif.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
        z-index: 9999;
        animation: slideIn 0.3s ease-in-out;
        max-width: 400px;
        font-weight: 500;
    `;
    notif.textContent = message;
    document.body.appendChild(notif);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        notif.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => notif.remove(), 300);
    }, 4000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Agency slots data from PHP
const agencySlots = <?php echo json_encode($agency_slots); ?>;

// Orientation records data from PHP
const orientationRecords = <?php echo json_encode($orientation_records); ?>;

// MOA Tracking data from PHP (linked by agency_name)
const moaTracking = <?php echo json_encode($moa_tracking); ?>;

// Student application status
const studentAgencyName = <?php echo json_encode($student_agency_name); ?>;
const studentAgencyStatus = <?php echo json_encode($student_agency_status); ?>;

// DTR/OJT Hours data from PHP
const totalOJTHours = <?php echo json_encode((float)$total_ojt_hours); ?>;
const targetOJTHours = 200;

// Display agency slots as notifications
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('slotsContainer');
    
    // Check if student has active application (pending or approved)
    // Rejected students CAN reapply
    const hasActiveApplication = studentAgencyName && (studentAgencyStatus === 'pending' || studentAgencyStatus === 'approved');
    const isRejected = studentAgencyStatus === 'rejected';
    
    if (agencySlots.length === 0) {
        container.innerHTML = '<p style="color: #999; padding: 20px; text-align: center;">No available slots at the moment.</p>';
        return;
    }

    agencySlots.forEach((slot, index) => {
        const startDate = new Date(slot.starting_date).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
        const endDate = new Date(slot.ending_date).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });

        const notification = document.createElement('div');
        notification.className = 'notification info';
        notification.style.animationDelay = (index * 0.1) + 's';
        
        let statusMessage = 'Click to apply for this internship slot';
        if (hasActiveApplication) {
            statusMessage = '📌 View only - you have an active application';
        } else if (isRejected) {
            statusMessage = '🔄 Rejected previously - you can apply to other agencies';
        }
        
        notification.innerHTML = `
            <div style="display: flex; gap: 12px;">
                <div class="notification-icon">🏢</div>
                <div style="flex: 1;">
                    <p class="notification-title">${slot.agency}</p>
                    <p class="notification-message">${slot.address}</p>
                </div>
            </div>
            <div class="notification-info">
                <div><strong>Available Slots:</strong> ${slot.slots}</div>
                <div><strong>Period:</strong> ${startDate} - ${endDate}</div>
            </div>
            <div class="notification-date">${statusMessage}</div>
        `;

        // Only add click listener if student doesn't have active application
        if (!hasActiveApplication) {
            notification.addEventListener('click', function() {
                if (confirm(`Apply for internship at ${slot.agency}?`)) {
                    const formData = new FormData();
                    formData.append('agency', slot.agency);

                    fetch('../phpbackend/apply-agency.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success notification
                            showNotification('✅ ' + data.message, 'success');
                            // Disable the clicked notification
                            notification.style.opacity = '0.6';
                            notification.style.pointerEvents = 'none';
                            notification.style.cursor = 'not-allowed';
                        } else {
                            showNotification('❌ ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('❌ Error submitting application. Please try again.', 'error');
                    });
                }
            });
            notification.style.cursor = 'pointer';
        } else {
            notification.style.cursor = 'not-allowed';
            notification.style.opacity = '0.7';
        }
        
        container.appendChild(notification);
    });

    // Orientation records display
    const orientContainer = document.getElementById('orientationContainer');
    orientationRecords.forEach((record, index) => {
        const eventDate = new Date(record.event_date).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });

        // Check if the orientation is in the past
        const eventEndDateTime = new Date(`${record.event_date}T${record.end_time}`);
        const now = new Date();
        if (eventEndDateTime < now) {
            return; // Skip past orientations
        }

        const notification = document.createElement('div');
        notification.className = 'notification info';
        notification.style.animationDelay = (index * 0.1) + 's';
        
        notification.innerHTML = `
            <div style="display: flex; gap: 12px;">
                <div class="notification-icon">🎓</div>
                <div style="flex: 1;">
                    <p class="notification-title">${record.orientation_title}</p>
                    <p class="notification-message">${record.venue}</p>
                </div>
            </div>
            <div class="notification-info">
                <div><strong>Academic Term:</strong> ${record.academic_term}</div>
                <div><strong>Date:</strong> ${eventDate}</div>
                <div><strong>Time:</strong> ${record.start_time} - ${record.end_time}</div>
            </div>
        `;

        orientContainer.appendChild(notification);
    });

    // MOA Tracking - Update Requirements Card
    if (studentAgencyName) {
        const requirementsText = document.getElementById('requirementsText');
        if (requirementsText) {
            if (moaTracking) {
                // If MOA data exists, show progress
                const steps = ['step1', 'step2', 'step3', 'step4', 'step5', 'step6', 'step7'];
                const completedSteps = steps.filter(step => moaTracking[step] == 1).length;
                
                requirementsText.innerHTML = `<strong>${moaTracking['agency_name']}</strong><br>MOA Progress: ${completedSteps} of 7 Steps Completed`;
                requirementsText.style.fontSize = '0.9rem';
                requirementsText.style.lineHeight = '1.5';
                
                // Optional: Add a tooltip or click handler to show details
                const requirementsCard = document.getElementById('requirementsCard');
                if (requirementsCard) {
                    requirementsCard.style.cursor = 'pointer';
                    requirementsCard.title = `${completedSteps === 7 ? '✅ MOA Completed!' : 'View MOA Progress'}`;
                    
                    requirementsCard.addEventListener('click', function() {
                        alert(`MOA Tracking for ${moaTracking['agency_name']}\n\nCompleted Steps: ${completedSteps}/7\n\nStep 1: ${moaTracking['step1'] == 1 ? '✅' : '⏳'}\nStep 2: ${moaTracking['step2'] == 1 ? '✅' : '⏳'}\nStep 3: ${moaTracking['step3'] == 1 ? '✅' : '⏳'}\nStep 4: ${moaTracking['step4'] == 1 ? '✅' : '⏳'}\nStep 5: ${moaTracking['step5'] == 1 ? '✅' : '⏳'}\nStep 6: ${moaTracking['step6'] == 1 ? '✅' : '⏳'}\nStep 7: ${moaTracking['step7'] == 1 ? '✅' : '⏳'}`);
                    });
                }
            } else {
                // If no MOA data exists yet, show notice
                requirementsText.innerHTML = `<strong>${studentAgencyName}</strong><br><span style="color: #ff6b6b; font-weight: 600;">⚠️ No MOA Record Yet</span><br><span style="font-size: 0.85rem; color: #999;">MOA will be created once approved.</span>`;
                requirementsText.style.fontSize = '0.9rem';
                requirementsText.style.lineHeight = '1.6';
                
                const requirementsCard = document.getElementById('requirementsCard');
                if (requirementsCard) {
                    requirementsCard.style.cursor = 'default';
                    requirementsCard.title = 'Waiting for MOA to be created';
                }
            }
        }
    } else {
        // If no agency assigned yet
        const requirementsText = document.getElementById('requirementsText');
        const requirementsCard = document.getElementById('requirementsCard');
        if (requirementsText) {
            requirementsText.innerHTML = `<span style="color: #9b59b6; font-weight: 600;">📝 Awaiting Agency Assignment</span><br><span style="font-size: 0.85rem; color: #999;">Apply for an internship slot to get started.</span>`;
            requirementsText.style.fontSize = '0.9rem';
            requirementsText.style.lineHeight = '1.6';
        }
        if (requirementsCard) {
            requirementsCard.style.cursor = 'default';
            requirementsCard.title = 'Complete your internship application';
        }
    }

    // OJT Hours - Update from DTR (Attendance)
    const ojtHoursText = document.getElementById('ojtHoursText');
    const ojtHoursCard = document.getElementById('ojtHoursCard');
    if (ojtHoursText && ojtHoursCard) {
        const percentComplete = Math.round((totalOJTHours / targetOJTHours) * 100);
        const progressColor = percentComplete >= 100 ? '#27ae60' : percentComplete >= 75 ? '#3498db' : percentComplete >= 50 ? '#f39c12' : '#e74c3c';
        
        ojtHoursText.innerHTML = `<strong>${totalOJTHours.toFixed(2)} / ${targetOJTHours} Hours</strong><br><span style="font-size: 0.85rem; color: ${progressColor}; font-weight: 600;">${percentComplete}% Complete</span>`;
        ojtHoursText.style.lineHeight = '1.6';
        
        // Set tooltip with more info
        ojtHoursCard.title = `OJT Hours Progress\nCompleted: ${totalOJTHours.toFixed(2)} hours\nRemaining: ${Math.max(0, targetOJTHours - totalOJTHours).toFixed(2)} hours`;
        ojtHoursCard.style.cursor = 'default';
        
        // Add click listener to show detailed view
        ojtHoursCard.addEventListener('click', function() {
            const remaining = Math.max(0, targetOJTHours - totalOJTHours);
            alert(`OJT Hours Summary\n\n✅ Completed: ${totalOJTHours.toFixed(2)} hours\n⏳ Remaining: ${remaining.toFixed(2)} hours\n📊 Progress: ${percentComplete}%\n\nTarget: ${targetOJTHours} hours`);
        });
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
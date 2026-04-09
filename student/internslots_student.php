<?php
session_start();
include '../db_connect.php';

// Check if student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student's current agency application
$student_query = "SELECT agency_name, agency_status FROM student_users WHERE student_id = ?";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();
$current_agency = $student_data['agency_name'] ?? null;
$current_status = $student_data['agency_status'] ?? null;

// Fetch all available agency slots
$query = "SELECT * FROM agency_slots ORDER BY agency ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <title>Available Internship Slots</title>
</head>
<body>

    <?php include ('../includes/sidebar_student.php'); ?>
    
    <br>
    <h1>Available Internship Slots</h1>
    <p>Browse and apply to internship opportunities</p>

    <!-- Student Application Status -->
    <?php if ($current_agency && $current_status) { ?>
    <div class="application-status-box">
        <div class="status-header">
            <h3>📋 Your Application Status</h3>
        </div>
        <div class="status-content">
            <p><strong>Agency:</strong> <?php echo htmlspecialchars($current_agency); ?></p>
            <p><strong>Status:</strong> 
                <span class="status-badge <?php echo $current_status; ?>">
                    <?php 
                    if ($current_status === 'pending') {
                        echo '⏳ Pending Review';
                    } elseif ($current_status === 'approved') {
                        echo '✅ Approved';
                    } elseif ($current_status === 'rejected') {
                        echo '❌ Rejected';
                    } else {
                        echo ucfirst($current_status);
                    }
                    ?>
                </span>
            </p>
        </div>
    </div>
    <br>
    <?php } ?>

    <div class="table-controls">
        <div class="show-entries">
            <p>Show 
            <select id="entriesSelect">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            entries</p>
        </div>
        <div class="search-box">
            <p>Search:
            <input type="text" id="customSearch" placeholder="Search slots...">
        </div>
    </div>

    <div class="table-container">
        <table id="internSlotsTable" class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Agency</th>
                    <th>Address</th>
                    <th>Available Slots</th>
                    <th>Duration</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $is_current = ($current_agency === $row['agency']);

        // Use updated_slots when present; fallback to slots if missing or zero while total slots > 0
        $available_slots = isset($row['updated_slots']) ? (int)$row['updated_slots'] : (int)$row['slots'];
        if ($available_slots === 0 && (int)$row['slots'] > 0) {
            $available_slots = (int)$row['slots'];
        }

        $is_available = ($available_slots > 0);
        $can_apply = ($is_available && !$current_agency);
        $button_disabled = !$can_apply;
    ?>
    <tr>
        <td><?php echo $no++; ?></td>
        <td><?php echo htmlspecialchars($row['agency']); ?></td>
        <td><?php echo htmlspecialchars($row['address']); ?></td>
        <td>
            <?php 
            $slot_class = $is_available ? 'slots-available' : 'slots-full';
            echo '<span class="slots-badge ' . $slot_class . '">' . $available_slots . ' / ' . $row['slots'] . ' slot(s)</span>';
            ?>
        </td>
        <td>
            <?php 
            if ($row['starting_date'] != "0000-00-00" && $row['ending_date'] != "0000-00-00") {
                echo date("M d, Y", strtotime($row['starting_date'])) . 
                     " - " . 
                     date("M d, Y", strtotime($row['ending_date']));
            } else {
                echo "Not set";
            }
            ?>
        </td>
        <td>
            <?php if ($is_current) { ?>
                <span class="badge-current">✓ Current Application</span>
            <?php } elseif ($can_apply) { ?>
                <button class="btn-apply" onclick="applyToAgency('<?php echo htmlspecialchars($row['agency']); ?>')">Apply</button>
            <?php } elseif (!$is_available) { ?>
                <span class="badge-unavailable">No Slots Available</span>
            <?php } else { ?>
                <span class="badge-pending">Cancellation of Current Application is Required to Apply</span>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
    $(document).ready(function () {
        var table = $('#internSlotsTable').DataTable({
            dom: 'rtip',      // removes default search & length
            pageLength: 10
        });

        // Custom search input
        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Custom entries dropdown
        $('#entriesSelect').on('change', function () {
            table.page.len(this.value).draw();
        });
    });

    function showNotification(message, type = 'info') {
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
        
        setTimeout(() => {
            notif.style.animation = 'slideOut 0.3s ease-in-out';
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }

    function applyToAgency(agency) {
        if (confirm(`Apply for internship at ${agency}?`)) {
            const formData = new FormData();
            formData.append('agency', agency);

            fetch('../phpbackend/apply-agency.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('✅ ' + data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('❌ ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error processing application.', 'error');
            });
        }
    }
    </script>

    <style>
        #internSlotsTable thead {
            background-color: #0A1D56;
        }
        
        #internSlotsTable thead th {
            background-color: #0A1D56;
            color: white;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            border: none;
        }

        #internSlotsTable tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        /* Application Status Box */
        .application-status-box {
            background: white;
            border-left: 5px solid #28a745;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            margin: 0 20px;
            overflow: hidden;
        }

        .status-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px;
        }

        .status-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .status-content {
            padding: 15px;
        }

        .status-content p {
            margin: 8px 0;
            font-size: 0.95rem;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Slots Badge */
        .slots-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .slots-available {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .slots-full {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Action Buttons & Badges */
        .btn-apply {
            padding: 6px 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-apply:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .badge-current {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-unavailable {
            display: inline-block;
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-pending {
            display: inline-block;
            background: #ffc107;
            color: #000;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

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
    </style>
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

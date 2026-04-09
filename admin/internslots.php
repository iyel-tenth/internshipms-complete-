<?php
include '../db_connect.php';

$query = "SELECT a.*, COUNT(s.student_id) as applied FROM agency_slots a LEFT JOIN student_users s ON a.agency = s.agency_name GROUP BY a.id ORDER BY a.agency ASC";
$result = mysqli_query($conn, $query);

// Fetch pending applications by agency
$pending_query = "SELECT agency_name, student_id, first_name, last_name, email FROM student_users WHERE agency_status = 'pending' ORDER BY agency_name ASC";
$pending_result = mysqli_query($conn, $pending_query);
$pending_applications = [];
if ($pending_result) {
    while ($pending = mysqli_fetch_assoc($pending_result)) {
        $agency = $pending['agency_name'];
        if (!isset($pending_applications[$agency])) {
            $pending_applications[$agency] = [];
        }
        $pending_applications[$agency][] = $pending;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Internship Slots</title>
    <style>
        .toolbar-row {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .toolbar-row .filter-container {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 12px;
            margin: 0;
        }
        .toolbar-row .filter-container select,
        .toolbar-row .filter-container button,
        .toolbar-row .filter-container .btn-add {
            margin: 0;
        }
        .toolbar-row .top-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        .toolbar-row .action-buttons {
            display: flex;
            gap: 10px;
        }
        .toolbar-row .action-buttons button {
            min-width: 110px;
            padding: 8px 14px;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <?php include ('../includes/sidebar_admin.php'); ?>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'EventDeleted') { ?>
        <p style="color: green; font-weight: bold;">Orientation event has been deleted successfully!</p>
    <?php } ?>

    <br>
    <h1>Internship Slots</h1>
    <p>Manages the internship slots to student interns</p>

    <div class="toolbar-row">
        <form method="GET" action="" class="filter-container">
            <input type="text" id="search-input" placeholder="Search slots..." style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="button" class="btn-filter" onclick="filterTable()">Filter</button>
            <a href="../companies_event/add_agency.php" class="btn-add">+ Add</a>
        </form>

        <div class="top-controls align-right">
            <div class="action-buttons">
                <button type="button" onclick="activateEdit()">✏️ Edit</button>
                <button type="button" onclick="activateDelete()">🗑 Delete</button>
            </div>
        </div>
    </div>
    <br>

    <div class="table-container">
        <table id="internSlotsTable" class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>No</th>
                    <th>Agency</th>
                    <th>Address</th>
                    <th>Slots</th>
                    <th>Duration</th>
                    <th>Interns</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $agency_name = htmlspecialchars($row['agency']);
    ?>
    <tr>
        <td><input type="checkbox" class="row-checkbox" data-id="<?php echo $row['id']; ?>"></td>
        <td><?php echo $no++; ?></td>
        <td><?php echo $agency_name; ?></td>
        <td><?php echo htmlspecialchars($row['address']); ?></td>
        <td>
            <div class="slot-fraction">
                <span class="slot-numerator"><?php echo $row['applied']; ?></span>
                <span class="slot-divider">/</span>
                <span class="slot-denominator"><?php echo $row['slots']; ?></span>
            </div>
        </td>
        <td>
            <?php 
            if ($row['starting_date'] != "0000-00-00" && $row['ending_date'] != "0000-00-00") {
                echo date("F j, Y", strtotime($row['starting_date'])) . 
                     " - " . 
                     date("F j, Y", strtotime($row['ending_date']));
            } else {
                echo "Not set";
            }
            ?>
        </td>
        <td>
            <button class="btn-view-interns" onclick="viewInterns('<?php echo $agency_name; ?>')">View Interns</button>
        </td>
    </tr>
    <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pending Applications Notifications -->
    <?php if (count($pending_applications) > 0) { ?>
    <div class="notifications-section">
        <h3 style="margin-left: 20px; color: #0A1D56;">📬 Pending Student Applications</h3>
        <div class="notifications-grid">
            <?php foreach ($pending_applications as $agency => $students) { ?>
                <div class="notification-box">
                    <div class="notification-header">
                        <h4>🏢 <?php echo htmlspecialchars($agency); ?></h4>
                        <span class="badge-pending"><?php echo count($students); ?> Student(s)</span>
                    </div>
                    <div class="notification-body">
                        <?php foreach ($students as $student) { ?>
                            <div class="student-item">
                                <div class="student-info">
                                    <p class="student-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                    <p class="student-id">ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                                    <p class="student-email">Email: <?php echo htmlspecialchars($student['email']); ?></p>
                                </div>
                                <div class="student-actions">
                                    <button class="btn-approve" onclick="approveApplication('<?php echo htmlspecialchars($student['student_id']); ?>', '<?php echo htmlspecialchars($agency); ?>')">Approve</button>
                                    <button class="btn-reject" onclick="rejectApplication('<?php echo htmlspecialchars($student['student_id']); ?>')">Reject</button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <br>
    <?php } ?>

    <!-- INTERNS MODAL -->
    <div id="internsModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeInternsModal()">&times;</span>
            <div class="modal-header" id="modalTitle">Interns</div>
            <div class="modal-body" id="internsContainer">
            </div>
            <button class="close-btn" onclick="closeInternsModal()">Close</button>
        </div>
    </div>

    <script>
    $(document).ready(function () {
        var table = $('#internSlotsTable').DataTable({
            dom: 'rtip',      
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: [0, 6] } // disable sorting on checkbox and View Interns
            ]
        });

        // Custom search input
        $('#search-input').on('keyup', function () {
            table.search(this.value).draw();
        });
    });

    // Select All checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Search/Filter functionality
    function filterTable() {
        const input = document.getElementById('search-input');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('internSlotsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }

    // Edit functionality
    function activateEdit() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            Swal.fire('Select a slot', 'Please select a slot to edit.', 'warning');
            return;
        }
        if (checked.length > 1) {
            Swal.fire('Select one slot', 'You can only edit one slot at a time.', 'warning');
            return;
        }
        const id = checked[0].dataset.id;
        window.location.href = '../companies_event/edit_company.php?id=' + id;
    }

    // Delete functionality
    function activateDelete() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) {
            Swal.fire('Select slots', 'Please select at least one slot to delete.', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete ' + checked.length + ' slot(s). This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d32f2f',
            cancelButtonColor: '#757575',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                checked.forEach(checkbox => {
                    const id = checkbox.dataset.id;
                    deleteSlot(id);
                });
            }
        });
    }

    // Delete single slot
    function deleteSlot(id) {
        fetch('../phpbackend/delete-slot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Deleted!', 'Slot has been deleted.', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message || 'Failed to delete slot.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'An error occurred: ' + error, 'error');
        });
    }

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

    function approveApplication(studentId, agency) {
        if (confirm(`Approve application for ${agency}?`)) {
            const formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('agency_name', agency);
            formData.append('action', 'approve');

            fetch('../phpbackend/manage-applications.php', {
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

    function rejectApplication(studentId) {
        // Show modal for rejection reason
        Swal.fire({
            title: 'Reject Application',
            html: `
                <div style="text-align: center; margin-bottom: 8px;">
                    <label for="rejectionReason" style="font-weight: 600; margin-bottom: 6px; display: block; font-size: 0.9rem;">Please provide a reason for rejection:</label>
                    <textarea id="rejectionReason" class="swal2-textarea" placeholder="e.g., Insufficient qualifications, Agency slots full, etc." style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif; font-size: 0.85rem; resize: none;"></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d32f2f',
            cancelButtonColor: '#757575',
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            width: '450px',
            padding: '1.5rem',
            scrollbarPadding: false,
            didOpen: () => {
                document.getElementById('rejectionReason').focus();
                // Remove scrolling from body when modal is open
                document.body.style.overflow = 'hidden';
            },
            willClose: () => {
                // Restore scrolling when modal closes
                document.body.style.overflow = 'auto';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const rejectionReason = document.getElementById('rejectionReason').value.trim();
                if (!rejectionReason) {
                    Swal.fire('Required', 'Please provide a rejection reason.', 'warning');
                    return;
                }
                
                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('action', 'reject');
                formData.append('rejection_reason', rejectionReason);

                // Show loading state
                Swal.showLoading();

                fetch('../phpbackend/manage-applications.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    return response.text().then(text => {
                        console.log('Raw response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                        }
                    });
                })
                .then(data => {
                    console.log('Parsed data:', data);
                    Swal.hideLoading();
                    
                    if (data && data.success) {
                        Swal.fire('Success!', data.message || 'Application rejected successfully!', 'success').then(() => {
                            showNotification('✅ ' + (data.message || 'Application rejected'), 'success');
                            setTimeout(() => location.reload(), 500);
                        });
                    } else {
                        const errorMsg = data && data.message ? data.message : 'Unknown error occurred';
                        Swal.fire('Error!', errorMsg, 'error');
                        showNotification('❌ ' + errorMsg, 'error');
                    }
                })
                .catch(error => {
                    console.error('Full error:', error);
                    Swal.hideLoading();
                    Swal.fire('Error!', 'Error: ' + error.message, 'error');
                    showNotification('❌ ' + error.message, 'error');
                });
            }
        });
    }

    function viewInterns(agency) {
        console.log('Viewing interns for agency:', agency);
        const modal = document.getElementById('internsModal');
        const modalTitle = document.getElementById('modalTitle');
        const internsContainer = document.getElementById('internsContainer');

        // Set modal title
        modalTitle.textContent = 'Interns at ' + agency;

        // Show loading state
        internsContainer.innerHTML = '<div class="no-interns">Loading...</div>';
        modal.style.display = 'block';

        // Fetch interns for this agency
        const fetchUrl = '../phpbackend/fetch-interns.php?agency=' + encodeURIComponent(agency);
        console.log('Fetching from:', fetchUrl);
        
        fetch(fetchUrl)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers.get('content-type'));
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error(`Invalid content type. Expected JSON, got: ${contentType}`);
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text(); // Read as text first to debug
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(`JSON parse error: ${e.message}\nResponse: ${text.substring(0, 200)}`);
                }
            })
            .then(data => {
                console.log('Parsed data:', data);
                if (data.success && data.interns && data.interns.length > 0) {
                    let html = '';
                    data.interns.forEach(intern => {
                        const statusClass = intern.agency_status === 'approved' ? 'status-approved' : 'status-pending';
                        const statusText = intern.agency_status === 'approved' ? 'Approved' : 'Pending';
                        html += `
                            <div class="intern-card">
                                <p class="intern-name">${intern.first_name} ${intern.last_name}</p>
                                <p class="intern-detail"><strong>Student ID:</strong> ${intern.student_id}</p>
                                <p class="intern-detail"><strong>Email:</strong> ${intern.email}</p>
                                <span class="intern-status ${statusClass}">${statusText}</span>
                            </div>
                        `;
                    });
                    internsContainer.innerHTML = html;
                } else if (data.success) {
                    internsContainer.innerHTML = '<div class="no-interns">No interns applied to this agency yet.</div>';
                } else {
                    throw new Error(data.message || 'Unknown error from server');
                }
            })
            .catch(error => {
                console.error('Full error:', error);
                internsContainer.innerHTML = `<div class="no-interns">Error: ${error.message}</div>`;
            });
    }

    function closeInternsModal() {
        const modal = document.getElementById('internsModal');
        modal.style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('internsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
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

        /* Slot Fraction Display */
        .slot-fraction {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .slot-numerator {
            color: #0A1D56;
            font-size: 1.3rem;
        }

        .slot-divider {
            color: #0A1D56;
            font-size: 1.5rem;
            margin: 0 3px;
        }

        .slot-denominator {
            color: #666;
            font-size: 1rem;
        }

        /* Notifications Section */
        .notifications-section {
            margin: 20px 0;
        }

        .notifications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            padding: 0 20px;
        }

        .notification-box {
            background: white;
            border-left: 5px solid #FFA500;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .notification-header {
            background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h4 {
            margin: 0;
            font-size: 1.1rem;
            flex-grow: 1;
        }

        .badge-pending {
            background: rgba(255, 255, 255, 0.3);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .notification-body {
            padding: 15px;
        }

        .student-item {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid #FFA500;
        }

        .student-item:last-child {
            margin-bottom: 0;
        }

        .student-info {
            flex-grow: 1;
        }

        .student-name {
            margin: 0;
            font-weight: 600;
            color: #0A1D56;
            font-size: 0.95rem;
        }

        .student-id {
            margin: 4px 0 0 0;
            font-size: 0.85rem;
            color: #666;
        }

        .student-email {
            margin: 2px 0 0 0;
            font-size: 0.8rem;
            color: #999;
        }

        .student-status {
            text-align: center;
            margin-left: 10px;
        }

        .status-label {
            background: #fff3cd;
            color: #856404;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid #ffeaa7;
        }

        .student-actions {
            display: flex;
            gap: 8px;
            margin-left: 10px;
        }

        .btn-approve,
        .btn-reject {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-view-interns {
            padding: 8px 16px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view-interns:hover {
            background-color: #138496;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
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
            max-width: 600px;
            animation: slideIn 0.3s ease;
            max-height: 85vh;
            overflow-y: auto;
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

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
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

        .intern-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 12px;
            border-left: 4px solid #17a2b8;
        }

        .intern-card:last-child {
            margin-bottom: 0;
        }

        .intern-name {
            font-weight: 600;
            color: #0A1D56;
            font-size: 1rem;
            margin: 0 0 5px 0;
        }

        .intern-detail {
            font-size: 0.9rem;
            color: #666;
            margin: 3px 0;
        }

        .intern-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 5px;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .no-interns {
            text-align: center;
            color: #999;
            padding: 20px;
            font-size: 1.05rem;
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

</body>
</html>
<?php
include '../db_connect.php';

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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Internship Activities</title>
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
        @media (max-width: 900px) {
            .toolbar-row {
                justify-content: flex-start;
            }
            .toolbar-row .filter-container {
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <?php include ('../includes/sidebar_admin.php'); ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'EventDeleted') { ?>

    <p style="color: green; font-weight: bold;">Orientation event has been deleted successfully!</p>
<?php } ?>

    <h1>Internship Activities</h1>
    <p>Manages the records of the  current and past orientation</p>
  <br>
  
  <div class="toolbar-row">
    <form method="GET" action="" class="filter-container">
        <input type="text" id="search-input" placeholder="Search orientations..." style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        <button type="button" class="btn-filter" onclick="filterTable()">Filter</button>
        <a href="../companies_event/add_orient.php" class="btn-add">+ Add</a>
    </form>

    <div class="top-controls align-right">
        <div class="action-buttons">
            <button type="button" onclick="activateEdit()">✏️ Edit</button>
            <button type="button" onclick="activateDelete()">🗑 Delete</button>
        </div>
    </div>
  </div>
  
  <div class="table-container">
    <table id="orientationTable" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAll"></th>
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
            <td><input type="checkbox" class="row-checkbox" data-id="<?php echo $row['id']; ?>"></td>
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
                <a href="orient_event/edit_event.php?id=<?php echo $row['id']; ?>" onclick="publishOrientation(<?php echo $row['id']; ?>); return false;">Publish</a>
            </td>
        </tr>
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
        order: [[5, 'asc']], // sort by Date column
        columnDefs: [
            { orderable: false, targets: [0, 7] } // disable sorting on checkbox and Actions
        ]
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
    const table = document.getElementById('orientationTable');
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
        Swal.fire('Select an orientation', 'Please select an orientation to edit.', 'warning');
        return;
    }
    if (checked.length > 1) {
        Swal.fire('Select one orientation', 'You can only edit one orientation at a time.', 'warning');
        return;
    }
    const id = checked[0].dataset.id;
    window.location.href = 'orient_event/edit_event.php?id=' + id;
}

// Delete functionality
function activateDelete() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (checked.length === 0) {
        Swal.fire('Select orientations', 'Please select at least one orientation to delete.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete ' + checked.length + ' orientation(s). This cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d32f2f',
        cancelButtonColor: '#757575',
        confirmButtonText: 'Yes, delete!'
    }).then((result) => {
        if (result.isConfirmed) {
            checked.forEach(checkbox => {
                const id = checkbox.dataset.id;
                deleteOrientation(id);
            });
        }
    });
}

// Delete single orientation
function deleteOrientation(id) {
    fetch('../phpbackend/delete-orientation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Deleted!', 'Orientation has been deleted.', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to delete orientation.', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred: ' + error, 'error');
    });
}

// Publish orientation
function publishOrientation(id) {
    fetch('../phpbackend/publish-orientation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Published!', 'Orientation has been published.', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', data.message || 'Failed to publish orientation.', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'An error occurred: ' + error, 'error');
    });
}
    </script>

</body>
</html>
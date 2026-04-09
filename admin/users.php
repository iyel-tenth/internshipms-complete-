<?php
include '../db_connect.php';

$where = "";
$student_where = "";

if (isset($_GET['role']) && $_GET['role'] != "") {
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    $where = "WHERE role = '$role'";
    $student_where = "WHERE role = '$role'";
}

// Union query to fetch from both users and student_users tables
$sql = "
    SELECT id, first_name, last_name, email, role, course_major, last_login, last_logout, 'users' as source
    FROM users $where
    UNION
    SELECT id, first_name, last_name, email, role, course_major, last_login, last_logout, 'student_users' as source
    FROM student_users $student_where
    ORDER BY last_login DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
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
    <style>
        .toolbar-row {
            display: flex;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: center;
        
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
    <title>User Profiles</title>
</head>
<body>
<?php include ('../includes/sidebar_admin.php'); ?>
    <br> 
    <h1>Users</h1>
    <p>Displays all users of the system - students, administrators, and industry partners</p>
    <br>

<div class="toolbar-row">
   <form method="GET" action="" class="filter-container">
    
    <select name="role" class="filter-select">
        <option value="">Select Role</option>
        <option value="admin" <?= (isset($_GET['role']) && $_GET['role']=='admin') ? 'selected' : '' ?>>Admin</option>
        <option value="student" <?= (isset($_GET['role']) && $_GET['role']=='student') ? 'selected' : '' ?>>Student</option>
        <option value="teacher" <?= (isset($_GET['role']) && $_GET['role']=='teacher') ? 'selected' : '' ?>>Instructor</option>
    </select>

    <button type="submit" class="btn-filter">Filter</button>
    <a href="../companies_event/add-user.php" class="btn-add">Add</a>
    </form>

    <div class="top-controls align-right">
        <div class="action-buttons">
            <button type="button" onclick="activateEdit()">✏️ Edit</button>
            <button type="button" onclick="activateDelete()">🗑 Delete</button>
        </div>
    </div>
</div>

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
            <input type="text" id="customSearch" placeholder="Search users...">
        </div>
    </div>
<div class="table-container">
    <table id="usersTable" class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Course and Major</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
<?php
while ($row = $result->fetch_assoc()) {

    $lastLogin = !empty($row['last_login'])
        ? date("M d, Y h:i A", strtotime($row['last_login']))
        : '—';

    $lastLogout = !empty($row['last_logout'])
        ? date("M d, Y h:i A", strtotime($row['last_logout']))
        : '—';

    echo "<tr data-id=\"{$row['id']}\" data-source=\"{$row['source']}\">
        <td>{$row['id']}</td>
        <td>{$row['first_name']}</td>
        <td>{$row['last_name']}</td>
        <td>{$row['email']}</td>
        <td>{$row['role']}</td>
        <td>{$row['course_major']}</td>
        <td>$lastLogin</td>
        <td>
            <button type=\"button\" class=\"btn-edit-row\">✏️</button>
            <button type=\"button\" class=\"btn-delete-row\">🗑</button>
        </td>
    </tr>";
}
?>
</tbody>
    </table>
</div>

    <script>
    $(document).ready(function () {

    var table = $('#usersTable').DataTable({
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

    // Row action buttons
    $('#usersTable tbody').on('click', '.btn-edit-row', function () {
        const row = $(this).closest('tr');
        activateEditRow(row);
    });

    $('#usersTable tbody').on('click', '.btn-delete-row', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const source = row.data('source');
        activateDeleteRow(id, source, row);
    });
});

let editingRow = null;

function activateEdit() {
    Swal.fire({
        title: 'Select a row to edit',
        icon: 'info',
        confirmButtonColor: '#0A1D56'
    });

    $('#usersTable tbody').off('click.users');
    $('#usersTable tbody').on('click.users', 'tr', function () {
        if (editingRow) return;
        activateEditRow($(this));
    });
}

function activateEditRow(row) {
    if (editingRow) return;
    editingRow = row;

    const id = row.data('id');
    const source = row.data('source');
    const cells = row.children('td');
    const firstName = cells.eq(1).text().trim();
    const lastName = cells.eq(2).text().trim();
    const email = cells.eq(3).text().trim();
    const role = cells.eq(4).text().trim();
    const courseMajor = cells.eq(5).text().trim();

    cells.eq(1).html(`<input type="text" id="editFirstName" value="${firstName}" style="width:100%;">`);
    cells.eq(2).html(`<input type="text" id="editLastName" value="${lastName}" style="width:100%;">`);
    cells.eq(3).html(`<input type="email" id="editEmail" value="${email}" style="width:100%;">`);
    cells.eq(4).html(`<input type="text" id="editRole" value="${role}" style="width:100%;">`);
    cells.eq(5).html(`<input type="text" id="editCourseMajor" value="${courseMajor}" style="width:100%;">`);

    cells.eq(7).html(`
        <button type="button" onclick="saveEdit(${id}, '${source}')">💾</button>
        <button type="button" onclick="cancelEdit()">❌</button>
    `);
}

function saveEdit(id, source) {
    const newFirstName = $('#editFirstName').val().trim();
    const newLastName = $('#editLastName').val().trim();
    const newEmail = $('#editEmail').val().trim();
    const newRole = $('#editRole').val().trim();
    const newCourseMajor = $('#editCourseMajor').val().trim();

    Swal.fire({
        title: 'Save changes?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56',
        confirmButtonText: 'Yes, Save'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '../phpbackend/update-user.php',
            method: 'POST',
            data: {
                id: id,
                source: source,
                first_name: newFirstName,
                last_name: newLastName,
                email: newEmail,
                role: newRole,
                course_major: newCourseMajor
            },
            success: function(response) {
                if (response.trim() === 'success') {
                    const cells = editingRow.children('td');
                    cells.eq(1).text(newFirstName);
                    cells.eq(2).text(newLastName);
                    cells.eq(3).text(newEmail);
                    cells.eq(4).text(newRole);
                    cells.eq(5).text(newCourseMajor);
                    cells.eq(7).html(`
                        <button type="button" class="btn-edit-row">✏️</button>
                        <button type="button" class="btn-delete-row">🗑</button>
                    `);

                    Swal.fire({
                        title: 'Updated Successfully!',
                        icon: 'success',
                        confirmButtonColor: '#0A1D56'
                    });

                    editingRow = null;
                } else {
                    Swal.fire('Update Failed', response, 'error');
                }
            },
            error: function() {
                Swal.fire('Update Failed', 'Server error occurred.', 'error');
            }
        });
    });
}

function cancelEdit() {
    location.reload();
}

function activateDelete() {
    Swal.fire({
        title: 'Select a row to delete',
        icon: 'warning',
        confirmButtonColor: '#0A1D56'
    });

    $('#usersTable tbody').off('click.users');
    $('#usersTable tbody').on('click.users', 'tr', function () {
        const row = $(this);
        const id = row.data('id');
        const source = row.data('source');
        activateDeleteRow(id, source, row);
    });
}

function activateDeleteRow(id, source, row) {
    Swal.fire({
        title: 'Delete this user?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '../phpbackend/delete-user.php',
            method: 'POST',
            data: {
                id: id,
                source: source
            },
            success: function(response) {
                if (response.trim() === 'success') {
                    row.remove();
                    Swal.fire('Deleted!', 'The user has been deleted.', 'success');
                } else {
                    Swal.fire('Delete Failed', response, 'error');
                }
            },
            error: function() {
                Swal.fire('Delete Failed', 'Server error occurred.', 'error');
            }
        });
    });
}

</script>
</body>
</html>
<?php
include 'db_connect.php';

$where = "";

if (isset($_GET['role']) && $_GET['role'] != "") {
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    $where = "WHERE role = '$role'";
}

$sql = "SELECT * FROM users $where";
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
    <title>User Profiles</title>
</head>
<body>
<?php include ('includes/sidebar.php'); ?>
    <br> 
    <h1>Users</h1>
    <p>Displays all users of the system - students, administrators, and industry partners</p>
    <br>

   <form method="GET" action="" class="filter-container">
    
    <select name="role" class="filter-select">
        <option value="">Select Role</option>
        <option value="admin" <?= (isset($_GET['role']) && $_GET['role']=='admin') ? 'selected' : '' ?>>Admin</option>
        <option value="student" <?= (isset($_GET['role']) && $_GET['role']=='student') ? 'selected' : '' ?>>Student</option>
        <option value="teacher" <?= (isset($_GET['role']) && $_GET['role']=='teacher') ? 'selected' : '' ?>>Instructor</option>
    </select>

    <button type="submit" class="btn-filter">Filter</button>
    <a href="companies_event/add-user.php" class="btn-add">Add</a>
    </form>

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
                <th>Last Logout</th>

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

    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['first_name']}</td>
        <td>{$row['last_name']}</td>
        <td>{$row['email']}</td>
        <td>{$row['role']}</td>
        <td>{$row['course_major']}</td>
        <td>$lastLogin</td>
        <td>$lastLogout</td>
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

});
</script>

</script>
</body>
</html>
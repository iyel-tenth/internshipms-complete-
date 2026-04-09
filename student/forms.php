<?php
include 'db_connect.php';

$query = "SELECT id, template_name, template_description, is_hidden, is_published 
          FROM form_templates 
          ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <title>Form Templates</title>
</head>
<body>

<?php include ('includes/sidebar.php'); ?>

<h1>Form Templates</h1>
<p>Manages form templates that will be used for internship</p>
<br>

<form>
    <a href="companies_event/addform.php" class="btn-add">+ Add Form Template</a>
</form>
<br>

<div class="table-container">
<table id="formTemplatesTable" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Form Name</th>
            <th>Form Description</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $no = 1;
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo htmlspecialchars($row['template_name']); ?></td>
            <td><?php echo htmlspecialchars($row['template_description']); ?></td>
            <td>
                <a href="view_template.php?id=<?php echo $row['id']; ?>" class="action-btn btn-view">View</a>
                <a href="edit_template.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit">Edit</a>
                <a href="publish_template.php?id=<?php echo $row['id']; ?>" class="action-btn btn-publish">Publish</a>
                <a href="delete_template.php?id=<?php echo $row['id']; ?>"
                   class="action-btn btn-delete"
                   onclick="return confirm('Are you sure you want to delete this template?')">
                   Delete
                </a>
            </td>
        </tr>
        <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="4" style="text-align:center; padding:20px;">
                No templates found
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
</div>

<!-- DataTables scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#formTemplatesTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'asc']], // sort by No column
        columnDefs: [
            { orderable: false, targets: 3 } // disable sorting on Actions
        ]
    });
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

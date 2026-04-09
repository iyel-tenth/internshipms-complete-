<?php
include 'db_connect.php';

$query = "SELECT id, template_name, template_description
          FROM form_templates 
          ORDER BY id ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <title>Sample Accomplished Forms</title>

    <style>
    /* =========================
       TOP CONTROL LAYOUT
    ========================= */
    .top-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
    }

    /* BUTTON STYLE */
    .action-buttons button,
    .btn-add {
        padding: 10px 18px;
        border: none;
        background: #0A1D56;
        color: #ffffff;
        font-size: 14px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .action-buttons button:hover,
    .btn-add:hover {
        background: #112b85;
        transform: translateY(-2px);
    }

    /* SELECTED ROW */
    .selected-row {
        background-color: #d6e4ff !important;
    }
    </style>
</head>
<body>

<?php include ('includes/sidebar_student.php'); ?>

<h1>Sample Accomplished Forms</h1>
<p>Accomplished forms that will be used for internship</p>
<br>

<!-- TOP CONTROLS -->
<div class="top-controls">
    <a href="companies_event/add-form.php" class="btn-add">+ Add Form Template</a>

    <div class="action-buttons">
        <button type="button" onclick="activateEdit()">✏️ Edit</button>
        <button type="button" onclick="activateDelete()">🗑 Delete</button>
    </div>
</div>

<div class="table-container">
<table id="formTemplatesTable" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Form Name</th>
            <th>Form Description</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $no = 1;
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <tr data-id="<?php echo $row['id']; ?>">
            <td><?php echo $no++; ?></td>

            <td>
                <a href="view_pdf.php?id=<?php echo $row['id']; ?>" 
                   target="_blank"
                   style="text-decoration: none; color: #007bff; font-weight: 600;">
                   <?php echo htmlspecialchars($row['template_name']); ?>
                </a>
            </td>

            <td><?php echo htmlspecialchars($row['template_description']); ?></td>
        </tr>
        <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="3" style="text-align:center; padding:20px;">
                No templates found
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- SWEETALERT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

let editingRow = null;
let table;

// DataTable init
$(document).ready(function () {
    table = $('#formTemplatesTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        order: [[0, 'asc']]
    });
});

function activateEdit() {

    Swal.fire({
        title: 'Select a row to edit',
        icon: 'info',
        confirmButtonColor: '#0A1D56'
    });

    $("#formTemplatesTable tbody").on("click", "tr", function () {

        if (editingRow) return;

        editingRow = this;
        let id = $(this).data("id");

        let nameCell = this.children[1];
        let descCell = this.children[2];

        let currentName = nameCell.innerText;
        let currentDesc = descCell.innerText;

        nameCell.innerHTML = `<input type="text" id="editName" value="${currentName}" style="width:100%;">`;
        descCell.innerHTML = `<textarea id="editDesc" style="width:100%;">${currentDesc}</textarea>`;

        $(".action-buttons").html(`
            <button onclick="saveEdit(${id})">💾 Save</button>
            <button onclick="cancelEdit()">❌ Cancel</button>
        `);
    });
}

function saveEdit(id) {

    Swal.fire({
        title: 'Save changes?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56',
        confirmButtonText: 'Yes, Save'
    }).then((result) => {

        if (result.isConfirmed) {

            let newName = document.getElementById("editName").value;
            let newDesc = document.getElementById("editDesc").value;

            $.ajax({
                url: "phpbackend/update-inline-form.php",
                method: "POST",
                data: {
                    id: id,
                    template_name: newName,
                    template_description: newDesc
                },
                success: function(response) {

                    if (response.trim() === "success") {

                        let rowIndex = table.row(editingRow).index();

                        // Proper DataTable update
                        table.cell(rowIndex, 1).data(newName);
                        table.cell(rowIndex, 2).data(newDesc);
                        table.draw(false);

                        Swal.fire({
                            title: 'Updated Successfully!',
                            icon: 'success',
                            confirmButtonColor: '#0A1D56'
                        });

                        resetButtons();

                    } else {
                        Swal.fire('Update Failed');
                    }
                }
            });
        }
    });
}

function cancelEdit() {

    Swal.fire({
        title: 'Cancel editing?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56',
        confirmButtonText: 'Yes, Cancel'
    }).then((result) => {

        if (result.isConfirmed) {
            location.reload();
        }
    });
}

function resetButtons() {

    editingRow = null;

    $(".action-buttons").html(`
        <button type="button" onclick="activateEdit()">✏️ Edit</button>
        <button type="button" onclick="activateDelete()">🗑 Delete</button>
    `);

    $("#formTemplatesTable tbody").off("click", "tr");
}

</script>

</body>
</html>



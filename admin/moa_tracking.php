<?php
include '../db_connect.php';

$query = "SELECT * FROM moa_tracking ORDER BY id ASC";
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

    <link rel="stylesheet" type="text/css" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>MOA Tracking</title>

</head>
<body>

    <?php include ('../includes/sidebar_admin.php'); ?>

    <h1>MOA Tracking</h1>
    <p>Monitors the progress of Memorandum of Agreements (MOA) of partnered agencies </p>
<br>

<!-- TOP CONTROLS -->
<div class="top-controls">
    <a href="../companies_event/add_moa.php" class="btn-add">+ Add MOA Progress</a>

    <div class="action-buttons">
        <!-- <button type="button" onclick="activateEdit()">✏️ Edit</button> -->
        <button type="button" onclick="activateDelete()">🗑 Delete</button>
    </div>
</div>

<div class="table-container">
<table id="formTemplatesTable" class="display" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Agency</th>
            <th>Progress</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                // Calculate the progress percentage based on completed steps
                $completedSteps = 0;
                for ($i = 1; $i <= 7; $i++) {
                    if ($row["step" . $i] == 1) {
                        $completedSteps++;
                    }
                }
                $progressPercentage = ($completedSteps / 7) * 100;
                $isComplete = $completedSteps === 7; // Check if all steps are completed
    ?>
        <tr data-id="<?php echo $row['id']; ?>">
            <td><?php echo htmlspecialchars($row['agency_name']); ?></td>
            <td>
                <div class="progress">
                    <div 
                        class="progress-bar <?php echo $isComplete ? 'bg-success' : 'bg-primary'; ?>" 
                        role="progressbar" 
                        style="width: <?php echo $progressPercentage; ?>%" 
                        aria-valuenow="<?php echo $progressPercentage; ?>" 
                        aria-valuemin="0" 
                        aria-valuemax="100"
                        title="<?php echo $isComplete ? 'Processed' : ''; ?>">
                        <?php echo $isComplete ? 'Processed' : ''; ?>
                    </div>
                </div>
            </td>

            <td class="action-column">

                <?php
                $steps = [
                    1 => ["icon" => "fa-comments",       "color" => "primary",   "title" => "Communicated to Agency"],
                    2 => ["icon" => "fa-user-tie",       "color" => "success",   "title" => "Signed by Campus Executive Director"],
                    3 => ["icon" => "fa-university",     "color" => "info",      "title" => "Signed by University President"],
                    4 => ["icon" => "fa-user-graduate",  "color" => "warning",   "title" => "Received by Student Interns"],
                    5 => ["icon" => "fa-stamp",          "color" => "secondary", "title" => "Notary"],
                    6 => ["icon" => "fa-paper-plane",    "color" => "dark",      "title" => "Submitted Copy to Agency"],
                    7 => ["icon" => "fa-undo",           "color" => "danger",    "title" => "Return Other Copies"]
                ];
                ?>

                <?php foreach ($steps as $i => $data): 
                    $isDone = $row["step".$i] == 1;
                    $isAdminStep = ($i <= 3);
                    $isNextStep = $isAdminStep && ($i == $completedSteps + 1); // Only admin steps are actionable here
                ?>

                    <button 
                        class="step-btn btn btn-sm 
                        <?php echo $isDone ? 'btn-'.$data['color'] : 'btn-outline-'.$data['color']; ?>"
                        data-step="<?php echo $i; ?>"
                        <?php echo ($isAdminStep && !$isDone && $isNextStep) ? '' : 'disabled'; ?>
                        title="<?php echo $data['title']; ?> (<?php echo $isAdminStep ? 'Admin' : 'Student'; ?>)">

                        <i class="fas <?php echo $data['icon']; ?>"></i>
                    </button>

                <?php endforeach; ?>

            </td>
        </tr>
        <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="3" style="text-align:center; padding:20px;">
                No MOA records found
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

        let nameCell = this.children[0];
        let descCell = this.children[1];

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

$(document).on("click", ".step-btn", function() {

    let button = $(this);
    let row = button.closest("tr");
    let id = row.data("id");
    let step = button.data("step");

    if (!id || !step) {
        Swal.fire({
            title: 'Error',
            text: 'Invalid row or step data.',
            icon: 'error',
            confirmButtonColor: '#0A1D56'
        });
        return;
    }

    Swal.fire({
        title: 'Mark this step as completed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "../phpbackend/update-step.php",
                method: "POST",
                data: { id: id, step: step },
                success: function(response) {
                    if (response.trim() === "success") {
                        button
                            .removeClass(function(index, className) {
                                return (className.match(/(^|\s)btn-outline-\S+/g) || []).join(' ');
                            })
                            .addClass("btn-success")
                            .prop("disabled", true);

                        // Update progress bar
                        let progressBar = row.find(".progress-bar").first();
                        let currentWidth = parseFloat(progressBar.css("width")) || 0;
                        let parentWidth = parseFloat(progressBar.parent().css("width")) || 1; // Avoid division by zero
                        let currentPercentage = (currentWidth / parentWidth) * 100;

                        let newPercentage = currentPercentage + 14.28;
                        if (newPercentage > 100) newPercentage = 100; // Cap at 100%

                        progressBar.css("width", newPercentage + "%").attr("aria-valuenow", newPercentage);

                        Swal.fire({
                            title: 'Step Locked ✔',
                            icon: 'success',
                            confirmButtonColor: '#0A1D56'
                        });
                    } else {
                        Swal.fire({
                            title: 'Update Failed',
                            text: response,
                            icon: 'error',
                            confirmButtonColor: '#0A1D56'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'AJAX Error',
                        text: `Status: ${status}, Error: ${error}`,
                        icon: 'error',
                        confirmButtonColor: '#0A1D56'
                    });
                }
            });
        }
    });
});

function activateDelete() {
    Swal.fire({
        title: 'Select a row to delete',
        icon: 'info',
        confirmButtonColor: '#0A1D56'
    });

    $("#formTemplatesTable tbody").on("click", "tr", function () {
        let id = $(this).data("id");

        Swal.fire({
            title: 'Are you sure you want to delete this record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "../phpbackend/delete-moa.php",
                    method: "POST",
                    data: { id: id },
                    success: function(response) {
                        if (response.trim() === "success") {
                            Swal.fire('Deleted!', 'The record has been deleted.', 'success');
                            location.reload();
                        } else {
                            Swal.fire('Error!', 'Failed to delete the record.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error!', `AJAX Error: ${error}`, 'error');
                    }
                });
            }
        });
    });
}

</script>

</body>
</html>



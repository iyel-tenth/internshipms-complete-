<?php
session_start();
include '../db_connect.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student's data including agency name
$student_query = $conn->prepare("SELECT student_id, first_name, last_name, agency_name, agency_status FROM student_users WHERE student_id = ?");
$student_query->bind_param("s", $student_id);
$student_query->execute();
$student_result = $student_query->get_result();
$student = $student_result->fetch_assoc();
$student_query->close();

if (!$student) {
    die("Student record not found");
}

$student_agency_name = $student['agency_name'] ?? null;

// Fetch MOA tracking data linked to student's agency
$result = null;
if ($student_agency_name) {
    // Use flexible matching: check if student agency contains MOA agency OR MOA agency contains student agency
    $search_term1 = '%' . $student_agency_name . '%';
    $moa_query = $conn->prepare("SELECT id, agency_name, step1, step2, step3, step4, step5, step6, step7 FROM moa_tracking WHERE agency_name LIKE ? OR ? LIKE CONCAT('%', agency_name, '%')");
    $moa_query->bind_param("ss", $search_term1, $student_agency_name);
    $moa_query->execute();
    $result = $moa_query->get_result();
    $moa_query->close();
} else {
    // If no agency assigned, create empty result
    $result = $conn->query("SELECT id, agency_name, step1, step2, step3, step4, step5, step6, step7 FROM moa_tracking WHERE 1=0");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Form Templates</title>

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

    .action-column {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    }

    .step-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .step-btn i {
        font-size: 14px;
    }

    </style>
</head>
<body>

<?php include ('../includes/sidebar_student.php'); ?>

<h1>MOA Tracking</h1>
<p>Monitors the progress of Memorandum of Agreement (MOA) for 
<?php 
    if ($student_agency_name) {
        echo '<strong>' . htmlspecialchars($student_agency_name) . '</strong>';
    } else {
        echo 'your assigned agency';
    }
?>
</p>
<br>

<!-- TOP CONTROLS -->
<div class="top-controls">
    <!-- MOA Progress button only shown if student has agency assigned -->
    <?php if ($student_agency_name && $student['agency_status'] === 'approved'): ?>
        <div style="font-size: 14px; color: #0A1D56;">
            <strong>Status:</strong> 
            <span style="background: #d4edda; padding: 5px 12px; border-radius: 20px; color: #155724;">
                ✅ Approved
            </span>
        </div>
    <?php elseif ($student_agency_name): ?>
        <div style="font-size: 14px; color: #0A1D56;">
            <strong>Status:</strong> 
            <span style="background: #fff3cd; padding: 5px 12px; border-radius: 20px; color: #856404;">
                ⏳ <?php echo ucfirst($student['agency_status']); ?>
            </span>
        </div>
    <?php else: ?>
        <div style="font-size: 14px; color: #666;">
            📝 <em>No agency assigned yet. Apply for an internship slot to get started.</em>
        </div>
    <?php endif; ?>
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
                        $isStudentStep = ($i >= 4);
                        $isNextStep = $isStudentStep && ($i == $completedSteps + 1);
                ?>

                    <button 
                        class="step-btn btn btn-sm student-step 
                        <?php echo $isDone ? 'btn-'.$data['color'] : 'btn-outline-'.$data['color']; ?>"
                        data-id="<?php echo $row['id']; ?>"
                        data-step="<?php echo $i; ?>"
                        title="<?php echo $data['title']; ?> (<?php echo $isStudentStep ? 'Student' : 'Admin'; ?>)"
                        <?php echo ($isStudentStep && !$isDone && $isNextStep) ? '' : 'disabled'; ?>
                    >

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
                <?php 
                    if (!$student_agency_name) {
                        echo "📝 No MOA record yet. Complete your internship application to get started.";
                    } else {
                        echo "⏳ MOA record is being prepared. Check back soon.";
                    }
                ?>
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

// Student can complete steps 4-7
$(document).on('click', '.student-step', function () {
    const btn = $(this);
    const step = parseInt(btn.data('step'), 10);
    const id = parseInt(btn.data('id'), 10);
    if (isNaN(step) || isNaN(id)) return;

    Swal.fire({
        title: 'Mark this step as completed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const payload = new FormData();
        payload.append('id', id);
        payload.append('step', step);

        fetch('../phpbackend/update-step.php', {
            method: 'POST',
            body: payload
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Update failed');

            btn.removeClass(function(idx, cls) {
                return (cls.match(/btn-outline-\S+/g) || []).join(' ');
            }).addClass('btn-success').prop('disabled', true);

            // Update progress bar
            const row = btn.closest('tr');
            const bar = row.find('.progress-bar');
            const current = parseFloat(bar.attr('aria-valuenow')) || 0;
            const increment = (100 / 7);
            let nextVal = current + increment;
            if (nextVal > 100) nextVal = 100;
            bar.css('width', nextVal + '%').attr('aria-valuenow', nextVal);
            if (nextVal >= 100) {
                bar.addClass('bg-success').text('Processed');
            }
        })
        .catch(err => {
            Swal.fire({
                title: 'Error',
                text: err.message,
                icon: 'error',
                confirmButtonColor: '#0A1D56'
            });
        });
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



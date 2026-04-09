<?php include '../db_connect.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Form Template</title>
    <link rel="stylesheet" href="../css/add-form.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

<form id="addForm" action="../phpbackend/upload-form.php" method="POST" enctype="multipart/form-data">
    <h2>Add Form Template</h2>

    <label>Form Name</label>
    <input type="text" name="template_name" required>

    <label>Description</label>
    <textarea name="template_description" rows="4"></textarea>

    <label>Upload PDF</label>
    <input type="file" name="pdf_file" accept="application/pdf" required>

    <button type="submit">Add Form Template</button>

    <a href="../admin/saforms.php" class="btn-secondary" id="cancelBtn">Cancel</a>
</form>

<script>
// HANDLE FORM SUBMIT (ADD)
document.getElementById("addForm").addEventListener("submit", function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Add Form Template?',
        text: "Make sure all details are correct.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, add it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Optional loading alert
            Swal.fire({
                title: 'Saving...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form after confirmation
            e.target.submit();
        }
    });
});

// HANDLE CANCEL BUTTON
document.getElementById("cancelBtn").addEventListener("click", function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Cancel?',
        text: "Your changes will not be saved.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0A1D56',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel',
        cancelButtonText: 'Stay'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "../saforms.php";
        }
    });
});
</script>

</body>
</html>

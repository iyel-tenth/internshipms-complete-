<?php
// Include the database connection file
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the agency name from the POST request
    $agency_name = trim($_POST['agency_name']);

    // Check if the agency name is not empty
    if (!empty($agency_name)) {
        // Prepare the SQL statement to insert the agency name into the moa_tracking table
        $sql = "INSERT INTO moa_tracking (agency_name) VALUES (?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind the parameter to the SQL statement
            $stmt->bind_param('s', $agency_name);

            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>
                    alert('Agency name successfully added to the database.');
                    window.location.href = '../admin/moa_tracking.php';
                </script>";
                exit;
            } else {
                echo "Error: Could not execute the query. " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error: Could not prepare the query. " . $conn->error;
        }
    } else {
        echo "<script>alert('Please provide an agency name.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add MOA</title>
    <link rel="stylesheet" href="../css/addevent.css">
</head>
<body>

<?php include_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

<form method="POST" onsubmit="return confirmAdd()">
    <h2>Add MOA</h2>

    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>

    <label>Agency Name:</label>
    <input type="text" name="agency_name" required>

    <button type="submit" class="btn btn-success">Add MOA</button>
    <a href="../admin/moa_tracking.php" class="btn btn-secondary" onclick="return confirmCancel()">Cancel</a>
</form>

<script>
function confirmAdd() {
    return confirm("Are you sure you want to add this MOA?");
}

function confirmCancel() {
    return confirm("Are you sure you want to cancel?");
}
</script>

</body>
</html>
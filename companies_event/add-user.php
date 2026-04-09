<?php include_once __DIR__ . '/../includes/sidebar_admin.php'; ?>

<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "internship_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------------------------
// Handle form submission
// ---------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $course_major = $_POST["course_major"] ?? null; // may be null if hidden

    $insert_sql = "INSERT INTO users 
        (first_name, last_name, email, role, course_major)
        VALUES 
        ('$first_name', '$last_name', '$email', '$role', '$course_major')";

    if ($conn->query($insert_sql)) {
        echo "<script>alert('User added successfully!'); window.location='configuration.php';</script>";
        exit;
    } else {
        echo "Error adding user: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/add-user.css" />
    <title>Add User</title>
</head>
<body>
    <form method="POST" action="..admin/users.php" onsubmit="return confirmAdd()">
        <h2>Add User</h2>
        <div class="mb-3">
            <label class="form-label">First Name:</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Last Name:</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Role:</label>
            <select name="role" id="roleSelect" class="form-select" required>
                <option value="" disabled selected>Select role</option>
                <option value="Admin">Admin</option>
                <option value="Instructor">Instructor</option>
                <option value="Employer">Employer</option>
                <option value="Student">Student</option>
            </select>
        </div>

        <div class="mb-3" id="courseDiv">
            <label class="form-label">Course & Major:</label>
            <select name="course_major" class="form-select">
                <option value="" disabled selected>Select course</option>
                <option value="BS Math - PURE">III-BS Mathematics Major in Pure Mathematics</option>
                <option value="BS Math - CIT">III-BS Mathematics Major in Computer Information Technology</option>
                <option value="BS Math - STATS">III-BS Mathematics Major in Statistics</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Add User</button>
        <a href="../adminp.php" class="btn btn-secondary" onclick="return confirmCancel()">Cancel</a>
    </form>

    <script>
    // Confirm before submitting
    function confirmAdd() {
        return confirm("Are you sure you want to add this user?");
    }

    // Confirm before canceling
    function confirmCancel() {
        return confirm("Are you sure you want to cancel? Any changes will be lost.");
    }
        const roleSelect = document.getElementById('roleSelect');
        const courseDiv = document.getElementById('courseDiv');

        function toggleCourseField() {
            const role = roleSelect.value;
            // Show course only if role is Student
            if (role === 'Student') {
                courseDiv.style.display = 'block';
            } else {
                courseDiv.style.display = 'none';
                courseDiv.querySelector('select').value = ''; // reset selection
            }
        }

        // Run on change
        roleSelect.addEventListener('change', toggleCourseField);

        // Run on page load in case role is pre-selected
        window.addEventListener('DOMContentLoaded', toggleCourseField);
    </script>

</body>
</html>

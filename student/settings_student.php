<?php
session_start();
include(__DIR__ . '/../db_connect.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch student data
$stmt = $conn->prepare("SELECT first_name, last_name, email, password FROM student_users WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];

    // Verify current password
    if (!password_verify($current_password, $student['password'])) {
        $error = "Current password is incorrect.";
    } else {
        $update = $conn->prepare("UPDATE student_users SET first_name=?, last_name=?, email=? WHERE student_id=?");
        $update->bind_param("ssss", $first_name, $last_name, $email, $student_id);

        if ($update->execute()) {
            $success = "Profile updated successfully!";
            // Refresh student data
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
        } else {
            $error = "Failed to update profile.";
        }
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($current_password, $student['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $pass_stmt = $conn->prepare("UPDATE student_users SET password=? WHERE student_id=?");
        $pass_stmt->bind_param("ss", $new_password_hashed, $student_id);

        if ($pass_stmt->execute()) {
            $success = "Password changed successfully!";
            // Refresh student data
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
        } else {
            $error = "Failed to change password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings</title>

<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.settings-container {
    max-width: 700px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
}

.settings-container h2 {
    color: #0A1D56;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.btn {
    background: #0A1D56;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.btn:hover {
    background: #162d7a;
}

.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
}

.success {
    background: #d4edda;
    color: #155724;
}

.error {
    background: #f8d7da;
    color: #721c24;
}
</style>
</head>

<body id="body-pd">

<?php include(__DIR__ . '/../includes/sidebar_student.php'); ?>

<div class="main-content">
    <div class="settings-container">

        <h2><i class="fas fa-cog"></i> Settings</h2>

        <?php if (isset($success)) echo "<div class='alert success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert error'>$error</div>"; ?>

        <!-- PROFILE UPDATE -->
        <form method="POST">
            <h3>Profile Information and Privacy</h3>
            <p><i class="fas fa-info-circle"></i> Note: First Name and Last Name cannot be changed. Please contact the administrator for any changes regarding your name.</p>

            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>

            <button type="submit" class="btn">Save Changes</button>
        </form>

        <hr style="margin: 30px 0;">

        <!-- CHANGE PASSWORD -->
        <form method="POST">
            <h3>Change Password</h3>

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <span id="password_match" style="font-size: 12px; margin-top: 5px;"></span>
            </div>

            <button type="submit" name="change_password" class="btn">
                Update Password
            </button>
        </form>

    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('input', function() {
    var newPassword = document.getElementById('new_password').value;
    var confirmPassword = this.value;
    var matchIndicator = document.getElementById('password_match');
    var submitButton = document.querySelector('button[name="change_password"]');
    
    if (confirmPassword === '') {
        matchIndicator.textContent = '';
        matchIndicator.style.color = 'black';
        submitButton.disabled = false;
    } else if (newPassword === confirmPassword) {
        matchIndicator.textContent = '✓ Passwords match';
        matchIndicator.style.color = 'green';
        submitButton.disabled = false;
    } else {
        matchIndicator.textContent = '✗ Passwords do not match';
        matchIndicator.style.color = 'red';
        submitButton.disabled = true;
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    var confirmPassword = document.getElementById('confirm_password').value;
    if (confirmPassword !== '') {
        document.getElementById('confirm_password').dispatchEvent(new Event('input'));
    } else {
        document.getElementById('password_match').textContent = '';
        document.querySelector('button[name="change_password"]').disabled = false;
    }
});
</script>
</body>
</html>
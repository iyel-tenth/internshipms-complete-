<?php
session_start();
include(__DIR__ . '/../db_connect.php');

/* ===============================
   ADMIN ACCESS PROTECTION
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

/* ===============================
   FETCH CURRENT ADMIN DATA
================================ */
$admin_id = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

/* ===============================
   HANDLE FORM SUBMISSION
================================ */
if (isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $current_password = $_POST['current_password'];

    // Verify current password if changing
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $error = "Please enter your current password to change it.";
        } elseif (!password_verify($current_password, $admin['password'])) {
            $error = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name=?, email=?, password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $hashed, $admin_id);

            if ($stmt->execute()) {
                $success = "Profile and password updated successfully!";
                $_SESSION['name'] = $name;
            } else {
                $error = "Failed to update profile.";
            }
        }
    } else {
        // Update without password
        $sql = "UPDATE users SET name=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $admin_id);

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['name'] = $name;
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Settings</title>
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.settings-container {
    max-width: 600px;
    margin: 50px auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
}

.settings-container h2 {
    text-align: center;
    color: #0A1D56;
    margin-bottom: 25px;
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
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 6px;
    background-color: #0A1D56;
    color: white;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
}

.btn:hover {
    background-color: #112b85;
}

.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    text-align: center;
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

<body>

<?php include(__DIR__ . '/../includes/sidebar_admin.php'); ?>

<div class="main-content">
    <div class="settings-container">
        <h2><i class="fas fa-cog"></i> Admin Settings</h2>

        <?php if ($success != ''): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error != ''): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password if changing">
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Leave blank if no change">
            </div>

            <button type="submit" name="update" class="btn">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>
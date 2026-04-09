<?php
session_start();

// Initialize variables to prevent warnings
$register_error = "";
$success = "";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'admin' && $password === '1234') {
        $_SESSION['user'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/login_register.css">

<style>
/* Removed inline CSS as the styles are now in login_register.css */
</style>
</head>

<body>

<div class="container">

    <div class="left">
        <!-- BUBBLES -->
        <div class="bubble bubble1"></div>
        <div class="bubble bubble2"></div>
        <div class="bubble bubble3"></div>
        <div class="bubble bubble4"></div>
        <div class="bubble bubble5"></div>
        <div class="bubble bubble6"></div>
        <div class="bubble bubble7"></div>
        <div class="bubble bubble8"></div>

        <div>
            <div class="badge">
                <img src="logo/PSU.png" alt="Logo">
                <img src="logo/CCS.png" alt="New Logo">
            </div>
            <h1>Internbuddy :</h1>
            <h1>Web-Based Internship Management System</h1>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
        </div>

        <div class="logo-box">
            <img src="logo/MC.png" alt="Logo">
        </div>

        <div class="footer-text">
            Internship records, tracking, and reporting in one system.
        </div>
    </div>

    <div class="right">

        <!-- LOGIN FORM (UNCHANGED, just added name="login") -->
        <form class="login-container" method="POST" id="loginForm">
            <h2>Login</h2>
            <p>Sign in with your institutional account credentials.</p>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <label>USERNAME</label>
            <input type="text" name="username" required>

            <label>PASSWORD</label>
            <input type="password" name="password" required>

            <button type="submit" name="login">LOGIN</button>

            <button type="button" class="switch-btn" onclick="showRegister()">Register</button>

            <div class="privacy">
                By logging in, you agree to the Data Privacy Notice.
            </div>
        </form>

        <!-- REGISTER FORM (NEW) -->
        <form class="login-container hidden" method="POST" id="registerForm">
            <h2>Register</h2>
            <p>Create your account.</p>

            <?php if ($register_error): ?>
                <div class="error"><?php echo $register_error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="color:green; font-size:13px;"> <?php echo $success; ?> </div>
            <?php endif; ?>

            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>FIRST NAME</label>
                    <input type="text" name="firstname" required>
                </div>
                <div style="flex: 1;">
                    <label>LAST NAME</label>
                    <input type="text" name="lastname" required>
                </div>
            </div>

            <label>EMAIL</label>
            <input type="email" name="email" required>

            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>PASSWORD</label>
                    <input type="password" name="password" required>
                </div>
                <div style="flex: 1;">
                    <label>RETYPE PASSWORD</label>
                    <input type="password" name="confirm_password" required>
                </div>
            </div>

            <label>STUDENT ID</label>
            <input type="text" name="student_id" required>

            <label>SELECT YEAR LEVEL / COURSE / MAJOR</label>
            <select name="course" required style="width:100%; padding:12px; border-radius:8px; margin-bottom:15px;">
                <option value="">Select...</option>
                <option>1st Year - BSIT - Web Development</option>
                <option>2nd Year - BSIT - Web Development</option>
                <option>3rd Year - BSIT - Web Development</option>
                <option>4th Year - BSIT - Web Development</option>
                <option>1st Year - BSCS - Software Engineering</option>
                <option>2nd Year - BSCS - Software Engineering</option>
                <option>3rd Year - BSCS - Software Engineering</option>
                <option>4th Year - BSCS - Software Engineering</option>
                <option>1st Year - BSIS - Information Systems</option>
                <option>2nd Year - BSIS - Information Systems</option>
                <option>3rd Year - BSIS - Information Systems</option>
                <option>4th Year - BSIS - Information Systems</option>
            </select>

            <button type="submit" name="register">REGISTER</button>

            <button type="button" class="switch-btn" onclick="showLogin()">Back to Login</button>

            <div class="privacy">
                By registering, you agree to the Data Privacy Notice.
            </div>
        </form>

    </div>

</div>

<!-- JS TO TOGGLE FORMS -->
<script>
function showRegister() {
    document.getElementById('loginForm').classList.add('hidden');
    document.getElementById('registerForm').classList.remove('hidden');
}

function showLogin() {
    document.getElementById('registerForm').classList.add('hidden');
    document.getElementById('loginForm').classList.remove('hidden');
}
</script>

</body>
</html>
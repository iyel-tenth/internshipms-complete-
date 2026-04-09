<?php 

session_start();

$errors = [
  'login' => $_SESSION['login_error'] ?? '',
  'register' => $_SESSION['register_error'] ?? ''
];
$success = $_SESSION['registration_success'] ??'';
$activeForm = $_SESSION['active_form'] ?? 'login';


// Show registration success message if redirected from OTP verification
$registrationSuccessMsg = '';
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
  $registrationSuccessMsg = "<p class='success-message'>Account successfully created! You can now log in.</p>";
}
session_unset();

function showError($error) {
  return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function showSuccess($success) {
  return !empty($success) ? "<p class='success-message'>$success</p>" : '';
}

function isActiveForm($formName, $activeForm) {
  return $formName === $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>INTERNBUDDY.IMS</title>

  <!-- YOUR CSS -->
  <link rel="stylesheet" type="text/css" href="css/login.css">

  <!-- FONT AWESOME (FOR ICONS) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .input-group {
      position: relative;
      margin-bottom: 10px;
    }

    .input-group i {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      color: #888;
    }

    .input-group .left-icon {
      left: 10px;
    }

    .input-group .right-icon {
      right: 10px;
      cursor: pointer;
    }

    .input-group input {
      width: 100%;
      padding: 10px 35px;
      box-sizing: border-box;
    }
  </style>
</head>

<body>

<nav>
  <label class="logo"></label>
</nav>

<!-- LOGIN FORM -->
<div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
  <form action="login_register.php" method="post">
    <h2>LOGIN</h2>
    <?= $registrationSuccessMsg; ?>
    <?= showError($errors['login']); ?>

    <!-- EMAIL -->
    <div class="input-group">
      <i class="fa fa-envelope left-icon"></i>
      <input type="email" name="email" placeholder="Email" required>
    </div>

    <!-- PASSWORD -->
    <div class="input-group">
      <i class="fa fa-lock left-icon"></i>
      <input type="password" name="password" id="loginPassword" placeholder="Password" required>
      <i class="fa fa-eye right-icon" onclick="togglePassword('loginPassword', this)"></i>
    </div>

    <button type="submit" name="login">Login</button>

    <label class="highlighted">
      Don't have an account? 
      <a href="#" onclick="showForm('register-form')">Register</a>
    </label>
  </form> 
</div>


<!-- REGISTER FORM -->
<div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
  <form action="login_register.php" method="post">
    <h2>REGISTRATION</h2>

    <?= showError($errors['register']); ?>
    <?= showSuccess($success); ?>

    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>

    <!-- EMAIL -->
    <div class="input-group">
      <i class="fa fa-envelope left-icon"></i>
      <input type="email" name="email" placeholder="Email" required>
    </div>

    <!-- PASSWORD -->
    <div class="input-group">
      <i class="fa fa-lock left-icon"></i>
      <input type="password" name="password" id="registerPassword" placeholder="Password" required>
      <i class="fa fa-eye right-icon" onclick="togglePassword('registerPassword', this)"></i>
    </div>

    <!-- CONFIRM PASSWORD -->
    <div class="input-group">
      <i class="fa fa-lock left-icon"></i>
      <input type="password" name="confirm_password" id="confirmPassword" placeholder="Retype Password" required>
      <i class="fa fa-eye right-icon" onclick="togglePassword('confirmPassword', this)"></i>
    </div>

    <input type="hidden" name="role" value="Student">

    <input type="text" name="student_id" placeholder="Student ID" required>
    <select name="course_major" required>
      <option value="">Select Year Level Course and Major</option>
      <option value="BS MATH - PURE">III-BS Mathematics Major in Pure Mathematics</option>
      <option value="BS MATH - CIT">III-BS Mathematics Major in Computer Information Technology</option>
      <option value="BS MATH - STATS">III-BS Mathematics Major in Statistics</option>
    </select>

    <button type="submit" name="register">Register</button>

    <label class="highlighted">
      Already have an account? 
      <a href="#" onclick="showForm('login-form')">Sign in</a>
    </label>
  </form>
</div>

<script src="script.js"></script>

<script>
function togglePassword(inputId, icon) {
  const input = document.getElementById(inputId);

  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}
</script>

</body>
</html>
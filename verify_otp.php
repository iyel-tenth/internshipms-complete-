<?php 
$message = "";

session_start();
$message = "";

if (isset($_POST["verify_email"])) {
    $email = trim($_POST['email']);
    $verification_code = trim($_POST["verification_code"]);

    if (!isset($_SESSION['pending_registration']) || $_SESSION['pending_registration']['email'] !== $email) {
        $message = "<p class='error'>Verification failed. Please start registration again.</p>";
    } else {
        $pending = $_SESSION['pending_registration'];
        if (time() > $pending['expires_at']) {
            unset($_SESSION['pending_registration']);
            $message = "<p class='error'>Verification code expired. Please register again.</p>";
        } elseif ($pending['verification_code'] != $verification_code) {
            $message = "<p class='error'>Verification failed. Please check your code and try again.</p>";
        } else {
            $conn = mysqli_connect("localhost","root","","internship_db");
            if (!$conn) {
                die("Database connection failed: " . mysqli_connect_error());
            }
            $is_verified = 1;
            if ($pending['role'] === 'Student') {
                $stmt = $conn->prepare("INSERT INTO student_users (student_id, first_name, last_name, email, password, role, course_major, is_verified, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssssii', $pending['student_id'], $pending['first_name'], $pending['last_name'], $pending['email'], $pending['password'], $pending['role'], $pending['course_major'], $is_verified, $pending['verification_code']);
            } else {
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, course_major, is_verified, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssii', $pending['first_name'], $pending['last_name'], $pending['email'], $pending['password'], $pending['role'], $pending['course_major'], $is_verified, $pending['verification_code']);
            }
            if ($stmt->execute()) {
                unset($_SESSION['pending_registration']);
                // Redirect to login.php with success note
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "<p class='error'>Registration failed. Please try again.</p>";
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Email Verification</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    margin: 0;
    padding: 0;
    font-family: Poppins, sans-serif;
    background: #c5c5c5;
}

nav {
    position: fixed;
    background-color: midnightblue;
    height: 70px;
    width: 100%;
    z-index: 1;
    top: 0;
}

.wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.card-container {
    width: 60%;
    max-width: 600px;
    background: #ffffff;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    text-align: center;
}

.card-container h2 {
    margin-bottom: 15px;
    font-size: 28px;
    color: midnightblue;
}

.card-container .text {
    color: #666;
    font-size: 15px;
    margin-bottom: 30px;
    line-height: 1.5;
}

.card-container input[type="text"] {
    width: 100%;
    padding: 12px;
    border: 2px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    margin-bottom: 20px;
    outline: none;
    transition: 0.3s;
}

.card-container input[type="text"]:focus {
    border-color: midnightblue;
}

.card-container input[type="submit"] {
    background: midnightblue;
    color: #fff;
    padding: 12px 25px;
    font-size: 16px;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: 0.3s ease;
}

.card-container input[type="submit"]:hover {
    background: #0c0f54;
}

.success {
    color: green;
    font-weight: 600;
    margin-top: 15px;
}

.error {
    color: red;
    font-weight: 600;
    margin-top: 15px;
}

.card-container a {
    color: midnightblue;
    text-decoration: none;
    font-weight: 600;
}

.card-container a:hover {
    text-decoration: underline;
}

@media (max-width: 992px) {
    .card-container {
        width: 80%;
    }
}

@media (max-width: 600px) {
    .card-container {
        width: 95%;
        padding: 25px;
    }
}
</style>
</head>

<body>

<nav></nav>

<div class="wrapper">
    <div class="card-container">
        <form method="POST" action="verify_otp.php">
            <h2>Get Code From Your Email</h2>
            <p class="text">
                We want to make sure it's really you. Enter the verification code sent to your email.
            </p>

            <input type="hidden" name="email" 
                   value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" 
                   required>

            <input type="text" name="verification_code" placeholder="Enter verification code" required>
            <input type="submit" name="verify_email" value="Verify Email">
        </form>

        <?php echo $message; ?>
    </div>
</div>

</body>
</html>
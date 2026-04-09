<?php
ini_set('session.gc_maxlifetime', 28800);
session_set_cookie_params(28800);
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'db_connect.php';

if (isset($_POST["register"])) {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"] ?? 'Student';
    $verification_code = rand(100000, 999999);
    $is_verified = 0;
    $course_major = $_POST["course_major"];
    $expires_at = time() + 600; // 10 minutes

    $pending_registration = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'course_major' => $course_major,
        'verification_code' => $verification_code,
        'expires_at' => $expires_at
    ];
    if ($role === "Student") {
        $pending_registration['student_id'] = $_POST["student_id"];
    }
    $_SESSION['pending_registration'] = $pending_registration;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'kathsoriano29@gmail.com'; // your Gmail
        $mail->Password = 'dwjlnrivuenktuqg'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        // Allow self-signed certs and disable peer verification (for local/dev only)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('kathsoriano29@gmail.com', 'InternBuddy IMS');
        $mail->addAddress($email, "$first_name $last_name");

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "
            <h2>Hello $first_name $last_name!</h2>
            <p>Your One-Time Password (OTP) is:</p>
            <h1 style='color:#2d89ef;'>$verification_code</h1>
            <p>Enter this code on the website to verify your account.</p>
            <p>This code will expire after 10 minutes.</p>
        ";

        $mail->send();

        header("Location: verify_otp.php?email=" . urlencode($email));
        exit();
    } catch (Exception $e) {
        echo "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
    }
}

if (isset($_POST['login'])) {


    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check users table first
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    $table = "users";

    if ($result->num_rows === 0) {
        // If not found, check student_users table
        $result = $conn->query("SELECT * FROM student_users WHERE email = '$email'");
        $table = "student_users";
    }

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // REGENERATE SESSION (security)
            session_regenerate_id(true);

            // ==============================
            // STORE SESSION PROPERLY
            // ==============================
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            // IMPORTANT PART
            if ($table === "student_users") {
                $_SESSION['user_id'] = $user['student_id']; // student_id
            } else {
                $_SESSION['user_id'] = $user['id']; // id
            }

            // Update last login
            if ($table === "student_users") {
                $conn->query("UPDATE student_users SET last_login = NOW() WHERE student_id = '{$user['student_id']}'");
            } else {
                $conn->query("UPDATE users SET last_login = NOW() WHERE id = '{$user['id']}'");
            }

            // ==============================
            // REDIRECT BY ROLE
            // ==============================
            if ($user['role'] === 'Admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'Instructor') {
                header("Location: instructor.php");
            } elseif ($user['role'] === 'Employer') {
                header("Location: employer.php");
            } elseif ($user['role'] === 'Student') {
                header("Location: student/dashboard.php");
            }

            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password.';
    $_SESSION['active_form'] = 'login';
    header("Location: login.php");
    exit();
}


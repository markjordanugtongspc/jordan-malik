<?php
session_start();
include 'backend\database\config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['attempts'] = 0;
        $_SESSION['temp_user_email'] = $email;
        header("Location: captcha_verify.php");
    } else {
        $_SESSION['attempts']++;

        if ($_SESSION['attempts'] >= 3) {
            $_SESSION['attempts'] = 0;
            sendRecoveryEmail($email); 
            $_SESSION['login_error'] = "Too many failed attempts. Check your email to recover your account.";
        } else {
            $_SESSION['login_error'] = "Incorrect password. Attempt " . $_SESSION['attempts'] . " of 3.";
        }

        header("Location: login.php");
    }
} else {
    $_SESSION['login_error'] = "Email not found.";
    header("Location: login.php");
}

function sendRecoveryEmail($email)
{
    $to = $email;
    $subject = "⚠ Login Alert - Action Required";
    $message = "Hi there,\n\nWe detected multiple failed login attempts to your account.\n\nIf this was not you, please reset your password immediately:\n\nhttp://yourdomain.com/reset_password.php?email=" . urlencode($email) . "\n\nRegards,\nSecurity Team";
    $headers = "From: noreply@yourdomain.com";

    mail($to, $subject, $message, $headers);
}
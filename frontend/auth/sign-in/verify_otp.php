<?php
session_start();

if (!isset($_SESSION['otp_email']) || !isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expiry'])) {
    $_SESSION['login_error'] = "OTP session expired. Please try logging in again.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedOtp = $_POST['otp'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($email !== $_SESSION['otp_email']) {
        $_SESSION['login_error'] = "Email mismatch detected. Please try again.";
        header("Location: login.php");
        exit();
    }

    if (time() > $_SESSION['otp_expiry']) {
        unset($_SESSION['otp_email'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);
        $_SESSION['login_error'] = "OTP has expired. Please request a new one.";
        header("Location: login.php");
        exit();
    }

    if ($submittedOtp === $_SESSION['otp_code']) {
        // OTP is correct, set security recovery email and redirect to security verification
        $_SESSION['security_recovery_email'] = $_SESSION['otp_email'];
        
        // Clear OTP data
        unset($_SESSION['otp_code'], $_SESSION['otp_expiry']);
        
        // Redirect to security verification
        header("Location: ../security/security.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Invalid OTP. Please try again.";
        header("Location: login.php");
        exit();
    }
}

header("Location: login.php");
exit();
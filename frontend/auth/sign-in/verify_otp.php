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

        $_SESSION['user_email'] = $_SESSION['otp_email'];
        unset($_SESSION['otp_email'], $_SESSION['otp_code'], $_SESSION['otp_expiry']);
        header("Location: ../../dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = "Invalid OTP. Please try again.";
        header("Location: login.php");
        exit();
    }
}

header("Location: login.php");
exit();
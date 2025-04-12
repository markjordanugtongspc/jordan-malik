<?php
session_start();

if (!isset($_SESSION['temp_user_email'])) {
    header("Location: ../../frontend/auth/sign-in/login.php");
    exit();
}

$userInput = $_POST['captcha_input'] ?? '';
$captchaAnswer = $_SESSION['captcha_answer'] ?? '';
$isRobotChecked = isset($_POST['notRobot']);

if (!$isRobotChecked) {
    $_SESSION['register_error'] = "Please confirm you're not a robot.";
    header("Location: ../../../frontend/auth/captcha/captcha_verify.php");
    exit();
}

if (strtoupper($userInput) !== strtoupper($captchaAnswer)) {
    $_SESSION['register_error'] = "CAPTCHA verification failed.";
    header("Location: ../../../frontend/auth/captcha/captcha_verify.php");
    exit();
}

unset($_SESSION['captcha_answer']);
$_SESSION['captcha_passed'] = true;
header("Location: ../../frontend/auth/security/security.php");
exit();
?>
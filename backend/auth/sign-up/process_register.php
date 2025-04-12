<?php
session_start();
include '../../database/config.php'; 

$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$security_code = $_POST['security_code'] ?? '';

if (!$conn) {
    $_SESSION['register_error'] = "Database connection failed.";
    header("Location: ../../../frontend/auth/sign-up/register.php"); 
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    $_SESSION['register_error'] = "Database query failed.";
    header("Location: ../../../frontend/auth/sign-up/register.php"); 
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['register_error'] = "Email is already registered.";
    header("Location: ../../../frontend/auth/sign-up/register.php"); 
    exit();
}

$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$_SESSION['temp_registration_data'] = [
    'fullname' => $fullname,
    'email' => $email,
    'hashed_password' => $hashed_password,
    'security_code' => $security_code
];
$_SESSION['temp_user_email'] = $email;

header("Location: ../../../frontend/auth/captcha/captcha_verify.php"); 
exit();
?>
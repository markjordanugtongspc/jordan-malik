<?php
session_start();

// Check if CAPTCHA is verified
if (!isset($_SESSION['captcha_passed'])) {
    header("Location: ../../../frontend/auth/captcha/captcha_verify.php");
    exit();
}

// Check if temporary registration data exists
if (!isset($_SESSION['temp_registration_data'])) {
    $_SESSION['register_error'] = "Registration data not found.";
    header("Location: ../../../frontend/auth/sign-up/register.php");
    exit();
}

// Include database configuration
include '../../database/config.php';

if (!$conn) {
    $_SESSION['register_error'] = "Database connection failed.";
    header("Location: ../../../frontend/auth/sign-up/register.php");
    exit();
}

// Extract user data from session
$data = $_SESSION['temp_registration_data'];
$fullname = $data['fullname'];
$email = $data['email'];
$hashedPassword = $data['hashed_password'];
$securityCode = $data['security_code'] ?? '';

// Final check to ensure email is not already registered
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['register_error'] = "Email is already registered.";
    header("Location: ../../../frontend/auth/sign-up/register.php");
    exit();
}

// Insert the new user into the database
$insertStmt = $conn->prepare("INSERT INTO users (fullname, email, password, security_code) VALUES (?, ?, ?, ?)");
$insertStmt->bind_param("ssss", $fullname, $email, $hashedPassword, $securityCode);

if ($insertStmt->execute()) {
    // Registration successful - clean up session and authenticate the user
    unset($_SESSION['temp_registration_data'], $_SESSION['captcha_passed'], $_SESSION['temp_user_email']);
    
    // Set authentication status
    $_SESSION['authenticated'] = true;
    $_SESSION['user_email'] = $email;
    
    // Redirect to dashboard
    header("Location: ../../../frontend/dashboard.php");
    exit();
} else {
    // Registration failed
    $_SESSION['register_error'] = "Failed to create your account. Please try again.";
    header("Location: ../../../frontend/auth/sign-up/register.php");
    exit();
}
?> 
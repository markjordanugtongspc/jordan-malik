<?php
session_start();

// Check if security recovery email is set
if (!isset($_SESSION['security_recovery_email'])) {
    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}

// Include database configuration
include '../../database/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['security_recovery_email'];
    $securityCode = trim($_POST['security_code'] ?? '');

    if (empty($securityCode)) {
        header("Location: ../../../frontend/auth/security/security.php?error=empty");
        exit();
    }

    // Verify security code against database
    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ? AND security_code = ?");
    $stmt->bind_param("ss", $email, $securityCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        // Security code matches - set session for password reset
        $_SESSION['security_verified'] = true;
        $_SESSION['reset_email'] = $email;
        
        // Redirect to password reset form
        header("Location: ../../../frontend/auth/resetpass/reset_password_form.php");
        exit();
    } else {
        // Security code doesn't match
        header("Location: ../../../frontend/auth/security/security.php?error=invalid");
        exit();
    }
} else {
    // Not a POST request
    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}
?> 
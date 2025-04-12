<?php
session_start();

// Check if security has been verified
if (!isset($_SESSION['security_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}

// Include database configuration
include '../../database/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $repeatPassword = $_POST['repeat_password'] ?? '';
    
    // Validate email matches session
    if ($email !== $_SESSION['reset_email']) {
        $_SESSION['reset_error'] = "Email mismatch detected. Please try again.";
        header("Location: ../../../frontend/auth/resetpass/reset_password_form.php");
        exit();
    }
    
    // Validate passwords match
    if ($newPassword !== $repeatPassword) {
        $_SESSION['reset_error'] = "Passwords do not match.";
        header("Location: ../../../frontend/auth/resetpass/reset_password_form.php");
        exit();
    }
    
    // Validate password strength
    if (strlen($newPassword) < 8) {
        $_SESSION['reset_error'] = "Password must be at least 8 characters long.";
        header("Location: ../../../frontend/auth/resetpass/reset_password_form.php");
        exit();
    }
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Password updated successfully
        
        // Clear security verification sessions
        unset($_SESSION['security_verified'], $_SESSION['reset_email'], $_SESSION['security_recovery_email']);
        
        // Set authenticated session
        $_SESSION['authenticated'] = true;
        $_SESSION['user_email'] = $email;
        
        // Redirect to dashboard
        header("Location: ../../../frontend/dashboard.php");
        exit();
    } else {
        // Password update failed
        $_SESSION['reset_error'] = "Failed to update password. Please try again.";
        header("Location: ../../../frontend/auth/resetpass/reset_password_form.php");
        exit();
    }
} else {
    // Not a POST request
    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}
?> 
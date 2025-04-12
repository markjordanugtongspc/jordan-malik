<?php
session_start();
include 'backend\database\config.php'; 

$email = $_POST['email'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if ($email && $new_password) {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format.');
    }

    if (strlen($new_password) < 8) {
        die('Password must be at least 8 characters long.');
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo 'Your password has been reset successfully. <a href="login.php">Login</a>';
    } else {
        echo 'Failed to reset the password. Please try again.';
    }
} else {
    echo 'Invalid request.';
}
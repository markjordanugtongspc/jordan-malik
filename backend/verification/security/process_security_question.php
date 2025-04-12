// This file is no longer needed as security verification now uses process_security_verification.php
// Redirecting any requests to the login page
<?php
session_start();
header("Location: ../../../frontend/auth/sign-in/login.php");
exit();
?>
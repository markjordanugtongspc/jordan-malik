<?php
session_start();

if (!isset($_SESSION['captcha_passed']) || !isset($_SESSION['temp_registration_data'])) {
    header("Location: ../../../frontend/auth/captcha/captcha_verify.php");
    exit();
}

include '../../database/config.php';

$validDog = "blacky";
$validSchool = "SPC";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dog = trim($_POST['security_dog'] ?? '');
    $school = trim($_POST['security_school'] ?? '');

    if (empty($dog) || empty($school)) {
        header("Location: ../../../frontend/auth/security/security.php?error=empty");
        exit();
    }

    if (strcasecmp($dog, $validDog) !== 0 || strcasecmp($school, $validSchool) !== 0) {
        header("Location: ../../../frontend/auth/security/security.php?error=invalid");
        exit();
    }

    $data = $_SESSION['temp_registration_data'];
    $fullname = $data['fullname'];
    $email = $data['email'];
    $hashedPassword = $data['hashed_password'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = "Email is already registered.";
        header("Location: ../../../frontend/auth/sign-up/register.php");
        exit();
    }

    $insertStmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $fullname, $email, $hashedPassword);

    if ($insertStmt->execute()) {

        unset($_SESSION['temp_registration_data'], $_SESSION['captcha_passed'], $_SESSION['temp_user_email']);
        $_SESSION['authenticated'] = true;
        $_SESSION['user_email'] = $email;
        header("Location: ../../../frontend/dashboard.php");
        exit();
    } else {
        $_SESSION['security_attempt'] = true;
        header("Location: ../../../frontend/auth/security/security.php?error=database_error");
        exit();
    }
}
?>
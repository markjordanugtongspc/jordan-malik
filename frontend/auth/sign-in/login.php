<?php
session_start();

require_once __DIR__ . '/../../../backend/database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {

    if (!isset($_SESSION['otp_email']) || !isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expiry'])) {
        $_SESSION['login_error'] = "OTP session expired. Please try again.";
        header("Location: login.php");
        exit();
    }

    if ($_POST['otp'] === $_SESSION['otp_code'] && time() <= $_SESSION['otp_expiry']) {

        $_SESSION['show_password_reset'] = true;
    } else {
        $_SESSION['login_error'] = "Invalid OTP. Please try again.";
    }
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    if (!isset($_SESSION['otp_email'])) {
        $_SESSION['login_error'] = "Session expired. Please start over.";
        header("Location: login.php");
        exit();
    }

    $new_password = $_POST['new_password'];
    $repeat_password = $_POST['repeat_password'];

    if ($new_password !== $repeat_password) {
        $_SESSION['login_error'] = "Passwords don't match.";
        $_SESSION['show_password_reset'] = true;
        header("Location: login.php");
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $_SESSION['otp_email']);

    if ($stmt->execute()) {
        $_SESSION['login_success'] = "Password updated successfully. Please login.";
        unset($_SESSION['otp_email'], $_SESSION['otp_code'], $_SESSION['otp_expiry'], $_SESSION['show_password_reset']);
    } else {
        $_SESSION['login_error'] = "Failed to update password. Please try again.";
        $_SESSION['show_password_reset'] = true;
    }
    header("Location: login.php");
    exit();
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

$success = $_SESSION['login_success'] ?? ''; 
unset($_SESSION['login_success']);

$showOtpField = isset($_SESSION['otp_email']) && !isset($_SESSION['show_password_reset']);
$showPasswordReset = isset($_SESSION['show_password_reset']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../../frontend/auth/css/auth-styles.css">
</head>
<body>
    <div class="auth-container" id="login-container">
        <div class="auth-form">
            <h2 class="auth-title"><?= $showPasswordReset ? 'Reset Password' : 'Login' ?></h2>

            <?php if ($showPasswordReset): ?>
            <form action="login.php" method="POST" class="auth-form-content">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['otp_email'] ?? '') ?>">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="repeat_password" placeholder="Repeat Password" required>
                <input type="submit" value="Update Password">
            </form>
            <?php else: ?>
            <form action="<?= $showOtpField ? 'login.php' : '../../../backend/auth/sign-in/process_login.php' ?>" method="POST" class="auth-form-content">
                <input type="email" name="email" placeholder="Email" required 
                       value="<?= htmlspecialchars($_SESSION['otp_email'] ?? '') ?>" 
                       <?= $showOtpField ? 'readonly' : '' ?>>

                <?php if (!$showOtpField): ?>
                    <input type="password" name="password" placeholder="Password" required>
                <?php endif; ?>

                <?php if ($showOtpField): ?>
                <div class="otp-container">
                    <p>Enter OTP sent to your email:</p>
                    <input type="text" name="otp" placeholder="123456" required maxlength="6" class="otp-input">
                </div>
                <?php endif; ?>

                <input type="submit" value="<?= $showOtpField ? 'Verify OTP' : 'Login' ?>">
            </form>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="auth-message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="auth-message success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <a href="../sign-up/register.php" class="auth-switch-link">Create a new account</a>
        </div>

        <div class="auth-animation">
            <div class="floating-particle" style="--delay:0s; --size:80px; --left:10%; --duration:15s"></div>
            <div class="floating-particle" style="--delay:1s; --size:120px; --left:30%; --duration:20s"></div>
            <div class="floating-particle" style="--delay:2s; --size:60px; --left:50%; --duration:12s"></div>
            <div class="floating-particle" style="--delay:3s; --size:100px; --left:70%; --duration:18s"></div>
            <div class="floating-particle" style="--delay:4s; --size:90px; --left:90%; --duration:25s"></div>
        </div>
    </div>

    <script src="auth-animations.js"></script>
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: '<?= $showPasswordReset ? "Password Error" : ($showOtpField ? "OTP Error" : "Login Error") ?>',
            text: '<?= addslashes($error) ?>',
            background: 'var(--auth-surface)',
            color: 'var(--auth-text)',
            confirmButtonColor: 'var(--auth-primary)'
        });
    </script>
    <?php endif; ?>
</body>
</html>
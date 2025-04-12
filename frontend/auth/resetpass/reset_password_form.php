<?php
session_start();

// Check if security has been verified
if (!isset($_SESSION['security_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: ../sign-in/login.php");
    exit();
}

$email = $_SESSION['reset_email'];
$error = $_SESSION['reset_error'] ?? '';
unset($_SESSION['reset_error']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="../../../frontend/auth/css/auth-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="auth-container" id="reset-container">
        <div class="auth-form">
            <h2 class="auth-title">Reset Password</h2>
            <form action="../../../backend/verification/security/process_password_reset.php" method="POST" class="auth-form-content">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="repeat_password" placeholder="Repeat New Password" required>
                <input type="submit" value="Reset Password">
            </form>
            <?php if ($error): ?>
                <div class="auth-message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="auth-instruction">
                Please create a new strong password for your account.
            </div>
        </div>
        
        <div class="auth-animation">
            <div class="floating-particle" style="--delay:0s; --size:80px; --left:10%; --duration:15s"></div>
            <div class="floating-particle" style="--delay:1s; --size:120px; --left:30%; --duration:20s"></div>
            <div class="floating-particle" style="--delay:2s; --size:60px; --left:50%; --duration:12s"></div>
            <div class="floating-particle" style="--delay:3s; --size:100px; --left:70%; --duration:18s"></div>
            <div class="floating-particle" style="--delay:4s; --size:90px; --left:90%; --duration:25s"></div>
        </div>
    </div>
    
    <script src="../auth-animations.js"></script>
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Password Reset Error',
            text: '<?= addslashes($error) ?>',
            background: 'var(--auth-surface)',
            color: 'var(--auth-text)',
            confirmButtonColor: 'var(--auth-primary)'
        });
    </script>
    <?php endif; ?>
</body>
</html> 
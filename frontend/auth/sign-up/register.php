<?php
session_start();
$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../../../frontend/auth/css/auth-styles.css">
</head>
<body>
    <div class="auth-container" id="register-container">
        <div class="auth-form">
            <h2 class="auth-title">Register</h2>
            <form action="../../../backend/auth/sign-up/process_register.php" method="POST" class="auth-form-content">
                <input type="text" name="fullname" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="security_code" placeholder="Security Code" required>
                <input type="submit" value="Register">
            </form>
            <?php if ($error): ?>
                <div class="auth-message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <a href="../sign-in/login.php" class="auth-switch-link">Already have an account?</a>
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
</body>
</html>
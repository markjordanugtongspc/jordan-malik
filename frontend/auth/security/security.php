<?php
session_start();

// Check if this is a security recovery or captcha passed
$isRecovery = isset($_SESSION['security_recovery_email']);

// If it's not a recovery attempt and captcha hasn't been passed, redirect to captcha
if (!$isRecovery && !isset($_SESSION['captcha_passed'])) {
    header("Location: ../captcha/captcha_verify.php");
    exit();
}

// If user is already authenticated, redirect to dashboard
if (isset($_SESSION['authenticated'])) {
    header("Location: ../../dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Verification</title>
    <style>
        :root{--dashboard-primary:#7c3aed;--dashboard-primary-dark:#5b21b6;--dashboard-background:#0f172a;--dashboard-surface:#1e293b;--dashboard-text:#f8fafc;--dashboard-text-secondary:#94a3b8;--dashboard-error:#ef4444;-success:#10b981;--dashboard-border:#334155}body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background-color:var(--dashboard-background);display:flex;justify-content:center;align-items:center;height:100vh;margin:0;color:var(--dashboard-text)}.container{background:var(--dashboard-surface);padding:30px;border-radius:10px;box-shadow:0 0 15px rgb(0 0 0 / .1);width:100%;max-width:500px}.text{font-size:20px;margin-bottom:20px;color:var(--dashboard-primary)}input[type="text"]{width:100%;padding:12px;border:1px solid var(--dashboard-border);border-radius:5px;margin-bottom:20px;font-size:16px;background-color:rgb(30 41 59 / .7);color:var(--dashboard-text)}.error{color:var(--dashboard-error);margin-bottom:10px}input[type="submit"]{background-color:var(--dashboard-success);color:var(--dashboard-text);padding:10px 15px;border:none;border-radius:5px;cursor:pointer}input[type="submit"]:hover{background-color:#0f9d68}.ws{position:fixed;font-size:20px;color:rgb(124 58 237 / .7);display:none;font-weight:100;opacity:.5}.top-left{top:10px;left:10px}.top-right{top:10px;right:10px}.bottom-left{bottom:10px;left:10px}.bottom-right{bottom:10px;right:10px}
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text"><?= $isRecovery ? 'Account Recovery Verification' : 'Security Verification' ?></h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?php
                switch($_GET['error']) {
                    case 'empty':
                        echo 'Please enter your security code.';
                        break;
                    case 'invalid':
                        echo 'Incorrect security code. Please try again.';
                        break;
                    case 'database_error':
                        echo 'A database error occurred. Please try again.';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <form action="../../../backend/verification/security/process_security_verification.php" method="POST">
            <?php if ($isRecovery): ?>
                <p>Please enter your security code to recover your account:</p>
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['security_recovery_email']) ?>">
            <?php else: ?>
                <p>Please verify your security code:</p>
            <?php endif; ?>
            
            <input type="text" name="security_code" required placeholder="Enter your security code">
            <input type="submit" value="<?= $isRecovery ? 'Recover Account' : 'Verify' ?>">
        </form>
    </div>

    <div class="ws top-left">@Wizzy_</div>
    <div class="ws top-right">@Wizzy_</div>
    <div class="ws bottom-left">@Wizzy_</div>
    <div class="ws bottom-right">@Wizzy_</d>

    <script>
        function sW(){const wsElements=document.querySelectorAll('.ws');let index=0;setInterval(()=>{wsElements.forEach(ws=>ws.style.display='none');wsElements[index].style.display='block';index=(index+1)%wsElements.length},6000)}
        sW()
    </script>
</body>
</html>
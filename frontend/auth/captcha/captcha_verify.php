<?php
session_start();

if (!isset($_SESSION['temp_user_email'])) {
    header("Location: ../../frontend/auth/sign-in/login.php");
    exit();
}

if (!isset($_SESSION['captcha_answer'])) {
    $_SESSION['captcha_answer'] = generateCaptcha();
}

function generateCaptcha($length = 6) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $captcha;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userInput = $_POST['captcha_input'] ?? '';
    $isRobotChecked = isset($_POST['notRobot']);
    $storedCaptcha = $_SESSION['captcha_answer'] ?? '';

    if (!$isRobotChecked) {
        $_SESSION['captcha_error'] = "Please confirm you're not a robot.";
    } elseif (strtoupper($userInput) !== strtoupper($storedCaptcha)) {
        $_SESSION['captcha_error'] = "CAPTCHA does not match.";
        $_SESSION['captcha_answer'] = generateCaptcha();
    } else {
        unset($_SESSION['captcha_error']);
        $_SESSION['captcha_passed'] = true;
        header("Location: ../../../backend/auth/sign-up/complete_registration.php");
        exit();
    }

    header("Location: captcha_verify.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAPTCHA Verification</title>
    <style>
        :root {
            --dashboard-primary: #7c3aed;
            --dashboard-primary-dark: #5b21b6;
            --dashboard-background: #0f172a;
            --dashboard-surface: #1e293b;
            --dashboard-text: #f8fafc;
            --dashboard-text-secondary: #94a3b8;
            --dashboard-error: #ef4444;
            --dashboard-success: #10b981;
            --dashboard-border: #334155;
        }

        body {
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at center, var(--dashboard-background), #000);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dashboard-text);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid var(--dashboard-primary);
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 0 20px var(--dashboard-primary);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: var(--dashboard-primary);
            text-shadow: 0 0 10px var(--dashboard-primary);
        }
        .captcha-box {
            font-size: 28px;
            letter-spacing: 6px;
            background: #111;
            color: var(--dashboard-primary);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 0 10px var(--dashboard-primary);
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            pointer-events: none;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            transition: 0.3s ease;
        }
        input[type="text"] {
            background-color: #222;
            color: var(--dashboard-primary);
            border: 1px solid var(--dashboard-primary);
        }
        input[type="submit"] {
            background: linear-gradient(90deg, var(--dashboard-primary), var(--dashboard-primary-dark), var(--dashboard-primary));
            color: var(--dashboard-text);
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 0 10px var(--dashboard-primary);
        }
        input[type="submit"]:hover {
            box-shadow: 0 0 20px var(--dashboard-primary), 0 0 40px var(--dashboard-primary-dark);
        }
        .checkbox-container {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
        }
        label {
            font-size: 14px;
        }
        @media (max-width: 500px) {
            .captcha-box {
                font-size: 20px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['captcha_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                text: '<?= addslashes($_SESSION['captcha_error']) ?>',
                background: 'var(--dashboard-background)',
                color: 'var(--dashboard-text)',
                confirmButtonColor: 'var(--dashboard-primary)',
                customClass: {
                    popup: 'captcha-swal-popup',
                    container: 'captcha-swal-container'
                },
                position: 'center',
                backdrop: `
                    rgba(0,0,0,0.7)
                    center
                    no-repeat
                `,
                timer: 4000,
                timerProgressBar: true,
                showConfirmButton: true,
                willClose: () => {
                    console.log('SweetAlert closed automatically after 4 seconds');
                    window.location.reload();
                }
            });
            <?php unset($_SESSION['captcha_error']); ?>
            <?php endif; ?>
        });

        function validateCaptchaForm() {
            const captchaInput = document.forms["captchaForm"]["captcha_input"].value;
            const robotChecked = document.getElementById("notRobot").checked;

            if (!robotChecked) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "Please confirm you're not a robot.",
                    background: 'var(--dashboard-background)',
                    color: 'var(--dashboard-text)',
                    confirmButtonColor: 'var(--dashboard-primary)',
                    customClass: {
                        popup: 'captcha-swal-popup',
                        container: 'captcha-swal-container'
                    },
                    position: 'center',
                    backdrop: `
                        rgba(0,0,0,0.7)
                        center
                        no-repeat
                    `,
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    willClose: () => {
                        window.location.reload();
                    }
                });
                return false;
            }

            if (captchaInput.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: "Please enter the CAPTCHA code.",
                    background: 'var(--dashboard-background)',
                    color: 'var(--dashboard-text)',
                    confirmButtonColor: 'var(--dashboard-primary)',
                    customClass: {
                        popup: 'captcha-swal-popup',
                        container: 'captcha-swal-container'
                    },
                    position: 'center',
                    backdrop: `
                        rgba(0,0,0,0.7)
                        center
                        no-repeat
                    `,
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    willClose: () => {
                        window.location.reload();
                    }
                });
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Human Verification</h2>

        <form name="captchaForm" action="captcha_verify.php" method="POST" onsubmit="return validateCaptchaForm();">
            <div class="captcha-box"><?= $_SESSION['captcha_answer'] ?></div>
            <input type="text" name="captcha_input" placeholder="Type the CAPTCHA" required>

            <div class="checkbox-container">
                <input type="checkbox" id="notRobot" name="notRobot">
                <label for="notRobot">I'm not a robot</label>
            </div>

            <input type="submit" value="Verify">
        </form>
    </div>
    
    <style>
        /* Custom SweetAlert Styling */
        .captcha-swal-container {
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .captcha-swal-popup {
            border: 1px solid var(--dashboard-primary);
            border-radius: 12px;
            box-shadow: 0 0 20px var(--dashboard-primary);
            animation: swalBounce 0.3s ease-out;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0 !important;
            padding: 1.5rem;
        }
        
        @keyframes swalBounce {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
            70% { transform: translate(-50%, -50%) scale(1.05); }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        
        /* Progress timer styling */
        .swal2-timer-progress-bar {
            background: var(--dashboard-primary) !important;
        }
    </style>
</body>
</html>
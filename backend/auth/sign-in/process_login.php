<?php
session_start();

require_once __DIR__ . '/../../database/config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if (!$conn) {
    $_SESSION['login_error'] = "Database connection failed. Please try again later.";
    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database query preparation failed");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {

            $_SESSION['attempts'] = 0;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            header("Location: ../../../frontend/dashboard.php");
            exit();
        } else {
            handleFailedAttempt($email);
        }
    } else {
        $_SESSION['login_error'] = "Incorrect email or password";
        header("Location: ../../../frontend/auth/sign-in/login.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['login_error'] = "An error occurred. Please try again.";
    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}

function handleFailedAttempt($email) {
    $_SESSION['attempts']++;

    if ($_SESSION['attempts'] >= 3) {
        $_SESSION['attempts'] = 0;
        $otp = generateAndSendOTP($email);
        if ($otp !== false) {
            $_SESSION['otp_email'] = $email;
            $_SESSION['otp_code'] = $otp;
            $_SESSION['otp_expiry'] = time() + 300; 
            $_SESSION['login_error'] = "We've sent an OTP to wizbulatespcccs@ptct.net for verification.";
        } else {
            $_SESSION['login_error'] = "Failed to send OTP. Please try again later.";
        }
    } else {
        $_SESSION['login_error'] = "Incorrect email or password. Attempts remaining: " . (3 - $_SESSION['attempts']);
    }

    header("Location: ../../../frontend/auth/sign-in/login.php");
    exit();
}

function generateAndSendOTP($toEmail) {
    $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

    $mailtrapUsername = '2601c248b738e7';
    $mailtrapPassword = '39dbd0f7c351f6';
    $fromEmail = 'wizbulatespcccs@ptct.net'; 
    $recipientEmail = 'xyxazy@mailto.plus'; 

    $subject = 'Your Login Verification Code';
    $boundary = uniqid('np');

    $textContent = "Your verification code is: $otp\n";
    $textContent .= "This code will expire in 5 minutes.\n\n";
    $textContent .= "If you didn't request this, please secure your account.\n";

    $htmlContent = <<<HTML
<!doctype html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body style="font-family: sans-serif;">
    <div style="display: block; margin: auto; max-width: 600px; padding: 20px; background: #1a1a2e; color: #eee;">
      <h1 style="font-size: 20px; color: #6c5ce7;">Security Verification</h1>
      <p>Your one-time verification code is:</p>
      <div style="font-size: 32px; font-weight: bold; margin: 25px 0; color: #2ecc71;">$otp</div>
      <p>This code will expire in 5 minutes.</p>
      <p style="color: #ff7675;">If you didn't request this code, please secure your account.</p>
    </div>
  </body>
</html>
HTML;

    $emailContent = <<<EOF
From: Wizzy Test Mail <{$fromEmail}>
To: <{$recipientEmail}>
Subject: {$subject}
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary="{$boundary}"

--{$boundary}
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline

{$textContent}

--{$boundary}
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline

{$htmlContent}

--{$boundary}--
EOF;

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => "smtp://sandbox.smtp.mailtrap.io:2525",
        CURLOPT_MAIL_FROM => "<{$fromEmail}>",
        CURLOPT_MAIL_RCPT => ["<{$recipientEmail}>"],
        CURLOPT_USERNAME => $mailtrapUsername,
        CURLOPT_PASSWORD => $mailtrapPassword,
        CURLOPT_USE_SSL => CURLUSESSL_ALL,
        CURLOPT_UPLOAD => true,
        CURLOPT_READDATA => fopen('data://text/plain,' . $emailContent, 'r'),
        CURLOPT_VERBOSE => true,
    ]);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("Mailtrap cURL error: " . curl_error($ch));
        curl_close($ch);
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300) ? $otp : false;
}
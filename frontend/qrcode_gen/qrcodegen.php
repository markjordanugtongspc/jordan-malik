<?php
session_start();
include '../../backend/database/config.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: ./auth/sign-in/login.php?error=You must be logged in to access this page.");
    exit();
}

$user_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT id, fullname, email FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$qrData = array(
    'id' => $user['id'],
    'name' => $user['fullname'],
    'email' => $user['email']
);

$qrContent = urlencode(json_encode($qrData));

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . $qrContent . "&size=300x300&charset-source=UTF-8";
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/qrcode.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../dashboard.php" class="back-button">
                <i class="fas fa-arrow-left"> </i>
            </a>
            <div class="logo">
                <i class="fas fa-cube"></i>
                <span class="logo-text">Jordik</span>
            </div>
        </div>
        <div class="nav-links">
            <div class="theme-toggle" id="theme-toggle">
                <i class="fas fa-moon"></i>
            </div>
            <a href="../backend/auth/sign-out/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <div class="sidebar-menu-item">
                <i class="fas fa-tachometer-alt"></i> <a href="../dashboard.php">Dashboard</a>
            </div>
            <div class="sidebar-menu-item active">
                <i class="fas fa-qrcode"></i> QR Generator
            </div>
            <div class="sidebar-menu-item" id="open-profile-modal">
                <i class="fas fa-user"></i> Profile
            </div>
            <div class="sidebar-menu-item">
                <i class="fas fa-cog"></i> Settings
            </div>
            <div class="sidebar-divider"></div>
            <a href="../backend/auth/sign-out/logout.php" class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="qr-container">
            <div class="qr-layout">
                <div class="qr-reader-section">
                    <div class="video-container">
                        <div class="camera-frame">
                            <video id="qr-video" autoplay muted playsinline></video>
                            <canvas id="qr-canvas" style="display: none;"></canvas>
                            <div class="camera-overlay">
                                <div class="scan-region"></div>
                                <div class="scan-status" id="scan-status">
                                    <i class="fas fa-camera"></i> Position QR Code in frame
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="user-details-card">
                        <div class="user-details-header">
                            <i class="fas fa-user-circle"></i>
                            <h3>User Information</h3>
                        </div>
                        <div class="user-details-content">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-fingerprint"></i>
                                    <span>ID</span>
                                </div>
                                <div class="detail-value" id="user-id">N/A</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-user"></i>
                                    <span>Name</span>
                                </div>
                                <div class="detail-value" id="user-name">N/A</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-envelope"></i>
                                    <span>Email</span>
                                </div>
                                <div class="detail-value" id="user-email">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="qr-preview">
                    <h3>QR Code Preview</h3>
                    <img src="<?php echo $qr_url; ?>" alt="QR Code" class="qr-image" id="qr-image">
                    <div class="qr-actions">
                        <button type="button" class="btn btn-primary" id="download-qr">
                            <i class="fas fa-download"></i> Download
                        </button>
                        <button type="button" class="btn btn-secondary" id="copy-qr-link">
                            <i class="fas fa-link"></i> Copy Link
                        </button>
                    </div>
                    <button id="start-camera" class="btn btn-primary camera-toggle">
                        <i class="fas fa-camera"></i> Start Camera
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-container" class="toast-container"></div>
    <script src="../javascript/qrcodescript.js"></script>
</body>
</html>
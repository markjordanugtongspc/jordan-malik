<?php
session_start();
include '../backend/database/config.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: ./auth/sign-in/login.php?error=You must be logged in to access this page.");
    exit();
}

// Fetch user's full name from database
$user_email = $_SESSION['user_email'];
$stmt = $conn->prepare("SELECT fullname FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $fullname = $row['fullname'];
} else {
    // Fallback to email username if database fetch fails
    $fullname = explode('@', $_SESSION['user_email'])[0];
    $fullname = ucfirst($fullname);
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $fullname; ?> </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../backend/facialrecog/face-api.js"></script>
    <script src="./javascript/face-recognition.js"></script>
    <link rel="stylesheet" href="./css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <div class="hamburger" id="hamburger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
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
                <i class="fas fa-sign-out-alt"></i> Logout </a>
        </div>
    </nav>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <div class="sidebar-menu-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </div>
            <div class="sidebar-menu-item" id="open-profile-modal">
                <i class="fas fa-user"></i> Profile
            </div>
            <div class="sidebar-menu-item">
                <i class="fas fa-cog"></i> Settings
            </div>
            <div class="sidebar-menu-item">
                <i class="fas fa-bell"></i> Notifications
            </div>
            <div class="sidebar-menu-item">
                <i class="fas fa-chart-line"></i> Analytics
            </div>
            <a href="./qrcode_gen/qrcodegen.php" class="sidebar-menu-item" id="qr-code-nav">
                <i class="fas fa-qrcode"></i> QR Code </a>
            <div class="sidebar-divider"></div>
            <div class="sidebar-menu-item" id="open-notes">
                <i class="fas fa-sticky-note"></i> Notes
            </div>
            <div class="sidebar-menu-item">
                <i class="fas fa-calendar"></i> Calendar
            </div>
            <div class="sidebar-divider"></div>
            <div class="sidebar-menu-item" id="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </div>
        </div>
    </div>
    <div class="main-content">
        <section class="welcome-section">
            <h1>Welcome back, <span id="display-username"> <?php echo $fullname; ?> </span>! </h1>
            <p>You're now securely logged in to your dashboard.</p>
            <p>Last login: <?php echo date('F j, Y \a\t g:i a'); ?> </p>
            <div class="live-clock">
                <i class="far fa-clock"></i>
                <span id="live-time">--:--:--</span>
            </div>
        </section>
        <div class="chart-container">
            <div class="chart-header">
                <div class="chart-title">System Activity Overview</div>
                <select id="time-range">
                    <option value="week">Last Week</option>
                    <option value="month" selected>Last Month</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
            <canvas id="activityChart"></canvas>
        </div>
        <div class="dashboard-grid">
            <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Account Status</h3>
                        <p>Verified</p>
                        <i class="fas fa-check-circle" style="color: var(--dashboard-success)"></i>
                    </div>
                    <div class="stat-card">
                        <h3>Security Level</h3>
                        <p>Unknown</p>
                        <i class="fas fa-question-circle" style="color: var(--dashboard-warning)"></i>
                    </div>
                    <div class="stat-card">
                        <h3>Active Sessions</h3>
                        <p>1</p>
                        <i class="fas fa-laptop" style="color: var(--dashboard-info)"></i>
                    </div>
            </div>
            <div class="qr-preview-card">
                <div class="qr-code-container">
                    <h3>Your QR Code</h3> <?php
                    // Get user data for QR code
                    $qrData = array(
                        'id' => 'USER-' . rand(1000, 9999), // Replace with actual user ID
                        'name' => $fullname,
                        'email' => $_SESSION['user_email']
                    );
                    $qrContent = urlencode(json_encode($qrData));
                    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . $qrContent . "&size=200x200&charset-source=UTF-8";
                    ?> <img src="<?php echo $qr_url; ?>" alt="Your QR Code" />
                </div>
            </div>
            <div class="activity-list">
                    <div class="activity-header">
                        <h3 style="margin-bottom: 1.5rem; color: var(--dashboard-primary)">Recent Activity</h3>
                        <div class="activity-controls">
                            <select id="activity-filter" class="activity-filter">
                                <option value="all">All Activities</option>
                                <option value="login">Logins</option>
                                <option value="security">Security</option>
                                <option value="verification">Verification</option>
                            </select>
                            <button id="refresh-activities" class="btn btn-icon" title="Refresh activities">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="activity-timeline"></div>
                        <div class="activity-content">
                            <div class="activity-title">Login from new device</div>
                            <div class="activity-time">Today at <?php echo date('g:i a'); ?> </div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: var(--dashboard-info)">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="activity-timeline"></div>
                        <div class="activity-content">
                            <div class="activity-title">Security check completed</div>
                            <div class="activity-time">Yesterday at 3:45 PM</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: var(--dashboard-success)">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Account verification complete</div>
                            <div class="activity-time"> <?php echo date('M j', strtotime('-3 days')); ?> at 10:30 AM </div>
                        </div>
                    </div>
            </div>
            <!-- // Notes Widget -->
            <div id="notes-widget" class="notes-widget" style="display: none">
                    <div class="notes-header">
                        <div class="notes-title">Quick Notes</div>
                        <button id="add-note-btn" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.9rem">
                            <i class="fas fa-plus"></i> New Note </button>
                    </div>
                    <div id="notes-list" class="notes-list">
                        <!-- Notes will be loaded here -->
                    </div>
                    <div id="note-form" style="display: none">
                        <textarea id="note-text" placeholder="Write your note here..."></textarea>
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem">
                            <button id="cancel-note" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.9rem">Cancel</button>
                            <button id="save-note" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.9rem">Save Note</button>
                        </div>
                    </div>
            </div>
            
            <!-- Spacer for visual separation -->
            <!-- <div style="margin-top: 5rem;"></div> -->
        </div>
        <!-- Facial Recognition -->
        <div class="facial-recognition-container">
                <div class="facial-recognition-header">
                    <div class="header-left">
                        <h3>
                            <i class="fas fa-brain"></i> Emotion Analysis
                        </h3>
                        <span class="status-badge" id="detectionStatus">Inactive</span>
                    </div>
                    <div class="facial-controls">
                        <button id="startFaceDetection" class="btn btn-primary btn-circle">
                            <i class="fas fa-play"></i>
                        </button>
                        <button id="stopFaceDetection" class="btn btn-danger btn-circle" style="display: none;">
                            <i class="fas fa-stop"></i>
                        </button>
                    </div>
                </div>
                <div class="video-container">
                    <div class="camera-frame">
                        <video id="faceVideo" autoplay muted playsinline></video>
                        <canvas id="faceOverlay"></canvas>
                        <div class="camera-decorations">
                            <div class="corner top-left"></div>
                            <div class="corner top-right"></div>
                            <div class="corner bottom-left"></div>
                            <div class="corner bottom-right"></div>
                        </div>
                        <div class="face-loading" id="faceLoading">
                            <div class="loading-pulse"></div>
                            <div class="loading-text">Initializing AI models</div>
                        </div>
                        <div id="faceStatus" class="face-status">
                            <i class="fas fa-camera-slash"></i> Camera inactive
                        </div>
                    </div>
                    <div class="emotion-panel">
                        <div class="emotion-heading">Detected Emotion</div>
                        <div id="emotionDisplay" class="emotion-display">
                            <div class="emotion-placeholder">
                                <i class="fas fa-face-meh-blank"></i>
                                <span>Start camera to detect emotions</span>
                            </div>
                        </div>
                        <div class="emotion-metrics">
                            <div class="metric-item" id="happyMetric">
                                <div class="metric-label">
                                    <i class="fas fa-face-smile"></i> Happy
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 0%"></div>
                                </div>
                                <div class="metric-value">0%</div>
                            </div>
                            <div class="metric-item" id="sadMetric">
                                <div class="metric-label">
                                    <i class="fas fa-face-sad-tear"></i> Sad
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 0%"></div>
                                </div>
                                <div class="metric-value">0%</div>
                            </div>
                            <div class="metric-item" id="angryMetric">
                                <div class="metric-label">
                                    <i class="fas fa-face-angry"></i> Angry
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 0%"></div>
                                </div>
                                <div class="metric-value">0%</div>
                            </div>
                            <div class="metric-item" id="surprisedMetric">
                                <div class="metric-label">
                                    <i class="fas fa-face-surprise"></i> Surprised
                                </div>
                                <div class="metric-bar">
                                    <div class="metric-fill" style="width: 0%"></div>
                                </div>
                                <div class="metric-value">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <!-- Profile Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Profile Settings</h2>
                <span class="modal-close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="profileForm">
                    <div class="profile-image-container">
                        <div class="profile-image">
                            <img id="profileImg" src="https://via.placeholder.com/150" alt="Profile Image">
                        </div>
                        <label class="btn-upload" for="imageUpload">
                            <i class="fas fa-camera"></i> Change Photo </label>
                        <input type="file" id="imageUpload" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo $fullname; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $_SESSION['user_email']; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Dashboard Footer with Facial Recognition -->
    <footer class="dashboard-footer">
        <div class="footer-content">
            
        </div>
    </footer>
    
    <div id="toast-container" class="toast-container"></div>
    <script src="./javascript/dashboard.js"></script>
    <script src="./javascript/face-recognition.js"></script>
</body>
</html>
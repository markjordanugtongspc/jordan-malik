<?php
session_start();
include '../../database/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to update your profile']);
    exit();
}

// Verify the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get the user data
$user_email = $_SESSION['user_email'];
$new_fullname = $_POST['fullname'] ?? null;
$new_email = $_POST['email'] ?? null;

// Data validation
if (!$new_fullname || trim($new_fullname) === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Full name cannot be empty']);
    exit();
}

if (!$new_email || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Check if the new email already exists (but is not the user's current email)
if ($new_email !== $user_email) {
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND email != ?");
    $checkStmt->bind_param("ss", $new_email, $user_email);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is already in use by another account']);
        exit();
    }
}

// Update the user profile in the database
$stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ? WHERE email = ?");
$stmt->bind_param("sss", $new_fullname, $new_email, $user_email);

if ($stmt->execute()) {
    // Update the session with the new email if it has changed
    if ($user_email !== $new_email) {
        $_SESSION['user_email'] = $new_email;
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully',
        'user' => [
            'fullname' => $new_fullname,
            'email' => $new_email
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $conn->error]);
}
?> 
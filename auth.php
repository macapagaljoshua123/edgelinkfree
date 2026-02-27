<?php
require_once 'db_config.php';
session_start();

// FIXED CORS HEADERS - Pwede sa both localhost at 127.0.0.1
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Signup
if (isset($_POST['action']) && $_POST['action'] == 'signup') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = trim($_POST['email'] ?? '');
    
    try {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
        
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $email]);
        
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Login
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Get all feedback (for admin)
if (isset($_GET['action']) && $_GET['action'] == 'get_feedback') {
    // Check if user is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
        $feedback = $stmt->fetchAll();
        echo json_encode(['success' => true, 'feedback' => $feedback]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Submit feedback
if (isset($_POST['action']) && $_POST['action'] == 'submit_feedback') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $landline = trim($_POST['landline'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $message = trim($_POST['message']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (name, email, landline, mobile, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $landline, $mobile, $message]);
        
        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Update feedback status (admin only)
if (isset($_POST['action']) && $_POST['action'] == 'update_feedback_status') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $feedback_id = $_POST['feedback_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?");
        $stmt->execute([$status, $feedback_id]);
        
        echo json_encode(['success' => true, 'message' => 'Status updated']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Get stats for admin dashboard
if (isset($_GET['action']) && $_GET['action'] == 'get_stats') {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    try {
        $stats = [];
        
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['totalUsers'] = $stmt->fetch()['count'];
        
        // Total events
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM events");
        $stats['totalEvents'] = $stmt->fetch()['count'];
        
        // Total sessions
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sessions WHERE status = 'active'");
        $stats['totalSessions'] = $stmt->fetch()['count'];
        
        // New feedback
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'new'");
        $stats['newFeedback'] = $stmt->fetch()['count'];
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
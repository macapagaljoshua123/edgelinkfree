<?php
// FIXED CORS HEADERS
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db_config.php';
session_start();

// Rest of your code...
require_once 'db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(401);
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
    
    echo json_encode(['success' => true, ...$stats]);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
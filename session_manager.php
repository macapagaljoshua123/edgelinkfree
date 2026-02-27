<?php
// FIXED CORS HEADERS
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

require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

// Rest of your code...

// Debug: Log session info
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create_session') {
    $session_code = $_POST['session_code'];
    $session_type = $_POST['session_type'];
    $title = $_POST['title'] ?? '';
    $data = $_POST['data'] ?? '{}';
    $user_id = $_SESSION['user_id'];
    
    error_log("Creating session for user: $user_id, code: $session_code, type: $session_type");
    
    try {
        // Check if sessions table exists
        $checkTable = $pdo->query("SHOW TABLES LIKE 'sessions'");
        if ($checkTable->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'sessions table does not exist. Please run install.php first.']);
            exit;
        }
        
        // First, check if session already exists
        $checkStmt = $pdo->prepare("SELECT id FROM sessions WHERE session_code = ?");
        $checkStmt->execute([$session_code]);
        
        if ($checkStmt->fetch()) {
            // Update existing session
            $stmt = $pdo->prepare("
                UPDATE sessions SET data = ?, updated_at = NOW() 
                WHERE session_code = ?
            ");
            $stmt->execute([$data, $session_code]);
            error_log("Updated existing session: $session_code");
        } else {
            // Insert new session
            $stmt = $pdo->prepare("
                INSERT INTO sessions (user_id, session_code, session_type, title, data, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$user_id, $session_code, $session_type, $title, $data]);
            error_log("Created new session: $session_code");
        }
        
        echo json_encode(['success' => true, 'message' => 'Session saved successfully']);
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// If no action matched
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
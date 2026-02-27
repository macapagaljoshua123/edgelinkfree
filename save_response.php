<?php
// FIXED CORS HEADERS - Pwede sa both localhost at 127.0.0.1
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

// Rest of your code...

header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Save form response
if ($action === 'save_form_response') {
    $session_code = $_POST['session_code'];
    $participant_name = $_POST['participant_name'];
    $answers = $_POST['answers'];
    
    try {
        // I-save sa form_responses table
        $stmt = $pdo->prepare("
            INSERT INTO form_responses (session_code, participant_name, answers) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$session_code, $participant_name, $answers]);
        
        echo json_encode(['success' => true, 'message' => 'Response saved to database']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Save word submission
if ($action === 'save_word_submission') {
    $session_code = $_POST['session_code'];
    $participant_name = $_POST['participant_name'];
    $word = $_POST['word'];
    
    try {
        // I-save sa word_submissions table
        $stmt = $pdo->prepare("
            INSERT INTO word_submissions (session_code, participant_name, word) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$session_code, $participant_name, $word]);
        
        echo json_encode(['success' => true, 'message' => 'Word saved to database']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Save quiz participant
if ($action === 'save_quiz_participant') {
    $session_code = $_POST['session_code'];
    $participant_name = $_POST['participant_name'];
    $score = $_POST['score'] ?? 0;
    $answers = $_POST['answers'] ?? '[]';
    
    try {
        // Check if participant already exists
        $checkStmt = $pdo->prepare("SELECT id FROM quiz_participants WHERE session_code = ? AND participant_name = ?");
        $checkStmt->execute([$session_code, $participant_name]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Update existing participant
            $stmt = $pdo->prepare("
                UPDATE quiz_participants 
                SET score = ?, answers = ? 
                WHERE session_code = ? AND participant_name = ?
            ");
            $stmt->execute([$score, $answers, $session_code, $participant_name]);
            $message = 'Quiz participant updated';
        } else {
            // Insert new participant
            $stmt = $pdo->prepare("
                INSERT INTO quiz_participants (session_code, participant_name, score, answers) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$session_code, $participant_name, $score, $answers]);
            $message = 'Quiz participant saved';
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch(PDOException $e) {
        error_log("Database error in save_quiz_participant: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Get quiz participants for a session
if ($action === 'get_quiz_participants') {
    $session_code = $_POST['session_code'] ?? $_GET['session_code'] ?? '';
    
    if (empty($session_code)) {
        echo json_encode(['success' => false, 'message' => 'No session code provided']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM quiz_participants 
            WHERE session_code = ? 
            ORDER BY score DESC, joined_at ASC
        ");
        $stmt->execute([$session_code]);
        $participants = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'participants' => $participants]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
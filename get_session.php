<?php
$allowed_origins = ['http://localhost', 'http://127.0.0.1'];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'db_config.php';
header('Content-Type: application/json');

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'No code provided']);
    exit;
}

// Ensure code has # prefix
if (substr($code, 0, 1) !== '#') {
    $code = '#' . $code;
}

try {
    // Get session from sessions table
    $stmt = $pdo->prepare("
        SELECT s.*, u.username as owner_name 
        FROM sessions s 
        LEFT JOIN users u ON s.user_id = u.id 
        WHERE s.session_code = ? AND s.status = 'active'
    ");
    $stmt->execute([$code]);
    $session = $stmt->fetch();
    
    if ($session) {
        $sessionData = json_decode($session['data'], true);
        
        $response = [
            'success' => true,
            'session' => [
                'code' => $session['session_code'],
                'type' => $session['session_type'],
                'title' => $session['title'],
                'owner' => $session['owner_name'] ?? 'Unknown',
                'created_at' => $session['created_at']
            ]
        ];
        
        // Get form responses
        if ($session['session_type'] === 'form') {
            $respStmt = $pdo->prepare("SELECT * FROM form_responses WHERE session_code = ? ORDER BY submitted_at DESC");
            $respStmt->execute([$code]);
            $responses = $respStmt->fetchAll();
            
            $response['session']['questions'] = $sessionData['questions'] ?? [];
            $response['session']['responses'] = $responses;
        } 
        // Get word submissions
        else if ($session['session_type'] === 'cloud') {
            $wordStmt = $pdo->prepare("SELECT * FROM word_submissions WHERE session_code = ? ORDER BY submitted_at DESC");
            $wordStmt->execute([$code]);
            $submissions = $wordStmt->fetchAll();
            
            // Group words by word
            $words = [];
            foreach ($submissions as $sub) {
                $word = $sub['word'];
                if (!isset($words[$word])) {
                    $words[$word] = 0;
                }
                $words[$word]++;
            }
            
            $response['session']['question'] = $sessionData['question'] ?? '';
            $response['session']['instructions'] = $sessionData['instructions'] ?? '';
            $response['session']['words'] = $words;
            $response['session']['totalWords'] = array_sum($words);
            $response['session']['totalSubmissions'] = count($submissions);
            $response['session']['submissions'] = $submissions;
        } 
        // Get quiz participants
        else if ($session['session_type'] === 'quiz') {
            $partStmt = $pdo->prepare("SELECT * FROM quiz_participants WHERE session_code = ?");
            $partStmt->execute([$code]);
            $participants = $partStmt->fetchAll();
            
            $response['session']['questions'] = $sessionData['questions'] ?? [];
            $response['session']['participants'] = $participants;
            $response['session']['status'] = $sessionData['status'] ?? 'waiting';
        }
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Session not found. Please check the code and try again.']);
    }
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
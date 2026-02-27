<?php
require_once 'db_config.php';

echo "<h1>Test Form Responses</h1>";

try {
    // Kunin ang latest session
    $stmt = $pdo->query("SELECT * FROM sessions WHERE session_type = 'form' ORDER BY id DESC LIMIT 1");
    $session = $stmt->fetch();
    
    if (!$session) {
        // Gumawa ng test session kung wala
        echo "<p>No form sessions found. Creating test session...</p>";
        
        // Kunin ang unang user
        $userStmt = $pdo->query("SELECT * FROM users LIMIT 1");
        $user = $userStmt->fetch();
        
        if (!$user) {
            die("No users found. Please create a user first.");
        }
        
        $session_code = "#TEST" . rand(1000, 9999);
        $session_data = [
            'questions' => [
                [
                    'id' => 1,
                    'type' => 'short',
                    'text' => 'What is your name?'
                ],
                [
                    'id' => 2,
                    'type' => 'mcq',
                    'text' => 'What is your favorite color?',
                    'options' => ['Red', 'Blue', 'Green', 'Yellow']
                ],
                [
                    'id' => 3,
                    'type' => 'rating',
                    'text' => 'How would you rate our service?',
                    'scale' => 5,
                    'minLabel' => 'Poor',
                    'maxLabel' => 'Excellent'
                ]
            ],
            'responses' => []
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO sessions (user_id, session_code, session_type, title, data, status) 
            VALUES (?, ?, 'form', ?, ?, 'active')
        ");
        $insertStmt->execute([$user['id'], $session_code, "Test Form $session_code", json_encode($session_data)]);
        
        echo "<p>✅ Test session created with code: <strong>$session_code</strong></p>";
        
        // Kunin ang bagong session
        $stmt = $pdo->query("SELECT * FROM sessions WHERE session_code = '$session_code'");
        $session = $stmt->fetch();
    }
    
    // Ipakita ang session info
    echo "<h3>Session: " . $session['session_code'] . "</h3>";
    
    // Ipakita ang mga responses
    $respStmt = $pdo->prepare("SELECT * FROM form_responses WHERE session_code = ? ORDER BY id DESC");
    $respStmt->execute([$session['session_code']]);
    $responses = $respStmt->fetchAll();
    
    if (count($responses) > 0) {
        echo "<h3>Form Responses (" . count($responses) . "):</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:100%;'>";
        echo "<tr><th>ID</th><th>Participant</th><th>Answers</th><th>Submitted</th></tr>";
        
        foreach ($responses as $row) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><strong>" . $row['participant_name'] . "</strong></td>";
            
            // I-parse at i-display ang answers
            $answers = json_decode($row['answers'], true);
            echo "<td><pre style='margin:0; background:#f5f5f5; padding:5px; border-radius:5px;'>" . print_r($answers, true) . "</pre></td>";
            
            echo "<td>" . $row['submitted_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No responses yet for this session.</p>";
    }
    
    // Ipakita ang session data
    $sessionData = json_decode($session['data'], true);
    echo "<h3>Session Questions:</h3>";
    echo "<pre style='background:#f5f5f5; padding:10px; border-radius:5px;'>";
    print_r($sessionData['questions'] ?? []);
    echo "</pre>";
    
} catch(PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
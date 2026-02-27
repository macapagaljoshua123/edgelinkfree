<?php
require_once 'db_config.php';

echo "<h1>Test Database Submission</h1>";

try {
    // Mag-insert ng test response
    $stmt = $pdo->prepare("
        INSERT INTO form_responses (session_code, participant_name, answers) 
        VALUES (?, ?, ?)
    ");
    
    $test_answers = json_encode(['Test answer 1', 'Test answer 2']);
    $stmt->execute(['#TEST123', 'Test User', $test_answers]);
    
    echo "<p style='color:green;'>✅ Test response inserted successfully!</p>";
    
    // Ipakita ang laman ng form_responses
    $result = $pdo->query("SELECT * FROM form_responses ORDER BY id DESC LIMIT 5");
    $rows = $result->fetchAll();
    
    echo "<h3>Recent form_responses:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Session Code</th><th>Participant</th><th>Answers</th><th>Submitted</th></tr>";
    
    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['session_code'] . "</td>";
        echo "<td>" . $row['participant_name'] . "</td>";
        echo "<td>" . htmlspecialchars($row['answers']) . "</td>";
        echo "<td>" . $row['submitted_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
<?php
require_once 'db_config.php';
session_start();

// Only allow admin to run this
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die('Unauthorized');
}

echo "<h1>Notification Migration Tool</h1>";

try {
    // Clear database notifications
    $stmt = $pdo->prepare("DELETE FROM notifications");
    $stmt->execute();
    echo "<p>✅ Cleared all notifications from database</p>";
    
    echo "<p>Done! The notification system has been reset.</p>";
    echo "<p><a href='admin_dashboard.php'>Go back to Admin Dashboard</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
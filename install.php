<?php
require_once 'db_config.php';

echo "<h1>EDGELINK Database Installation</h1>";

try {
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        full_name VARCHAR(100),
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Users table created successfully<br>";

    // Create sessions table
    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_code VARCHAR(20) UNIQUE NOT NULL,
        session_type ENUM('form', 'cloud', 'quiz') NOT NULL,
        title VARCHAR(255),
        data TEXT,
        status ENUM('active', 'ended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Sessions table created successfully<br>";

    // Create form_responses table
    $sql = "CREATE TABLE IF NOT EXISTS form_responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_code VARCHAR(20) NOT NULL,
        participant_name VARCHAR(100) NOT NULL,
        answers TEXT NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Form responses table created successfully<br>";

    // Create word_submissions table
    $sql = "CREATE TABLE IF NOT EXISTS word_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_code VARCHAR(20) NOT NULL,
        participant_name VARCHAR(100) NOT NULL,
        word VARCHAR(100) NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Word submissions table created successfully<br>";

    // Create quiz_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS quiz_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_code VARCHAR(20) UNIQUE NOT NULL,
        data TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Quiz sessions table created successfully<br>";

    // Create quiz_participants table
    $sql = "CREATE TABLE IF NOT EXISTS quiz_participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_code VARCHAR(20) NOT NULL,
        participant_name VARCHAR(100) NOT NULL,
        score INT DEFAULT 0,
        answers TEXT,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Quiz participants table created successfully<br>";

    // Create feedback table
    $sql = "CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        landline VARCHAR(20),
        mobile VARCHAR(20),
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "✅ Feedback table created successfully<br>";

    // Create notifications table
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50),
        category VARCHAR(50),
        details TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Notifications table created successfully<br>";

    // Create events table
    $sql = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_data TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "✅ Events table created successfully<br>";

    echo "<br><strong style='color:green;'>✅ All tables created successfully!</strong><br>";
    echo "<p>You can now <a href='index.html'>go back to the application</a> and try again.</p>";

} catch(PDOException $e) {
    echo "<p style='color:red;'>❌ Error creating tables: " . $e->getMessage() . "</p>";
}
?>
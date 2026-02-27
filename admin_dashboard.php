<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EDGELINK Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f4f6f9;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: #061a3a;
            color: white;
            padding: 20px;
        }
        
        .sidebar h2 {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #1a2b40;
        }
        
        .sidebar h2 i {
            color: #2e89ff;
            margin-right: 10px;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar li {
            margin-bottom: 10px;
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #b0b8c4;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
            gap: 12px;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: #0d1f3d;
            color: white;
        }
        
        .sidebar a i {
            width: 20px;
            color: #2e89ff;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card h3 {
            font-size: 32px;
            color: #2e89ff;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            color: #666;
        }
        
        .feedback-table {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 15px 10px;
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
        }
        
        td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-new {
            background: #ff6b6b;
            color: white;
        }
        
        .status-read {
            background: #4CAF50;
            color: white;
        }
        
        .status-replied {
            background: #2e89ff;
            color: white;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            margin-right: 5px;
            transition: 0.2s;
        }
        
        .btn-primary {
            background: #2e89ff;
            color: white;
        }
        
        .btn-success {
            background: #4CAF50;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-content h3 {
            margin-bottom: 20px;
        }
        
        .modal-content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .close {
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2><i class="fas fa-chart-line"></i> EDGELINK Admin</h2>
            <ul>
                <li><a href="#" class="active" onclick="loadDashboard()"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#" onclick="loadFeedback()"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="#" onclick="loadUsers()"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="#" onclick="loadEvents()"><i class="fas fa-calendar"></i> Events</a></li>
                <li><a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content" id="mainContent">
            <div class="header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div>
                    <span>Welcome, <?php echo $_SESSION['username']; ?></span>
                </div>
            </div>
            
            <div class="stats-grid" id="statsGrid">
                <!-- Stats will be loaded here -->
            </div>
            
            <div class="feedback-table" id="feedbackSection">
                <h3 style="margin-bottom: 20px;">Recent Feedback</h3>
                <table id="feedbackTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="feedbackBody">
                        <!-- Feedback will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- View Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalName">Feedback Details</h3>
            <p><strong>Email:</strong> <span id="modalEmail"></span></p>
            <p><strong>Mobile:</strong> <span id="modalMobile"></span></p>
            <p><strong>Landline:</strong> <span id="modalLandline"></span></p>
            <p><strong>Message:</strong></p>
            <p id="modalMessage" style="background: #f8f9fa; padding: 15px; border-radius: 8px;"></p>
            <p><strong>Received:</strong> <span id="modalDate"></span></p>
            <div style="margin-top: 20px;">
                <button class="btn btn-success" onclick="markAsRead()">Mark as Read</button>
                <button class="btn btn-primary" onclick="markAsReplied()">Mark as Replied</button>
            </div>
        </div>
    </div>
    
    <script>
        let currentFeedbackId = null;
        
        function loadDashboard() {
            // Load dashboard stats
            fetch('edgelink_api/stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('statsGrid').innerHTML = `
                            <div class="stat-card">
                                <h3>${data.totalUsers}</h3>
                                <p>Total Users</p>
                            </div>
                            <div class="stat-card">
                                <h3>${data.totalEvents}</h3>
                                <p>Total Events</p>
                            </div>
                            <div class="stat-card">
                                <h3>${data.totalSessions}</h3>
                                <p>Active Sessions</p>
                            </div>
                            <div class="stat-card">
                                <h3>${data.newFeedback}</h3>
                                <p>New Feedback</p>
                            </div>
                        `;
                    }
                });
            
            loadFeedback();
        }
        
        function loadFeedback() {
            fetch('edgelink_api/auth.php?action=get_feedback')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '';
                        data.feedback.forEach(item => {
                            html += `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${item.email}</td>
                                    <td>${item.message.substring(0, 50)}...</td>
                                    <td>${new Date(item.created_at).toLocaleDateString()}</td>
                                    <td><span class="status-badge status-${item.status}">${item.status}</span></td>
                                    <td>
                                        <button class="btn btn-primary" onclick="viewFeedback(${item.id}, '${item.name}', '${item.email}', '${item.mobile || ''}', '${item.landline || ''}', '${item.message.replace(/'/g, "\\'")}', '${item.created_at}')">View</button>
                                    </td>
                                </tr>
                            `;
                        });
                        document.getElementById('feedbackBody').innerHTML = html;
                    }
                });
        }
        
        function viewFeedback(id, name, email, mobile, landline, message, date) {
            currentFeedbackId = id;
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalEmail').textContent = email;
            document.getElementById('modalMobile').textContent = mobile || 'Not provided';
            document.getElementById('modalLandline').textContent = landline || 'Not provided';
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('modalDate').textContent = new Date(date).toLocaleString();
            document.getElementById('feedbackModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('feedbackModal').style.display = 'none';
        }
        
        function markAsRead() {
            updateFeedbackStatus('read');
        }
        
        function markAsReplied() {
            updateFeedbackStatus('replied');
        }
        
        function updateFeedbackStatus(status) {
            const formData = new FormData();
            formData.append('action', 'update_feedback_status');
            formData.append('feedback_id', currentFeedbackId);
            formData.append('status', status);
            
            fetch('edgelink_api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    loadFeedback();
                }
            });
        }
        
        function loadUsers() {
            // Implement users management
            alert('Users management coming soon!');
        }
        
        function loadEvents() {
            // Implement events management
            alert('Events management coming soon!');
        }
        
        function logout() {
            window.location.href = 'index.html';
        }
        
        // Load dashboard on page load
        loadDashboard();
    </script>
</body>
</html>
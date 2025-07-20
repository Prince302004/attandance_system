<?php
/**
 * Simple Login Test - Attendance Management System
 * This script tests the basic login functionality
 */

// Start session
session_start();

// Include database configuration
require_once 'php/config.php';

echo "<h2>🔑 Login Test - Attendance Management System</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    .btn:hover { background: #0056b3; }
</style>";

// Test database connection
if (!$conn->ping()) {
    echo "<div class='error'>❌ Database connection failed</div>";
    exit();
}

echo "<div class='success'>✓ Database connection successful</div>";

// Test login process
if (isset($_POST['test_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    echo "<div class='info'>Testing login for: $username</div>";
    
    // Ensure we're using the correct database
    if ($conn->database != DB_NAME) {
        $conn->select_db(DB_NAME);
    }
    
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<div class='success'>✓ User found: " . $user['username'] . " (Role: " . $user['role'] . ")</div>";
        
        if (password_verify($password, $user['password'])) {
            echo "<div class='success'>✓ Password verified</div>";
            
            if ($user['is_verified'] || $user['role'] == 'admin') {
                echo "<div class='success'>✓ User is verified</div>";
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                echo "<div class='success'>✓ Session variables set</div>";
                echo "<div class='info'>Session data: " . print_r($_SESSION, true) . "</div>";
                
                // Determine redirect URL
                $redirect_url = '';
                switch ($user['role']) {
                    case 'student':
                        $redirect_url = 'student/dashboard.php';
                        break;
                    case 'teacher':
                        $redirect_url = 'teacher/dashboard.php';
                        break;
                    case 'admin':
                        $redirect_url = 'admin/dashboard.php';
                        break;
                }
                
                echo "<div class='success'>✓ Redirect URL: $redirect_url</div>";
                
                if (file_exists($redirect_url)) {
                    echo "<div class='success'>✓ Dashboard file exists</div>";
                    echo "<a href='$redirect_url' class='btn' style='background: #28a745;'>Go to Dashboard</a>";
                } else {
                    echo "<div class='error'>❌ Dashboard file not found</div>";
                }
                
            } else {
                echo "<div class='error'>❌ User not verified</div>";
            }
        } else {
            echo "<div class='error'>❌ Password incorrect</div>";
        }
    } else {
        echo "<div class='error'>❌ User not found</div>";
    }
} else {
    echo "<div class='info'>Test the login process:</div>";
    echo "<form method='POST'>";
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Username: <input type='text' name='username' value='admin' style='padding: 5px; width: 200px;'></label>";
    echo "</div>";
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Password: <input type='password' name='password' value='admin123' style='padding: 5px; width: 200px;'></label>";
    echo "</div>";
    echo "<input type='submit' name='test_login' value='Test Login' class='btn' style='background: #28a745;'>";
    echo "</form>";
}

echo "<hr>";
echo "<div style='text-align: center;'>";
echo "<a href='index.php' class='btn'>Go to Login Page</a>";
echo "<a href='debug_login.php' class='btn' style='background: #6c757d;'>Full Debug</a>";
echo "<a href='logout.php' class='btn' style='background: #dc3545;'>Logout</a>";
echo "</div>";
?>
<?php
/**
 * Debug Login Script - Attendance Management System
 * This script helps identify login issues
 */

// Start session
session_start();

// Include database configuration
require_once 'php/config.php';

echo "<h2>🔍 Login Debug - Attendance Management System</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .debug-section { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
    .debug-table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    .debug-table th, .debug-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .debug-table th { background-color: #f2f2f2; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    .btn:hover { background: #0056b3; }
</style>";

// Test 1: Database Connection
echo "<div class='debug-section'>";
echo "<h3>🔌 Test 1: Database Connection</h3>";

if ($conn->ping()) {
    echo "<div class='success'>✓ Database connection is working</div>";
} else {
    echo "<div class='error'>❌ Database connection failed</div>";
    exit();
}

// Test 2: Session Status
echo "<div class='debug-section'>";
echo "<h3>🔐 Test 2: Session Status</h3>";

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='success'>✓ Session is active</div>";
    echo "<div class='info'>Session ID: " . session_id() . "</div>";
} else {
    echo "<div class='error'>❌ Session is not active</div>";
}

if (isset($_SESSION['user_id'])) {
    echo "<div class='success'>✓ User is logged in</div>";
    echo "<div class='info'>User ID: " . $_SESSION['user_id'] . "</div>";
    echo "<div class='info'>Username: " . $_SESSION['username'] . "</div>";
    echo "<div class='info'>Role: " . $_SESSION['role'] . "</div>";
    echo "<div class='info'>Full Name: " . $_SESSION['full_name'] . "</div>";
} else {
    echo "<div class='info'>ℹ No user is currently logged in</div>";
}

// Test 3: Check Users Table
echo "<div class='debug-section'>";
echo "<h3>👥 Test 3: Users Table Check</h3>";

$users_sql = "SELECT id, username, email, role, is_verified FROM users ORDER BY id";
$users_result = $conn->query($users_sql);

if ($users_result) {
    echo "<div class='success'>✓ Users table exists and is accessible</div>";
    echo "<div class='info'>Found " . $users_result->num_rows . " users in database</div>";
    
    if ($users_result->num_rows > 0) {
        echo "<table class='debug-table'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Verified</th></tr>";
        while ($user = $users_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . ($user['is_verified'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='error'>❌ Error accessing users table: " . $conn->error . "</div>";
}

// Test 4: Test Login Process
echo "<div class='debug-section'>";
echo "<h3>🔑 Test 4: Login Process Test</h3>";

if (isset($_POST['test_login'])) {
    $test_username = trim($_POST['test_username']);
    $test_password = $_POST['test_password'];
    
    echo "<div class='info'>Testing login for username: $test_username</div>";
    
    if (empty($test_username) || empty($test_password)) {
        echo "<div class='error'>❌ Username and password are required</div>";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $test_username, $test_username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<div class='success'>✓ User found in database</div>";
            echo "<div class='info'>User ID: " . $user['id'] . "</div>";
            echo "<div class='info'>Role: " . $user['role'] . "</div>";
            echo "<div class='info'>Verified: " . ($user['is_verified'] ? 'Yes' : 'No') . "</div>";
            
            if (password_verify($test_password, $user['password'])) {
                echo "<div class='success'>✓ Password is correct</div>";
                
                if ($user['is_verified'] || $user['role'] == 'admin') {
                    echo "<div class='success'>✓ User is verified or admin</div>";
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    
                    echo "<div class='success'>✓ Session variables set successfully</div>";
                    echo "<div class='info'>Session data:</div>";
                    echo "<ul>";
                    echo "<li>user_id: " . $_SESSION['user_id'] . "</li>";
                    echo "<li>username: " . $_SESSION['username'] . "</li>";
                    echo "<li>role: " . $_SESSION['role'] . "</li>";
                    echo "<li>full_name: " . $_SESSION['full_name'] . "</li>";
                    echo "</ul>";
                    
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
                    
                    // Check if dashboard file exists
                    if (file_exists($redirect_url)) {
                        echo "<div class='success'>✓ Dashboard file exists</div>";
                        echo "<a href='$redirect_url' class='btn' style='background: #28a745;'>Go to Dashboard</a>";
                    } else {
                        echo "<div class='error'>❌ Dashboard file not found: $redirect_url</div>";
                    }
                    
                } else {
                    echo "<div class='error'>❌ User is not verified</div>";
                }
            } else {
                echo "<div class='error'>❌ Password is incorrect</div>";
            }
        } else {
            echo "<div class='error'>❌ User not found in database</div>";
        }
    }
} else {
    echo "<div class='info'>Test login with any user credentials:</div>";
    echo "<form method='POST'>";
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Username or Email: <input type='text' name='test_username' placeholder='admin' style='padding: 5px; width: 200px;'></label>";
    echo "</div>";
    echo "<div style='margin: 10px 0;'>";
    echo "<label>Password: <input type='password' name='test_password' placeholder='admin123' style='padding: 5px; width: 200px;'></label>";
    echo "</div>";
    echo "<input type='submit' name='test_login' value='Test Login' class='btn' style='background: #28a745;'>";
    echo "</form>";
}

// Test 5: Check Dashboard Files
echo "<div class='debug-section'>";
echo "<h3>📁 Test 5: Dashboard Files Check</h3>";

$dashboard_files = [
    'student/dashboard.php' => 'Student Dashboard',
    'teacher/dashboard.php' => 'Teacher Dashboard',
    'admin/dashboard.php' => 'Admin Dashboard'
];

foreach ($dashboard_files as $file => $name) {
    if (file_exists($file)) {
        echo "<div class='success'>✓ $name file exists</div>";
    } else {
        echo "<div class='error'>❌ $name file missing: $file</div>";
    }
}

// Test 6: PHP Error Reporting
echo "<div class='debug-section'>";
echo "<h3>🐛 Test 6: PHP Error Reporting</h3>";

$error_reporting = error_reporting();
$display_errors = ini_get('display_errors');
$log_errors = ini_get('log_errors');

echo "<div class='info'>Error Reporting Level: $error_reporting</div>";
echo "<div class='info'>Display Errors: " . ($display_errors ? 'On' : 'Off') . "</div>";
echo "<div class='info'>Log Errors: " . ($log_errors ? 'On' : 'Off') . "</div>";

if (!$display_errors) {
    echo "<div class='warning'>⚠️ Display errors is off. Enable it to see PHP errors.</div>";
}

// Test 7: Common Issues Check
echo "<div class='debug-section'>";
echo "<h3>🔧 Test 7: Common Issues Check</h3>";

// Check if headers were already sent
if (headers_sent($file, $line)) {
    echo "<div class='error'>❌ Headers already sent in $file on line $line</div>";
} else {
    echo "<div class='success'>✓ Headers not sent yet</div>";
}

// Check output buffering
if (ob_get_level() > 0) {
    echo "<div class='info'>ℹ Output buffering is active (level: " . ob_get_level() . ")</div>";
} else {
    echo "<div class='info'>ℹ No output buffering</div>";
}

// Check file permissions
$test_files = ['student/dashboard.php', 'teacher/dashboard.php', 'admin/dashboard.php'];
foreach ($test_files as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<div class='success'>✓ $file is readable</div>";
        } else {
            echo "<div class='error'>❌ $file is not readable</div>";
        }
    }
}

echo "</div>";

// Navigation
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='index.php' class='btn'>Go to Login Page</a>";
echo "<a href='setup_database.php' class='btn' style='background: #6c757d;'>Setup Database</a>";
echo "<a href='logout.php' class='btn' style='background: #dc3545;'>Logout</a>";
echo "</div>";

echo "<div class='info'>";
echo "<strong>Debugging Tips:</strong><br>";
echo "• Check browser console for JavaScript errors<br>";
echo "• Check server error logs<br>";
echo "• Verify database credentials<br>";
echo "• Ensure all files have correct permissions<br>";
echo "• Test with default admin credentials: admin / admin123";
echo "</div>";
?>
<?php
/**
 * Dashboard Access Test - Attendance Management System
 * This script tests if dashboard files are accessible
 */

echo "<h2>🚪 Dashboard Access Test</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    .btn:hover { background: #0056b3; }
</style>";

// Test 1: Check if dashboard files exist
echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>📁 Test 1: Dashboard Files Existence</h3>";

$dashboard_files = [
    'student/dashboard.php' => 'Student Dashboard',
    'teacher/dashboard.php' => 'Teacher Dashboard',
    'admin/dashboard.php' => 'Admin Dashboard'
];

foreach ($dashboard_files as $file => $name) {
    if (file_exists($file)) {
        echo "<div class='success'>✓ $name file exists: $file</div>";
    } else {
        echo "<div class='error'>❌ $name file missing: $file</div>";
    }
}
echo "</div>";

// Test 2: Check file permissions
echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>🔐 Test 2: File Permissions</h3>";

foreach ($dashboard_files as $file => $name) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<div class='success'>✓ $name is readable</div>";
        } else {
            echo "<div class='error'>❌ $name is not readable</div>";
        }
        
        if (is_executable($file)) {
            echo "<div class='success'>✓ $name is executable</div>";
        } else {
            echo "<div class='warning'>⚠ $name is not executable (this might be normal)</div>";
        }
    }
}
echo "</div>";

// Test 3: Test direct access to dashboard files
echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>🔗 Test 3: Direct Dashboard Access</h3>";

echo "<div class='info'>Click the links below to test direct access to dashboard files:</div>";

foreach ($dashboard_files as $file => $name) {
    if (file_exists($file)) {
        echo "<a href='$file' class='btn' target='_blank'>Test $name</a> ";
    }
}
echo "</div>";

// Test 4: Check .htaccess impact
echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>📄 Test 4: .htaccess Configuration</h3>";

if (file_exists('.htaccess')) {
    echo "<div class='success'>✓ .htaccess file exists</div>";
    
    $htaccess_content = file_get_contents('.htaccess');
    
    // Check for problematic rules
    if (strpos($htaccess_content, 'Deny from all') !== false) {
        echo "<div class='warning'>⚠ .htaccess contains 'Deny from all' rules</div>";
    }
    
    if (strpos($htaccess_content, 'Options -Indexes') !== false) {
        echo "<div class='success'>✓ Directory browsing is disabled (good for security)</div>";
    }
    
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<div class='success'>✓ URL rewriting is enabled</div>";
    }
    
} else {
    echo "<div class='info'>ℹ No .htaccess file found</div>";
}
echo "</div>";

// Test 5: Session and Login Test
echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>🔑 Test 5: Quick Login Test</h3>";

session_start();

if (isset($_SESSION['user_id'])) {
    echo "<div class='success'>✓ User is logged in</div>";
    echo "<div class='info'>User ID: " . $_SESSION['user_id'] . "</div>";
    echo "<div class='info'>Role: " . $_SESSION['role'] . "</div>";
    echo "<div class='info'>Username: " . $_SESSION['username'] . "</div>";
    
    // Determine which dashboard to test
    $dashboard_url = '';
    switch ($_SESSION['role']) {
        case 'student':
            $dashboard_url = 'student/dashboard.php';
            break;
        case 'teacher':
            $dashboard_url = 'teacher/dashboard.php';
            break;
        case 'admin':
            $dashboard_url = 'admin/dashboard.php';
            break;
    }
    
    if ($dashboard_url && file_exists($dashboard_url)) {
        echo "<div class='success'>✓ Redirecting to appropriate dashboard</div>";
        echo "<a href='$dashboard_url' class='btn' style='background: #28a745;'>Go to Dashboard</a>";
    }
    
} else {
    echo "<div class='info'>ℹ No user is currently logged in</div>";
    echo "<div class='info'>Test login first:</div>";
    echo "<a href='test_login.php' class='btn' style='background: #28a745;'>Test Login</a>";
}
echo "</div>";

// Test 6: Common Issues Check
echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
echo "<h3>🔧 Test 6: Common Issues Check</h3>";

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

// Check PHP error reporting
$display_errors = ini_get('display_errors');
echo "<div class='info'>Display Errors: " . ($display_errors ? 'On' : 'Off') . "</div>";

if (!$display_errors) {
    echo "<div class='warning'>⚠️ Display errors is off. Enable it to see PHP errors.</div>";
}
echo "</div>";

// Navigation
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='index.php' class='btn'>Go to Login Page</a>";
echo "<a href='test_login.php' class='btn' style='background: #28a745;'>Test Login</a>";
echo "<a href='debug_login.php' class='btn' style='background: #6c757d;'>Full Debug</a>";
echo "<a href='logout.php' class='btn' style='background: #dc3545;'>Logout</a>";
echo "</div>";

echo "<div class='info'>";
echo "<strong>Troubleshooting Tips:</strong><br>";
echo "• If dashboard links show 'Access Denied', the .htaccess file is blocking access<br>";
echo "• If you see a blank page, check PHP error logs<br>";
echo "• If you see 'Page Not Found', the file path is incorrect<br>";
echo "• Make sure you're logged in before accessing dashboards<br>";
echo "• Try disabling .htaccess temporarily to test if it's causing issues";
echo "</div>";
?>
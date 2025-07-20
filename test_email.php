<?php
/**
 * Email Test Script for Attendance Management System
 * This file tests if PHPMailer is working correctly
 */

// Include PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email configuration (update these with your settings)
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'your-email@gmail.com'; // Change this
$smtp_password = 'your-app-password';    // Change this
$from_email = 'your-email@gmail.com';    // Change this
$from_name = 'Attendance System Test';

// Test email settings
$test_email = 'test@example.com'; // Change this to your test email

echo "<h2>📧 PHPMailer Test - Attendance Management System</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: orange; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .test-section { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
    .config-table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    .config-table th, .config-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .config-table th { background-color: #f2f2f2; }
    .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    .btn:hover { background: #0056b3; }
</style>";

// Test 1: Check if PHPMailer is installed
echo "<div class='test-section'>";
echo "<h3>🔍 Test 1: PHPMailer Installation Check</h3>";

if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "<div class='success'>✓ PHPMailer is installed and available</div>";
} else {
    echo "<div class='error'>❌ PHPMailer is not installed</div>";
    echo "<div class='info'>Please run: <code>composer require phpmailer/phpmailer</code></div>";
    exit();
}

// Test 2: Display current configuration
echo "<div class='test-section'>";
echo "<h3>⚙️ Test 2: Current Email Configuration</h3>";
echo "<table class='config-table'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";
echo "<tr><td>SMTP Host</td><td>$smtp_host</td><td>" . ($smtp_host ? "✓ Set" : "❌ Not set") . "</td></tr>";
echo "<tr><td>SMTP Port</td><td>$smtp_port</td><td>" . ($smtp_port ? "✓ Set" : "❌ Not set") . "</td></tr>";
echo "<tr><td>SMTP Username</td><td>" . ($smtp_username != 'your-email@gmail.com' ? $smtp_username : '<span style="color: red;">Not configured</span>') . "</td><td>" . ($smtp_username != 'your-email@gmail.com' ? "✓ Set" : "❌ Not set") . "</td></tr>";
echo "<tr><td>SMTP Password</td><td>" . ($smtp_password != 'your-app-password' ? "***hidden***" : '<span style="color: red;">Not configured</span>') . "</td><td>" . ($smtp_password != 'your-app-password' ? "✓ Set" : "❌ Not set") . "</td></tr>";
echo "<tr><td>From Email</td><td>$from_email</td><td>" . ($from_email != 'your-email@gmail.com' ? "✓ Set" : "❌ Not set") . "</td></tr>";
echo "<tr><td>Test Email</td><td>$test_email</td><td>" . ($test_email != 'test@example.com' ? "✓ Set" : "❌ Not set") . "</td></tr>";
echo "</table>";

if ($smtp_username == 'your-email@gmail.com' || $smtp_password == 'your-app-password' || $from_email == 'your-email@gmail.com' || $test_email == 'test@example.com') {
    echo "<div class='warning'>⚠️ Please update the email configuration at the top of this file before testing.</div>";
    echo "<div class='info'>Instructions:</div>";
    echo "<ol>";
    echo "<li>Update SMTP username with your Gmail address</li>";
    echo "<li>Update SMTP password with your Gmail app password</li>";
    echo "<li>Update from_email with your Gmail address</li>";
    echo "<li>Update test_email with the email where you want to receive test emails</li>";
    echo "</ol>";
    echo "<div class='info'><strong>Note:</strong> For Gmail, you need to use an App Password, not your regular password.</div>";
    echo "<a href='https://support.google.com/accounts/answer/185833' target='_blank' class='btn'>How to create Gmail App Password</a>";
    echo "</div>";
    exit();
}

// Test 3: Test SMTP Connection
echo "<div class='test-section'>";
echo "<h3>🔌 Test 3: SMTP Connection Test</h3>";

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_username;
    $mail->Password = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp_port;
    
    // Enable debug output
    $mail->SMTPDebug = 0; // Set to 2 for detailed debug output
    
    // Test connection
    $mail->smtpConnect();
    echo "<div class='success'>✓ SMTP connection successful</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ SMTP connection failed: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Common solutions:</div>";
    echo "<ul>";
    echo "<li>Check if Gmail 2-factor authentication is enabled</li>";
    echo "<li>Verify you're using an App Password, not your regular password</li>";
    echo "<li>Check if 'Less secure app access' is enabled (if not using App Password)</li>";
    echo "<li>Verify SMTP host and port settings</li>";
    echo "</ul>";
    echo "</div>";
    exit();
}

// Test 4: Send Test Email
echo "<div class='test-section'>";
echo "<h3>📤 Test 4: Send Test Email</h3>";

if (isset($_POST['send_test_email'])) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;
        
        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($test_email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Attendance System - Email Test';
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #333;'>🎉 Email Test Successful!</h2>
            <p>This is a test email from your Attendance Management System.</p>
            <div style='background-color: #f4f4f4; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                <h3>Test Details:</h3>
                <ul>
                    <li><strong>Sent from:</strong> $from_email</li>
                    <li><strong>Sent to:</strong> $test_email</li>
                    <li><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</li>
                    <li><strong>SMTP Server:</strong> $smtp_host:$smtp_port</li>
                </ul>
            </div>
            <p>If you received this email, your PHPMailer configuration is working correctly!</p>
            <hr>
            <p style='color: #666; font-size: 12px;'>This is an automated test email from the Attendance Management System.</p>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = "Email Test Successful!\n\nThis is a test email from your Attendance Management System.\n\nSent from: $from_email\nSent to: $test_email\nDate: " . date('Y-m-d H:i:s') . "\n\nIf you received this email, your PHPMailer configuration is working correctly!";
        
        $mail->send();
        echo "<div class='success'>✓ Test email sent successfully to $test_email</div>";
        echo "<div class='info'>Please check your email inbox (and spam folder) to confirm receipt.</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Failed to send test email: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='info'>Click the button below to send a test email to: <strong>$test_email</strong></div>";
    echo "<form method='POST'>";
    echo "<input type='submit' name='send_test_email' value='Send Test Email' class='btn' style='background: #28a745;'>";
    echo "</form>";
}

echo "</div>";

// Test 5: OTP Email Test
echo "<div class='test-section'>";
echo "<h3>🔐 Test 5: OTP Email Test</h3>";

if (isset($_POST['send_otp_email'])) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;
        
        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($test_email);
        
        // Generate test OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification OTP - Test';
        
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #333;'>Email Verification</h2>
            <p>Your verification code is:</p>
            <div style='background-color: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0;'>
                <h1 style='color: #007bff; font-size: 32px; margin: 0;'>$otp</h1>
            </div>
            <p>This code will expire in 10 minutes.</p>
            <p><strong>Note:</strong> This is a test OTP email. In the actual system, this code would be used for email verification.</p>
            <hr>
            <p style='color: #666; font-size: 12px;'>This is a test email from the Attendance Management System.</p>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = "Email Verification\n\nYour verification code is: $otp\n\nThis code will expire in 10 minutes.\n\nNote: This is a test OTP email.";
        
        $mail->send();
        echo "<div class='success'>✓ OTP test email sent successfully to $test_email</div>";
        echo "<div class='info'>Test OTP Code: <strong>$otp</strong></div>";
        echo "<div class='info'>Please check your email to see the OTP format.</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Failed to send OTP test email: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='info'>Click the button below to send a test OTP email to: <strong>$test_email</strong></div>";
    echo "<form method='POST'>";
    echo "<input type='submit' name='send_otp_email' value='Send OTP Test Email' class='btn' style='background: #ffc107; color: #000;'>";
    echo "</form>";
}

echo "</div>";

// Test 6: System Integration Test
echo "<div class='test-section'>";
echo "<h3>🔗 Test 6: System Integration Test</h3>";

// Check if the main email helper file exists
if (file_exists('php/email_helper.php')) {
    echo "<div class='success'>✓ Email helper file exists</div>";
    
    // Test if we can include it
    try {
        require_once 'php/email_helper.php';
        echo "<div class='success'>✓ Email helper file can be included</div>";
        
        // Test if EmailHelper class exists
        if (class_exists('EmailHelper')) {
            echo "<div class='success'>✓ EmailHelper class is available</div>";
        } else {
            echo "<div class='error'>❌ EmailHelper class not found</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error including email helper: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='error'>❌ Email helper file not found</div>";
}

echo "</div>";

// Summary
echo "<div class='test-section'>";
echo "<h3>📊 Test Summary</h3>";
echo "<div class='info'>";
echo "<strong>Configuration Status:</strong><br>";
echo "• SMTP Host: $smtp_host<br>";
echo "• SMTP Port: $smtp_port<br>";
echo "• From Email: $from_email<br>";
echo "• Test Email: $test_email<br>";
echo "</div>";
echo "<div class='info'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Update the email configuration in <code>php/config.php</code><br>";
echo "2. Test the actual system registration and login<br>";
echo "3. Verify OTP emails are working in the signup process<br>";
echo "</div>";
echo "</div>";

// Navigation
echo "<div style='text-align: center; margin: 20px 0;'>";
echo "<a href='index.php' class='btn'>Go to Login Page</a>";
echo "<a href='setup_database.php' class='btn' style='background: #6c757d;'>Setup Database</a>";
echo "<a href='signup.php' class='btn' style='background: #28a745;'>Test Signup</a>";
echo "</div>";

echo "<div class='info'>";
echo "<strong>Need Help?</strong><br>";
echo "• <a href='https://support.google.com/accounts/answer/185833' target='_blank'>How to create Gmail App Password</a><br>";
echo "• <a href='https://github.com/PHPMailer/PHPMailer' target='_blank'>PHPMailer Documentation</a><br>";
echo "• Check your server's error logs for detailed error messages";
echo "</div>";
?>
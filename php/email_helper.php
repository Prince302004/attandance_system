<?php
require_once 'config.php';
require 'vendor/autoload.php'; // Make sure to install PHPMailer via composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }
    
    private function setupMailer() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            
            // Default settings
            $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
        }
    }
    
    public function sendOTP($email, $otp) {
        try {
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Email Verification OTP';
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Email Verification</h2>
                <p>Your verification code is:</p>
                <div style='background-color: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #007bff; font-size: 32px; margin: 0;'>$otp</h1>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this code, please ignore this email.</p>
            </div>";
            
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendPasswordResetOTP($email, $otp, $full_name) {
        try {
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Password Reset OTP - Attendance System';
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Password Reset OTP</h2>
                <p>Hello $full_name,</p>
                <p>You have requested to reset your password. Use the OTP below to proceed:</p>
                <div style='background-color: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px;'>
                    <h1 style='color: #007bff; font-size: 32px; margin: 0; letter-spacing: 5px;'>$otp</h1>
                </div>
                <p><strong>This OTP will expire in 10 minutes.</strong></p>
                <p>If you didn't request this reset, please ignore this email.</p>
                <hr style='margin: 20px 0;'>
                <p style='color: #666; font-size: 12px;'>This is an automated email from the Attendance Management System.</p>
            </div>";
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = "Password Reset OTP\n\nHello $full_name,\n\nYour password reset OTP is: $otp\n\nThis OTP will expire in 10 minutes.\n\nIf you didn't request this reset, please ignore this email.";
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            return false;
        }
    }
}

// OTP generation function
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Store OTP in database
function storeOTP($email, $otp) {
    global $conn;
    
    // Delete existing OTP for this email
    $delete_sql = "DELETE FROM otp_verification WHERE email = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("s", $email);
    $delete_stmt->execute();
    
    // Insert new OTP
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $insert_sql = "INSERT INTO otp_verification (email, otp, expires_at) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $email, $otp, $expires_at);
    
    return $insert_stmt->execute();
}

// Verify OTP
function verifyOTP($email, $otp) {
    global $conn;
    
    $sql = "SELECT * FROM otp_verification WHERE email = ? AND otp = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete the used OTP
        $delete_sql = "DELETE FROM otp_verification WHERE email = ? AND otp = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ss", $email, $otp);
        $delete_stmt->execute();
        
        return true;
    }
    
    return false;
}
?>
<?php
require_once 'php/config.php';
require_once 'php/email_helper.php';

$error = '';
$success = '';

// Check if user has requested OTP
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit();
}

$reset_email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify_otp'])) {
        $otp = trim($_POST['otp']);
        
        if (empty($otp) || strlen($otp) != 6) {
            $error = 'Please enter a valid 6-digit OTP';
        } else {
            // Verify OTP
            $emailHelper = new EmailHelper();
            if (verifyOTP($reset_email, $otp)) {
                $_SESSION['otp_verified'] = true;
                $success = 'OTP verified successfully. You can now set your new password.';
            } else {
                $error = 'Invalid or expired OTP. Please try again.';
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password) || strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
            $error = 'Please verify OTP first';
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $reset_email);
            
            if ($update_stmt->execute()) {
                // Clear session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                
                $success = 'Password has been reset successfully. You can now login with your new password.';
                
                // Redirect to login after 3 seconds
                header("refresh:3;url=index.php");
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management System - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold text-dark">Reset Password</h2>
                            <p class="text-muted">Reset your password using OTP</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-envelope me-1"></i>
                                OTP sent to: <?php echo htmlspecialchars($reset_email); ?>
                            </small>
                        </div>
                        
                        <?php if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']): ?>
                            <!-- OTP Verification Form -->
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="otp" class="form-label">Enter OTP</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="text" class="form-control" id="otp" name="otp" 
                                               maxlength="6" pattern="[0-9]{6}" 
                                               placeholder="123456" required>
                                    </div>
                                    <small class="text-muted">Enter the 6-digit OTP sent to your email</small>
                                </div>
                                
                                <button type="submit" name="verify_otp" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-check me-2"></i>Verify OTP
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Password Reset Form -->
                            <form method="POST" action="" id="passwordForm">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="new_password" 
                                               name="new_password" minlength="6" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Password must be at least 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" minlength="6" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" name="reset_password" class="btn btn-success w-100 mb-3">
                                    <i class="fas fa-save me-2"></i>Reset Password
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="forgot_password.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            const passwordField = document.getElementById('new_password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordField = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Password confirmation validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
        
        // OTP input formatting
        document.getElementById('otp').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    </script>
</body>
</html>
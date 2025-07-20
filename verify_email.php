<?php
require_once 'php/config.php';
require_once 'php/email_helper.php';

$error = '';
$success = '';

// Use email from session if available
$email = isset($_SESSION['verify_email']) ? $_SESSION['verify_email'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify'])) {
        $email = trim($_POST['email']);
        $otp = trim($_POST['otp']);
        
        if (empty($email) || empty($otp)) {
            $error = 'Please fill in all fields';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            if (verifyOTP($email, $otp)) {
                // Update user verification status
                $update_sql = "UPDATE users SET is_verified = 1 WHERE email = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("s", $email);
                
                if ($update_stmt->execute()) {
                    // Log the user in and redirect to dashboard
                    $user_sql = "SELECT * FROM users WHERE email = ?";
                    $user_stmt = $conn->prepare($user_sql);
                    $user_stmt->bind_param("s", $email);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    if ($user_result->num_rows > 0) {
                        $user = $user_result->fetch_assoc();
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['full_name'] = $user['full_name'];
                        unset($_SESSION['verify_email']);
                        // Redirect to dashboard
                        switch ($user['role']) {
                            case 'student':
                                header('Location: student/dashboard.php');
                                break;
                            case 'teacher':
                                header('Location: teacher/dashboard.php');
                                break;
                            case 'admin':
                                header('Location: admin/dashboard.php');
                                break;
                        }
                        exit();
                    }
                    $success = 'Email verified successfully! You can now login to your account.';
                } else {
                    $error = 'Verification failed. Please try again.';
                }
            } else {
                $error = 'Invalid OTP or OTP has expired. Please check your email and try again.';
            }
        }
    } elseif (isset($_POST['resend'])) {
        $email = trim($_POST['email']);
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            // Check if user exists and is not verified
            $check_sql = "SELECT id FROM users WHERE email = ? AND is_verified = 0";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Generate and send new OTP
                $otp = generateOTP();
                if (storeOTP($email, $otp)) {
                    $emailHelper = new EmailHelper();
                    if ($emailHelper->sendOTP($email, $otp)) {
                        $success = 'New verification code has been sent to your email.';
                    } else {
                        $error = 'Failed to send verification email. Please try again.';
                    }
                } else {
                    $error = 'Failed to generate verification code. Please try again.';
                }
            } else {
                $error = 'Email not found or already verified.';
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
    <title>Attendance Management System - Email Verification</title>
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
                            <i class="fas fa-envelope-open fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold text-dark">Email Verification</h2>
                            <p class="text-muted">Enter the verification code sent to your email</p>
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
                        
                        <form method="POST" action="" id="verifyForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="otp" class="form-label">Verification Code</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" class="form-control" id="otp" name="otp" maxlength="6" required>
                                </div>
                                <small class="text-muted">Enter the 6-digit code sent to your email</small>
                            </div>
                            
                            <button type="submit" name="verify" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check me-2"></i>Verify Email
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Didn't receive the code?</p>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="email" id="resendEmail" value="<?php echo htmlspecialchars($email); ?>">
                                <button type="submit" name="resend" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Resend Code
                                </button>
                            </form>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill resend email field
        document.getElementById('email').addEventListener('input', function() {
            document.getElementById('resendEmail').value = this.value;
        });
        
        // Auto-format OTP input
        document.getElementById('otp').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    </script>
</body>
</html>
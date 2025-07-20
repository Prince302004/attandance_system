<?php
require_once '../php/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $mobile = trim($_POST['mobile']);
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name) || empty($role)) {
            $error = 'Please fill in all required fields';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address';
        } else {
            // Check if username or email already exists
            $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = 'Username or email already exists';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO users (username, email, password, role, full_name, mobile, is_verified) VALUES (?, ?, ?, ?, ?, ?, 1)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssssss", $username, $email, $hashed_password, $role, $full_name, $mobile);
                
                if ($insert_stmt->execute()) {
                    $success = 'User added successfully';
                } else {
                    $error = 'Failed to add user';
                }
            }
        }
    }
}

// Get system statistics
$stats_sql = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students,
                (SELECT COUNT(*) FROM users WHERE role = 'teacher') as total_teachers,
                (SELECT COUNT(*) FROM subjects) as total_subjects,
                (SELECT COUNT(*) FROM attendance) as total_attendance,
                (SELECT COUNT(*) FROM attendance WHERE date = CURDATE()) as today_attendance,
                (SELECT COUNT(*) FROM attendance WHERE status = 'present') as present_count,
                (SELECT COUNT(*) FROM attendance WHERE status = 'absent') as absent_count,
                (SELECT COUNT(*) FROM attendance WHERE status = 'late') as late_count";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$total_students = $stats['total_students'] ?: 0;
$total_teachers = $stats['total_teachers'] ?: 0;
$total_subjects = $stats['total_subjects'] ?: 0;
$total_attendance = $stats['total_attendance'] ?: 0;
$today_attendance = $stats['today_attendance'] ?: 0;
$present_count = $stats['present_count'] ?: 0;
$absent_count = $stats['absent_count'] ?: 0;
$late_count = $stats['late_count'] ?: 0;

// Get recent activities
$recent_attendance_sql = "SELECT a.*, u.full_name as student_name, s.subject_name 
                         FROM attendance a 
                         JOIN users u ON a.student_id = u.id 
                         JOIN subjects s ON a.subject_id = s.id 
                         ORDER BY a.created_at DESC LIMIT 10";
$recent_attendance_result = $conn->query($recent_attendance_sql);

// Get users by role
$students_sql = "SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5";
$students_result = $conn->query($students_sql);

$teachers_sql = "SELECT * FROM users WHERE role = 'teacher' ORDER BY created_at DESC LIMIT 5";
$teachers_result = $conn->query($teachers_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>Admin Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i><?php echo $_SESSION['full_name']; ?>
                </span>
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="fas fa-shield-alt me-2 text-primary"></i>
                            Welcome, <?php echo $_SESSION['full_name']; ?>!
                        </h4>
                        <p class="card-text text-muted">System Administration Dashboard - Today is <?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $total_students; ?></h5>
                    <p class="text-muted mb-0">Total Students</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon info">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $total_teachers; ?></h5>
                    <p class="text-muted mb-0">Total Teachers</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon success">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $total_subjects; ?></h5>
                    <p class="text-muted mb-0">Total Subjects</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon warning">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $today_attendance; ?></h5>
                    <p class="text-muted mb-0">Today's Attendance</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-tools me-2 text-primary"></i>
                            Quick Actions
                        </h5>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-user-plus me-1"></i>Add User
                                </button>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="manage_users.php" class="btn btn-info w-100">
                                    <i class="fas fa-users-cog me-1"></i>Manage Users
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="attendance_reports.php" class="btn btn-success w-100">
                                    <i class="fas fa-chart-bar me-1"></i>Attendance Reports
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="system_settings.php" class="btn btn-warning w-100">
                                    <i class="fas fa-cog me-1"></i>System Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities and User Lists -->
        <div class="row">
            <!-- Recent Attendance -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Recent Attendance Activities
                        </h5>
                        
                        <?php if ($recent_attendance_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($attendance = $recent_attendance_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($attendance['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($attendance['subject_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    $status_class = $attendance['status'] === 'present' ? 'success' : ($attendance['status'] === 'late' ? 'warning' : 'danger');
                                                    $status_icon = $attendance['status'] === 'present' ? 'check' : ($attendance['status'] === 'late' ? 'clock' : 'times');
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                        <?php echo ucfirst($attendance['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($attendance['date'])); ?></td>
                                                <td><?php echo $attendance['time_in']; ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No recent activities</h5>
                                <p class="text-muted">Attendance activities will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user-plus me-2 text-primary"></i>
                            Recent Users
                        </h5>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Recent Students</h6>
                            <?php if ($students_result->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php while ($student = $students_result->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $student['username']; ?></small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">Student</span>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">No students registered yet.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div>
                            <h6 class="text-muted">Recent Teachers</h6>
                            <?php if ($teachers_result->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($teacher['full_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $teacher['username']; ?></small>
                                            </div>
                                            <span class="badge bg-info rounded-pill">Teacher</span>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted">No teachers registered yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2 text-primary"></i>
                        Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="teacher">Teacher</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin-dashboard.js"></script>
</body>
</html>
<?php
require_once '../php/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get student's subjects
$subjects_sql = "SELECT s.*, u.full_name as teacher_name 
                 FROM subjects s 
                 JOIN users u ON s.teacher_id = u.id 
                 ORDER BY s.year DESC, s.semester DESC, s.subject_name";
$subjects_result = $conn->query($subjects_sql);

// Get today's attendance
$today = date('Y-m-d');
$attendance_sql = "SELECT a.*, s.subject_name 
                   FROM attendance a 
                   JOIN subjects s ON a.subject_id = s.id 
                   WHERE a.student_id = ? AND a.date = ?";
$attendance_stmt = $conn->prepare($attendance_sql);
$attendance_stmt->bind_param("is", $user_id, $today);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->get_result();

$today_attendance = [];
while ($row = $attendance_result->fetch_assoc()) {
    $today_attendance[$row['subject_id']] = $row;
}

// Get attendance statistics
$stats_sql = "SELECT 
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days
              FROM attendance 
              WHERE student_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$total_days = $stats['total_days'] ?: 0;
$present_days = $stats['present_days'] ?: 0;
$absent_days = $stats['absent_days'] ?: 0;
$late_days = $stats['late_days'] ?: 0;
$attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>Student Dashboard
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
                            <i class="fas fa-user-graduate me-2 text-primary"></i>
                            Welcome, <?php echo $_SESSION['full_name']; ?>!
                        </h4>
                        <p class="card-text text-muted">Today is <?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $present_days; ?></h5>
                    <p class="text-muted mb-0">Present Days</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon danger">
                        <i class="fas fa-times"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $absent_days; ?></h5>
                    <p class="text-muted mb-0">Absent Days</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $late_days; ?></h5>
                    <p class="text-muted mb-0">Late Days</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon primary">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $attendance_percentage; ?>%</h5>
                    <p class="text-muted mb-0">Attendance Rate</p>
                </div>
            </div>
        </div>

        <!-- Location Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            Location Status
                        </h5>
                        <div id="locationStatus" class="location-status outside">
                            <i class="fas fa-spinner fa-spin me-2"></i>Checking location...
                        </div>
                        <small class="text-muted">You must be on campus to mark attendance</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subjects and Attendance -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-book me-2 text-primary"></i>
                            My Subjects & Attendance
                        </h5>
                        
                        <?php if ($subjects_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th>Year</th>
                                            <th>Semester</th>
                                            <th>Today's Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($subject['teacher_name']); ?></td>
                                                <td><?php echo $subject['year']; ?></td>
                                                <td>Semester <?php echo $subject['semester']; ?></td>
                                                <td>
                                                    <?php if (isset($today_attendance[$subject['id']])): ?>
                                                        <?php 
                                                        $status = $today_attendance[$subject['id']]['status'];
                                                        $status_class = $status === 'present' ? 'success' : ($status === 'late' ? 'warning' : 'danger');
                                                        $status_icon = $status === 'present' ? 'check' : ($status === 'late' ? 'clock' : 'times');
                                                        ?>
                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                            <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                            <?php echo ucfirst($status); ?>
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo $today_attendance[$subject['id']]['time_in']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-minus me-1"></i>
                                                            Not Marked
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!isset($today_attendance[$subject['id']])): ?>
                                                        <button class="btn btn-primary btn-sm mark-attendance" 
                                                                data-subject-id="<?php echo $subject['id']; ?>"
                                                                data-subject-name="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                                            <i class="fas fa-check me-1"></i>Mark Attendance
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>Marked
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No subjects assigned</h5>
                                <p class="text-muted">Please contact your administrator to assign subjects.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2 text-primary"></i>
                        Mark Attendance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="attendanceMessage"></div>
                    <div class="text-center">
                        <div id="attendanceSpinner" class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/student-dashboard.js"></script>
</body>
</html>
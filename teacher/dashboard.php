<?php
require_once '../php/config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_subject'])) {
        $subject_name = trim($_POST['subject_name']);
        $year = $_POST['year'];
        $semester = $_POST['semester'];
        
        if (empty($subject_name) || empty($year) || empty($semester)) {
            $error = 'Please fill in all fields';
        } else {
            $insert_sql = "INSERT INTO subjects (subject_name, teacher_id, year, semester) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("siii", $subject_name, $user_id, $year, $semester);
            
            if ($insert_stmt->execute()) {
                $success = 'Subject added successfully';
            } else {
                $error = 'Failed to add subject';
            }
        }
    } elseif (isset($_POST['edit_subject'])) {
        $subject_id = $_POST['subject_id'];
        $subject_name = trim($_POST['subject_name']);
        $year = $_POST['year'];
        $semester = $_POST['semester'];
        
        if (empty($subject_name) || empty($year) || empty($semester)) {
            $error = 'Please fill in all fields';
        } else {
            $update_sql = "UPDATE subjects SET subject_name = ?, year = ?, semester = ? WHERE id = ? AND teacher_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("siiii", $subject_name, $year, $semester, $subject_id, $user_id);
            
            if ($update_stmt->execute()) {
                $success = 'Subject updated successfully';
            } else {
                $error = 'Failed to update subject';
            }
        }
    } elseif (isset($_POST['delete_subject'])) {
        $subject_id = $_POST['subject_id'];
        
        $delete_sql = "DELETE FROM subjects WHERE id = ? AND teacher_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $subject_id, $user_id);
        
        if ($delete_stmt->execute()) {
            $success = 'Subject deleted successfully';
        } else {
            $error = 'Failed to delete subject';
        }
    }
}

// Get teacher's subjects
$subjects_sql = "SELECT * FROM subjects WHERE teacher_id = ? ORDER BY year DESC, semester DESC, subject_name";
$subjects_stmt = $conn->prepare($subjects_sql);
$subjects_stmt->bind_param("i", $user_id);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();

// Get attendance statistics for teacher's subjects
$stats_sql = "SELECT 
                COUNT(DISTINCT a.student_id) as total_students,
                COUNT(a.id) as total_attendance,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
              FROM attendance a 
              JOIN subjects s ON a.subject_id = s.id 
              WHERE s.teacher_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

$total_students = $stats['total_students'] ?: 0;
$total_attendance = $stats['total_attendance'] ?: 0;
$present_count = $stats['present_count'] ?: 0;
$absent_count = $stats['absent_count'] ?: 0;
$late_count = $stats['late_count'] ?: 0;
$attendance_percentage = $total_attendance > 0 ? round(($present_count / $total_attendance) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chalkboard-teacher me-2"></i>Teacher Dashboard
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
                            <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                            Welcome, <?php echo $_SESSION['full_name']; ?>!
                        </h4>
                        <p class="card-text text-muted">Today is <?php echo date('l, F j, Y'); ?></p>
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
                    <div class="dashboard-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $present_count; ?></h5>
                    <p class="text-muted mb-0">Present</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $late_count; ?></h5>
                    <p class="text-muted mb-0">Late</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="dashboard-icon danger">
                        <i class="fas fa-times"></i>
                    </div>
                    <h5 class="mb-1"><?php echo $absent_count; ?></h5>
                    <p class="text-muted mb-0">Absent</p>
                </div>
            </div>
        </div>

        <!-- Subject Management -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-book me-2 text-primary"></i>
                                My Subjects
                            </h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                                <i class="fas fa-plus me-1"></i>Add Subject
                            </button>
                        </div>
                        
                        <?php if ($subjects_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Year</th>
                                            <th>Semester</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($subject['subject_name']); ?></strong></td>
                                                <td><?php echo $subject['year']; ?></td>
                                                <td>Semester <?php echo $subject['semester']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary edit-subject" 
                                                            data-subject-id="<?php echo $subject['id']; ?>"
                                                            data-subject-name="<?php echo htmlspecialchars($subject['subject_name']); ?>"
                                                            data-year="<?php echo $subject['year']; ?>"
                                                            data-semester="<?php echo $subject['semester']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-subject"
                                                            data-subject-id="<?php echo $subject['id']; ?>"
                                                            data-subject-name="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <a href="attendance_report.php?subject_id=<?php echo $subject['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No subjects added</h5>
                                <p class="text-muted">Add your first subject to get started.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            Attendance Overview
                        </h5>
                        <p class="card-text">View detailed attendance reports and analytics for your subjects.</p>
                        <a href="attendance_overview.php" class="btn btn-primary">
                            <i class="fas fa-chart-bar me-1"></i>View Reports
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            Today's Attendance
                        </h5>
                        <p class="card-text">Check today's attendance status for all your subjects.</p>
                        <a href="today_attendance.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-1"></i>Check Today
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2 text-primary"></i>
                        Add New Subject
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                    <?php for ($y = date('Y'); $y <= date('Y') + 2; $y++): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="semester" class="form-label">Semester</label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_subject" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2 text-primary"></i>
                        Edit Subject
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" id="edit_subject_id" name="subject_id">
                        <div class="mb-3">
                            <label for="edit_subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="edit_subject_name" name="subject_name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_year" class="form-label">Year</label>
                                <select class="form-select" id="edit_year" name="year" required>
                                    <option value="">Select Year</option>
                                    <?php for ($y = date('Y'); $y <= date('Y') + 2; $y++): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_semester" class="form-label">Semester</label>
                                <select class="form-select" id="edit_semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_subject" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Subject Modal -->
    <div class="modal fade" id="deleteSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2 text-danger"></i>
                        Delete Subject
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the subject "<span id="deleteSubjectName"></span>"?</p>
                    <p class="text-danger"><small>This action cannot be undone and will also delete all related attendance records.</small></p>
                </div>
                <form method="POST" action="">
                    <input type="hidden" id="delete_subject_id" name="subject_id">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_subject" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/teacher-dashboard.js"></script>
</body>
</html>
<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$subject_id = $_POST['subject_id'] ?? '';
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';

if (empty($subject_id) || empty($latitude) || empty($longitude)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate subject exists and user has access
$subject_sql = "SELECT s.*, u.full_name as teacher_name 
                FROM subjects s 
                JOIN users u ON s.teacher_id = u.id 
                WHERE s.id = ?";
$subject_stmt = $conn->prepare($subject_sql);
$subject_stmt->bind_param("i", $subject_id);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();

if ($subject_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Subject not found']);
    exit();
}

$subject = $subject_result->fetch_assoc();

// Check if attendance already marked for today
$today = date('Y-m-d');
$check_sql = "SELECT * FROM attendance WHERE student_id = ? AND subject_id = ? AND date = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iis", $user_id, $subject_id, $today);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Attendance already marked for today']);
    exit();
}

// Verify location is inside campus
$distance = calculateDistance($latitude, $longitude, CAMPUS_LAT, CAMPUS_LNG);
$isInsideCampus = $distance <= CAMPUS_RADIUS;

if (!$isInsideCampus) {
    echo json_encode(['success' => false, 'message' => 'You must be on campus to mark attendance']);
    exit();
}

// Determine attendance status based on time
$current_time = date('H:i:s');
$status = 'present';

// Consider late if after 9:30 AM (adjust as needed)
if ($current_time > '09:30:00') {
    $status = 'late';
}

// Insert attendance record
$insert_sql = "INSERT INTO attendance (student_id, subject_id, date, time_in, latitude, longitude, status) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iisddss", $user_id, $subject_id, $today, $current_time, $latitude, $longitude, $status);

if ($insert_stmt->execute()) {
    $location_status = $isInsideCampus ? 'Inside Campus' : 'Outside Campus';
    
    echo json_encode([
        'success' => true,
        'message' => 'Attendance marked successfully',
        'time' => $current_time,
        'status' => $status,
        'location_status' => $location_status,
        'distance' => round($distance, 2)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to mark attendance']);
}

// Calculate distance between two points using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371e3; // Earth's radius in meters
    $φ1 = $lat1 * M_PI / 180;
    $φ2 = $lat2 * M_PI / 180;
    $Δφ = ($lat2 - $lat1) * M_PI / 180;
    $Δλ = ($lon2 - $lon1) * M_PI / 180;

    $a = sin($Δφ / 2) * sin($Δφ / 2) +
         cos($φ1) * cos($φ2) *
         sin($Δλ / 2) * sin($Δλ / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $R * $c;
}
?>
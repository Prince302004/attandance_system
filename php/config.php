<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'attendance_system');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db(DB_NAME);
    
    // Create tables
    createTables($conn);
} else {
    die("Error creating database: " . $conn->error);
}

// Ensure we're using the correct database
if (!$conn->select_db(DB_NAME)) {
    die("Error selecting database: " . $conn->error);
}

function createTables($conn) {
    // Users table
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'teacher', 'admin') NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        mobile VARCHAR(15),
        is_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Subjects table
    $subjects_table = "CREATE TABLE IF NOT EXISTS subjects (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        subject_name VARCHAR(100) NOT NULL,
        teacher_id INT(11),
        year INT(4) NOT NULL,
        semester INT(1) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Attendance table
    $attendance_table = "CREATE TABLE IF NOT EXISTS attendance (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        student_id INT(11) NOT NULL,
        subject_id INT(11) NOT NULL,
        date DATE NOT NULL,
        time_in TIME,
        time_out TIME,
        latitude DECIMAL(10,8),
        longitude DECIMAL(11,8),
        status ENUM('present', 'absent', 'late') DEFAULT 'present',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    )";
    
    // OTP table for email verification
    $otp_table = "CREATE TABLE IF NOT EXISTS otp_verification (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        otp VARCHAR(6) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Password reset table
    $reset_table = "CREATE TABLE IF NOT EXISTS password_reset (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Execute table creation
    $conn->query($users_table);
    $conn->query($subjects_table);
    $conn->query($attendance_table);
    $conn->query($otp_table);
    $conn->query($reset_table);
    
    // Create default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_check = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $result = $conn->query($admin_check);
    
    if ($result->num_rows == 0) {
        $admin_insert = "INSERT INTO users (username, email, password, role, full_name, is_verified) 
                        VALUES ('admin', 'admin@attendance.com', '$admin_password', 'admin', 'System Administrator', 1)";
        $conn->query($admin_insert);
    }
}

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change this
define('SMTP_PASSWORD', 'your-app-password'); // Change this
define('FROM_EMAIL', 'your-email@gmail.com'); // Change this
define('FROM_NAME', 'Attendance System');

// Session configuration
session_start();

// Campus coordinates (update these with your actual campus coordinates)
define('CAMPUS_LAT', 12.9716); // Example: Bangalore coordinates
define('CAMPUS_LNG', 77.5946);
define('CAMPUS_RADIUS', 500); // Radius in meters
?>
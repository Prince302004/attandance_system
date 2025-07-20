<?php
/**
 * Database Setup Script for Attendance Management System
 * Run this file once to create the database and tables
 */

// Database configuration
$host = 'localhost';
$username = 'root';  // Change this to your MySQL username
$password = '';      // Change this to your MySQL password
$database = 'attendance_system';

echo "<h2>Attendance Management System - Database Setup</h2>";

try {
    // Create connection without database
    $conn = new mysqli($host, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✓ Connected to MySQL server successfully</p>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Database '$database' created successfully</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($database);
    echo "<p style='color: green;'>✓ Database selected successfully</p>";
    
    // Create tables
    $tables = [
        // Users table
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `email` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `role` ENUM('student', 'teacher', 'admin') NOT NULL,
            `full_name` VARCHAR(100) NOT NULL,
            `mobile` VARCHAR(15) NULL,
            `is_verified` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Subjects table
        "CREATE TABLE IF NOT EXISTS `subjects` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `subject_name` VARCHAR(100) NOT NULL,
            `teacher_id` INT(11) NULL,
            `year` INT(4) NOT NULL,
            `semester` INT(1) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Attendance table
        "CREATE TABLE IF NOT EXISTS `attendance` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `student_id` INT(11) NOT NULL,
            `subject_id` INT(11) NOT NULL,
            `date` DATE NOT NULL,
            `time_in` TIME NULL,
            `time_out` TIME NULL,
            `latitude` DECIMAL(10,8) NULL,
            `longitude` DECIMAL(11,8) NULL,
            `status` ENUM('present', 'absent', 'late') DEFAULT 'present',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // OTP verification table
        "CREATE TABLE IF NOT EXISTS `otp_verification` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(100) NOT NULL,
            `otp` VARCHAR(6) NOT NULL,
            `expires_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Password reset table
        "CREATE TABLE IF NOT EXISTS `password_reset` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `email` VARCHAR(100) NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `expires_at` TIMESTAMP NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($tables as $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Table created successfully</p>";
        } else {
            throw new Exception("Error creating table: " . $conn->error);
        }
    }
    
    // Create indexes
    $indexes = [
        "CREATE INDEX IF NOT EXISTS `idx_users_email` ON `users`(`email`)",
        "CREATE INDEX IF NOT EXISTS `idx_users_username` ON `users`(`username`)",
        "CREATE INDEX IF NOT EXISTS `idx_users_role` ON `users`(`role`)",
        "CREATE INDEX IF NOT EXISTS `idx_subjects_teacher` ON `subjects`(`teacher_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_subjects_year_semester` ON `subjects`(`year`, `semester`)",
        "CREATE INDEX IF NOT EXISTS `idx_attendance_student` ON `attendance`(`student_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_attendance_subject` ON `attendance`(`subject_id`)",
        "CREATE INDEX IF NOT EXISTS `idx_attendance_date` ON `attendance`(`date`)",
        "CREATE INDEX IF NOT EXISTS `idx_otp_email` ON `otp_verification`(`email`)",
        "CREATE INDEX IF NOT EXISTS `idx_otp_expires` ON `otp_verification`(`expires_at`)",
        "CREATE INDEX IF NOT EXISTS `idx_reset_email` ON `password_reset`(`email`)",
        "CREATE INDEX IF NOT EXISTS `idx_reset_token` ON `password_reset`(`token`)"
    ];
    
    foreach ($indexes as $sql) {
        $conn->query($sql);
    }
    echo "<p style='color: green;'>✓ Indexes created successfully</p>";
    
    // Insert default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_check = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
    $result = $conn->query($admin_check);
    
    if ($result->num_rows == 0) {
        $admin_insert = "INSERT INTO users (username, email, password, role, full_name, is_verified) 
                        VALUES ('admin', 'admin@attendance.com', '$admin_password', 'admin', 'System Administrator', 1)";
        if ($conn->query($admin_insert) === TRUE) {
            echo "<p style='color: green;'>✓ Default admin user created</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Warning: Could not create admin user: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ Admin user already exists</p>";
    }
    
    // Insert sample data
    $sample_data = [
        // Sample teachers
        "INSERT IGNORE INTO users (username, email, password, role, full_name, mobile, is_verified) VALUES
        ('teacher1', 'teacher1@example.com', '$admin_password', 'teacher', 'John Smith', '9876543210', 1),
        ('teacher2', 'teacher2@example.com', '$admin_password', 'teacher', 'Sarah Johnson', '9876543211', 1)",
        
        // Sample students
        "INSERT IGNORE INTO users (username, email, password, role, full_name, mobile, is_verified) VALUES
        ('student1', 'student1@example.com', '$admin_password', 'student', 'Alice Brown', '9876543212', 1),
        ('student2', 'student2@example.com', '$admin_password', 'student', 'Bob Wilson', '9876543213', 1),
        ('student3', 'student3@example.com', '$admin_password', 'student', 'Carol Davis', '9876543214', 1)",
        
        // Sample subjects
        "INSERT IGNORE INTO subjects (subject_name, teacher_id, year, semester) VALUES
        ('Mathematics', 2, 2024, 1),
        ('Physics', 2, 2024, 1),
        ('Computer Science', 3, 2024, 1),
        ('English Literature', 3, 2024, 2),
        ('Chemistry', 2, 2024, 2)"
    ];
    
    foreach ($sample_data as $sql) {
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color: green;'>✓ Sample data inserted</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Warning: Could not insert sample data: " . $conn->error . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>🎉 Database setup completed successfully!</h3>";
    echo "<p><strong>Default Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: admin, password: admin123</li>";
    echo "<li><strong>Teacher:</strong> username: teacher1, password: admin123</li>";
    echo "<li><strong>Student:</strong> username: student1, password: admin123</li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your MySQL configuration and try again.</p>";
}

$conn->close();
?>
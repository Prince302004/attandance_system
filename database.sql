-- Attendance Management System Database
-- Created for: Attendance Management System
-- Version: 1.0.0

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `attendance_system` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the database
USE `attendance_system`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects table
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_name` VARCHAR(100) NOT NULL,
    `teacher_id` INT(11) NULL,
    `year` INT(4) NOT NULL,
    `semester` INT(1) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance table
CREATE TABLE IF NOT EXISTS `attendance` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP verification table
CREATE TABLE IF NOT EXISTS `otp_verification` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `otp` VARCHAR(6) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset table
CREATE TABLE IF NOT EXISTS `password_reset` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(100) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
INSERT INTO `users` (`username`, `email`, `password`, `role`, `full_name`, `is_verified`) VALUES
('admin', 'admin@attendance.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 1);

-- Insert sample teachers
INSERT INTO `users` (`username`, `email`, `password`, `role`, `full_name`, `mobile`, `is_verified`) VALUES
('teacher1', 'teacher1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'John Smith', '9876543210', 1),
('teacher2', 'teacher2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Sarah Johnson', '9876543211', 1);

-- Insert sample students
INSERT INTO `users` (`username`, `email`, `password`, `role`, `full_name`, `mobile`, `is_verified`) VALUES
('student1', 'student1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Alice Brown', '9876543212', 1),
('student2', 'student2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Bob Wilson', '9876543213', 1),
('student3', 'student3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Carol Davis', '9876543214', 1);

-- Insert sample subjects
INSERT INTO `subjects` (`subject_name`, `teacher_id`, `year`, `semester`) VALUES
('Mathematics', 2, 2024, 1),
('Physics', 2, 2024, 1),
('Computer Science', 3, 2024, 1),
('English Literature', 3, 2024, 2),
('Chemistry', 2, 2024, 2);

-- Insert sample attendance records
INSERT INTO `attendance` (`student_id`, `subject_id`, `date`, `time_in`, `latitude`, `longitude`, `status`) VALUES
(4, 1, CURDATE(), '09:00:00', 12.9716, 77.5946, 'present'),
(5, 1, CURDATE(), '09:15:00', 12.9716, 77.5946, 'late'),
(6, 1, CURDATE(), '08:55:00', 12.9716, 77.5946, 'present'),
(4, 2, CURDATE(), '10:00:00', 12.9716, 77.5946, 'present'),
(5, 2, CURDATE(), '10:05:00', 12.9716, 77.5946, 'present'),
(6, 2, CURDATE(), '10:10:00', 12.9716, 77.5946, 'late');

-- Create indexes for better performance
CREATE INDEX `idx_users_email` ON `users`(`email`);
CREATE INDEX `idx_users_username` ON `users`(`username`);
CREATE INDEX `idx_users_role` ON `users`(`role`);
CREATE INDEX `idx_subjects_teacher` ON `subjects`(`teacher_id`);
CREATE INDEX `idx_subjects_year_semester` ON `subjects`(`year`, `semester`);
CREATE INDEX `idx_attendance_student` ON `attendance`(`student_id`);
CREATE INDEX `idx_attendance_subject` ON `attendance`(`subject_id`);
CREATE INDEX `idx_attendance_date` ON `attendance`(`date`);
CREATE INDEX `idx_otp_email` ON `otp_verification`(`email`);
CREATE INDEX `idx_otp_expires` ON `otp_verification`(`expires_at`);
CREATE INDEX `idx_reset_email` ON `password_reset`(`email`);
CREATE INDEX `idx_reset_token` ON `password_reset`(`token`);

-- Show success message
SELECT 'Database created successfully!' as message;
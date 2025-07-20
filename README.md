# Attendance Management System

A comprehensive web-based attendance management system built with PHP, MySQL, HTML, CSS, JavaScript, and Bootstrap. The system includes geolocation-based attendance marking, email OTP verification, and role-based access control.

## Features

### 🔐 Authentication & Security
- **Multi-role Login System**: Student, Teacher, and Admin roles
- **Email OTP Verification**: Secure email verification for new registrations
- **Password Reset**: Email-based password recovery system
- **Session Management**: Secure session handling with automatic logout
- **Input Validation**: Comprehensive form validation and sanitization

### 📍 Geolocation Attendance
- **Location-based Attendance**: Students can only mark attendance when on campus
- **Real-time Location Tracking**: Continuous location monitoring
- **Campus Boundary Detection**: Configurable campus radius for attendance marking
- **Mobile-friendly**: Works on smartphones with GPS capabilities

### 👨‍🎓 Student Features
- **Dashboard**: View attendance statistics and subject information
- **Attendance Marking**: Mark attendance for enrolled subjects
- **Real-time Status**: See current attendance status for each subject
- **Attendance History**: View personal attendance records
- **Location Verification**: Automatic campus location verification

### 👨‍🏫 Teacher Features
- **Subject Management**: Add, edit, and delete subjects
- **Year/Semester Organization**: Organize subjects by academic year and semester
- **Attendance Analysis**: View and analyze student attendance
- **Dashboard Statistics**: Overview of attendance metrics
- **Student Management**: View enrolled students for each subject

### 👨‍💼 Admin Features
- **User Management**: Add and manage all user accounts
- **System Overview**: Comprehensive system statistics
- **Recent Activities**: Monitor recent attendance activities
- **Role Management**: Assign and manage user roles
- **System Reports**: Generate attendance reports

### 📧 Email Integration
- **PHPMailer Integration**: Professional email sending
- **OTP Verification**: 6-digit verification codes
- **Password Reset**: Secure password recovery emails
- **Customizable Templates**: Professional email templates

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Email**: PHPMailer
- **Security**: Password hashing, SQL injection prevention, XSS protection

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (for PHPMailer)

### Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone <repository-url>
   cd attendance_system
   ```

2. **Install Dependencies**
   ```bash
   composer require phpmailer/phpmailer
   ```

3. **Database Configuration**
   - Create a MySQL database
   - Update database credentials in `php/config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'attendance_system');
     ```

4. **Email Configuration**
   - Update email settings in `php/config.php`:
     ```php
     define('SMTP_HOST', 'smtp.gmail.com');
     define('SMTP_PORT', 587);
     define('SMTP_USERNAME', 'your-email@gmail.com');
     define('SMTP_PASSWORD', 'your-app-password');
     define('FROM_EMAIL', 'your-email@gmail.com');
     define('FROM_NAME', 'Attendance System');
     ```

5. **Campus Location Configuration**
   - Update campus coordinates in `php/config.php`:
     ```php
     define('CAMPUS_LAT', 12.9716); // Your campus latitude
     define('CAMPUS_LNG', 77.5946); // Your campus longitude
     define('CAMPUS_RADIUS', 500); // Radius in meters
     ```

6. **File Permissions**
   ```bash
   chmod 755 -R attendance_system/
   chmod 777 -R uploads/ # If using file uploads
   ```

7. **Web Server Configuration**
   - Point your web server to the project directory
   - Ensure PHP has write permissions

## Default Login Credentials

- **Admin Account**:
  - Username: `admin`
  - Password: `admin123`
  - Email: `admin@attendance.com`

## Usage

### For Students
1. Register with email verification
2. Login to student dashboard
3. Allow location access when prompted
4. Mark attendance for enrolled subjects
5. View attendance statistics

### For Teachers
1. Register or login with admin-created account
2. Add subjects with year and semester
3. View student attendance reports
4. Manage subject information

### For Administrators
1. Login with admin credentials
2. Manage all users and roles
3. View system-wide statistics
4. Monitor attendance activities
5. Generate comprehensive reports

## File Structure

```
attendance_system/
├── admin/
│   └── dashboard.php
├── student/
│   └── dashboard.php
├── teacher/
│   └── dashboard.php
├── php/
│   ├── config.php
│   ├── email_helper.php
│   └── mark_attendance.php
├── css/
│   └── style.css
├── js/
│   ├── login.js
│   ├── signup.js
│   ├── student-dashboard.js
│   ├── teacher-dashboard.js
│   └── admin-dashboard.js
├── images/
├── uploads/
├── index.php
├── signup.php
├── verify_email.php
├── forgot_password.php
├── reset_password.php
├── logout.php
└── README.md
```

## Database Schema

### Users Table
- `id` (Primary Key)
- `username` (Unique)
- `email` (Unique)
- `password` (Hashed)
- `role` (student/teacher/admin)
- `full_name`
- `mobile`
- `is_verified`
- `created_at`

### Subjects Table
- `id` (Primary Key)
- `subject_name`
- `teacher_id` (Foreign Key)
- `year`
- `semester`
- `created_at`

### Attendance Table
- `id` (Primary Key)
- `student_id` (Foreign Key)
- `subject_id` (Foreign Key)
- `date`
- `time_in`
- `time_out`
- `latitude`
- `longitude`
- `status` (present/absent/late)
- `created_at`

### OTP Verification Table
- `id` (Primary Key)
- `email`
- `otp`
- `expires_at`
- `created_at`

### Password Reset Table
- `id` (Primary Key)
- `email`
- `token`
- `expires_at`
- `created_at`

## Security Features

- **Password Hashing**: Bcrypt password hashing
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Session-based tokens
- **Session Security**: Secure session handling
- **Email Verification**: OTP-based verification
- **Location Validation**: Server-side location verification

## Customization

### Styling
- Modify `css/style.css` for custom styling
- Update color schemes and layouts
- Add custom animations and effects

### Email Templates
- Customize email templates in `php/email_helper.php`
- Update branding and messaging
- Modify OTP and reset email content

### Campus Configuration
- Update campus coordinates in `php/config.php`
- Adjust campus radius as needed
- Configure multiple campus locations if required

## Troubleshooting

### Common Issues

1. **Email Not Sending**
   - Check SMTP credentials
   - Verify Gmail app password
   - Check server email configuration

2. **Location Not Working**
   - Ensure HTTPS is enabled (required for geolocation)
   - Check browser permissions
   - Verify campus coordinates

3. **Database Connection Issues**
   - Verify database credentials
   - Check MySQL service status
   - Ensure database exists

4. **Session Issues**
   - Check PHP session configuration
   - Verify file permissions
   - Clear browser cookies

## Support

For support and questions:
- Check the troubleshooting section
- Review error logs
- Ensure all prerequisites are met
- Verify configuration settings

## License

This project is open source and available under the MIT License.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Changelog

### Version 1.0.0
- Initial release
- Basic attendance management
- Geolocation support
- Email verification
- Role-based access control
- Responsive design
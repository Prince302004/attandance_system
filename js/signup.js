// Signup page JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    if (toggleConfirmPassword && confirmPasswordInput) {
        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Real-time password validation
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
            checkPasswordMatch();
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            checkPasswordMatch();
        });
    }
    
    // Username validation
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            validateUsername(this.value);
        });
    }
    
    // Email validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            validateEmail(this.value);
        });
    }
    
    // Form submission
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds if form doesn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    }
});

// Password validation function
function validatePassword(password) {
    const passwordField = document.getElementById('password');
    const feedback = getOrCreateFeedback(passwordField);
    
    let isValid = true;
    let messages = [];
    
    if (password.length < 6) {
        isValid = false;
        messages.push('At least 6 characters');
    }
    
    if (!/[A-Z]/.test(password)) {
        isValid = false;
        messages.push('One uppercase letter');
    }
    
    if (!/[a-z]/.test(password)) {
        isValid = false;
        messages.push('One lowercase letter');
    }
    
    if (!/[0-9]/.test(password)) {
        isValid = false;
        messages.push('One number');
    }
    
    if (isValid) {
        passwordField.classList.remove('is-invalid');
        passwordField.classList.add('is-valid');
        feedback.innerHTML = '<i class="fas fa-check text-success"></i> Password is strong';
        feedback.className = 'valid-feedback';
    } else {
        passwordField.classList.remove('is-valid');
        passwordField.classList.add('is-invalid');
        feedback.innerHTML = '<i class="fas fa-times text-danger"></i> Password must contain: ' + messages.join(', ');
        feedback.className = 'invalid-feedback';
    }
    
    return isValid;
}

// Password match validation
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmField = document.getElementById('confirm_password');
    const feedback = getOrCreateFeedback(confirmField);
    
    if (confirmPassword === '') {
        confirmField.classList.remove('is-valid', 'is-invalid');
        feedback.innerHTML = '';
        return false;
    }
    
    if (password === confirmPassword) {
        confirmField.classList.remove('is-invalid');
        confirmField.classList.add('is-valid');
        feedback.innerHTML = '<i class="fas fa-check text-success"></i> Passwords match';
        feedback.className = 'valid-feedback';
        return true;
    } else {
        confirmField.classList.remove('is-valid');
        confirmField.classList.add('is-invalid');
        feedback.innerHTML = '<i class="fas fa-times text-danger"></i> Passwords do not match';
        feedback.className = 'invalid-feedback';
        return false;
    }
}

// Username validation
function validateUsername(username) {
    const usernameField = document.getElementById('username');
    const feedback = getOrCreateFeedback(usernameField);
    
    if (username.length < 3) {
        usernameField.classList.remove('is-valid');
        usernameField.classList.add('is-invalid');
        feedback.innerHTML = '<i class="fas fa-times text-danger"></i> Username must be at least 3 characters';
        feedback.className = 'invalid-feedback';
        return false;
    }
    
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        usernameField.classList.remove('is-valid');
        usernameField.classList.add('is-invalid');
        feedback.innerHTML = '<i class="fas fa-times text-danger"></i> Username can only contain letters, numbers, and underscores';
        feedback.className = 'invalid-feedback';
        return false;
    }
    
    usernameField.classList.remove('is-invalid');
    usernameField.classList.add('is-valid');
    feedback.innerHTML = '<i class="fas fa-check text-success"></i> Username is available';
    feedback.className = 'valid-feedback';
    return true;
}

// Email validation
function validateEmail(email) {
    const emailField = document.getElementById('email');
    const feedback = getOrCreateFeedback(emailField);
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (!emailRegex.test(email)) {
        emailField.classList.remove('is-valid');
        emailField.classList.add('is-invalid');
        feedback.innerHTML = '<i class="fas fa-times text-danger"></i> Please enter a valid email address';
        feedback.className = 'invalid-feedback';
        return false;
    }
    
    emailField.classList.remove('is-invalid');
    emailField.classList.add('is-valid');
    feedback.innerHTML = '<i class="fas fa-check text-success"></i> Email format is valid';
    feedback.className = 'valid-feedback';
    return true;
}

// Get or create feedback element
function getOrCreateFeedback(field) {
    let feedback = field.parentNode.querySelector('.valid-feedback, .invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'feedback';
        field.parentNode.appendChild(feedback);
    }
    return feedback;
}

// Overall form validation
function validateForm() {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const fullName = document.getElementById('full_name').value.trim();
    const role = document.getElementById('role').value;
    const terms = document.getElementById('terms').checked;
    
    let isValid = true;
    
    if (!username || !email || !password || !confirmPassword || !fullName || !role) {
        showAlert('Please fill in all required fields', 'danger');
        isValid = false;
    }
    
    if (!validateUsername(username)) {
        isValid = false;
    }
    
    if (!validateEmail(email)) {
        isValid = false;
    }
    
    if (!validatePassword(password)) {
        isValid = false;
    }
    
    if (!checkPasswordMatch()) {
        isValid = false;
    }
    
    if (!terms) {
        showAlert('Please accept the terms and conditions', 'danger');
        isValid = false;
    }
    
    return isValid;
}

// Utility function to show alerts
function showAlert(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type} alert-dismissible fade show`;
    alertContainer.innerHTML = `
        <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const form = document.getElementById('signupForm');
    if (form) {
        form.parentNode.insertBefore(alertContainer, form);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }
}
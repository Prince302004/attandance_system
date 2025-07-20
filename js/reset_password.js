// Password reset page JavaScript functionality

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
    
    // Form submission
    const resetForm = document.querySelector('form');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Resetting Password...';
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
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    let isValid = true;
    
    if (!password || !confirmPassword) {
        showAlert('Please fill in all fields', 'danger');
        isValid = false;
    }
    
    if (!validatePassword(password)) {
        isValid = false;
    }
    
    if (!checkPasswordMatch()) {
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
    
    const form = document.querySelector('form');
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
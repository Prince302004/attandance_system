// Teacher Dashboard JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize edit subject functionality
    initEditSubject();
    
    // Initialize delete subject functionality
    initDeleteSubject();
    
    // Initialize form validation
    initFormValidation();
});

// Initialize edit subject functionality
function initEditSubject() {
    const editButtons = document.querySelectorAll('.edit-subject');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            const year = this.getAttribute('data-year');
            const semester = this.getAttribute('data-semester');
            
            // Populate edit modal
            document.getElementById('edit_subject_id').value = subjectId;
            document.getElementById('edit_subject_name').value = subjectName;
            document.getElementById('edit_year').value = year;
            document.getElementById('edit_semester').value = semester;
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
            editModal.show();
        });
    });
}

// Initialize delete subject functionality
function initDeleteSubject() {
    const deleteButtons = document.querySelectorAll('.delete-subject');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            
            // Populate delete modal
            document.getElementById('delete_subject_id').value = subjectId;
            document.getElementById('deleteSubjectName').textContent = subjectName;
            
            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteSubjectModal'));
            deleteModal.show();
        });
    });
}

// Initialize form validation
function initFormValidation() {
    // Add subject form validation
    const addSubjectForm = document.querySelector('#addSubjectModal form');
    if (addSubjectForm) {
        addSubjectForm.addEventListener('submit', function(e) {
            if (!validateSubjectForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Edit subject form validation
    const editSubjectForm = document.querySelector('#editSubjectModal form');
    if (editSubjectForm) {
        editSubjectForm.addEventListener('submit', function(e) {
            if (!validateSubjectForm(this)) {
                e.preventDefault();
                return false;
            }
        });
    }
}

// Validate subject form
function validateSubjectForm(form) {
    const subjectName = form.querySelector('input[name="subject_name"]').value.trim();
    const year = form.querySelector('select[name="year"]').value;
    const semester = form.querySelector('select[name="semester"]').value;
    
    let isValid = true;
    
    // Clear previous error messages
    clearFormErrors(form);
    
    // Validate subject name
    if (subjectName.length < 2) {
        showFieldError(form.querySelector('input[name="subject_name"]'), 'Subject name must be at least 2 characters long');
        isValid = false;
    }
    
    // Validate year
    if (!year) {
        showFieldError(form.querySelector('select[name="year"]'), 'Please select a year');
        isValid = false;
    }
    
    // Validate semester
    if (!semester) {
        showFieldError(form.querySelector('select[name="semester"]'), 'Please select a semester');
        isValid = false;
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    // Insert error message after the field
    field.parentNode.appendChild(errorDiv);
}

// Clear form errors
function clearFormErrors(form) {
    const invalidFields = form.querySelectorAll('.is-invalid');
    const errorMessages = form.querySelectorAll('.invalid-feedback');
    
    invalidFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    errorMessages.forEach(message => {
        message.remove();
    });
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});

// Add fade-in animation to dashboard cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Confirm delete action
function confirmDelete(message) {
    return confirm(message);
}

// Show loading state for form submissions
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                submitBtn.disabled = true;
                
                // Re-enable after 5 seconds if form doesn't submit
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });
});

// Auto-refresh page after successful form submission (if needed)
function refreshAfterSuccess() {
    setTimeout(() => {
        location.reload();
    }, 2000);
}

// Utility function to show notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Handle modal events
document.addEventListener('DOMContentLoaded', function() {
    // Clear form when add modal is closed
    const addModal = document.getElementById('addSubjectModal');
    if (addModal) {
        addModal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                clearFormErrors(form);
            }
        });
    }
    
    // Clear form when edit modal is closed
    const editModal = document.getElementById('editSubjectModal');
    if (editModal) {
        editModal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                clearFormErrors(form);
            }
        });
    }
});
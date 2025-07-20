// Student Dashboard JavaScript functionality

let currentLocation = null;
let isInsideCampus = false;

// Campus coordinates (should match PHP config)
const CAMPUS_LAT = 12.9716; // Example: Bangalore coordinates
const CAMPUS_LNG = 77.5946;
const CAMPUS_RADIUS = 500; // Radius in meters

document.addEventListener('DOMContentLoaded', function() {
    // Initialize geolocation
    initGeolocation();
    
    // Initialize attendance marking
    initAttendanceMarking();
});

// Initialize geolocation functionality
function initGeolocation() {
    const locationStatus = document.getElementById('locationStatus');
    
    if (!navigator.geolocation) {
        locationStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Geolocation not supported';
        locationStatus.className = 'location-status outside';
        return;
    }
    
    // Get current position
    navigator.geolocation.getCurrentPosition(
        function(position) {
            currentLocation = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            
            // Check if inside campus
            isInsideCampus = isLocationInsideCampus(currentLocation.latitude, currentLocation.longitude);
            
            // Update location status
            updateLocationStatus(isInsideCampus);
            
            // Update attendance buttons
            updateAttendanceButtons();
        },
        function(error) {
            console.error('Geolocation error:', error);
            locationStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Location access denied';
            locationStatus.className = 'location-status outside';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

// Check if location is inside campus
function isLocationInsideCampus(lat, lng) {
    const distance = calculateDistance(lat, lng, CAMPUS_LAT, CAMPUS_LNG);
    return distance <= CAMPUS_RADIUS;
}

// Calculate distance between two points using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI / 180;
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}

// Update location status display
function updateLocationStatus(isInside) {
    const locationStatus = document.getElementById('locationStatus');
    
    if (isInside) {
        locationStatus.innerHTML = '<i class="fas fa-check-circle me-2"></i>Inside Campus';
        locationStatus.className = 'location-status inside';
    } else {
        locationStatus.innerHTML = '<i class="fas fa-times-circle me-2"></i>Outside Campus';
        locationStatus.className = 'location-status outside';
    }
}

// Update attendance buttons based on location
function updateAttendanceButtons() {
    const attendanceButtons = document.querySelectorAll('.mark-attendance');
    
    attendanceButtons.forEach(button => {
        if (isInsideCampus) {
            button.disabled = false;
            button.classList.remove('btn-secondary');
            button.classList.add('btn-primary');
        } else {
            button.disabled = true;
            button.classList.remove('btn-primary');
            button.classList.add('btn-secondary');
            button.title = 'You must be on campus to mark attendance';
        }
    });
}

// Initialize attendance marking functionality
function initAttendanceMarking() {
    const attendanceButtons = document.querySelectorAll('.mark-attendance');
    
    attendanceButtons.forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            
            markAttendance(subjectId, subjectName);
        });
    });
}

// Mark attendance function
function markAttendance(subjectId, subjectName) {
    if (!isInsideCampus) {
        showAlert('You must be on campus to mark attendance', 'danger');
        return;
    }
    
    if (!currentLocation) {
        showAlert('Unable to get your location. Please try again.', 'danger');
        return;
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
    const messageDiv = document.getElementById('attendanceMessage');
    const spinner = document.getElementById('attendanceSpinner');
    
    messageDiv.innerHTML = `<p>Marking attendance for <strong>${subjectName}</strong>...</p>`;
    spinner.style.display = 'block';
    modal.show();
    
    // Prepare data
    const formData = new FormData();
    formData.append('subject_id', subjectId);
    formData.append('latitude', currentLocation.latitude);
    formData.append('longitude', currentLocation.longitude);
    formData.append('action', 'mark_attendance');
    
    // Send request
    fetch('../php/mark_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        spinner.style.display = 'none';
        
        if (data.success) {
            messageDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Attendance marked successfully!</strong><br>
                    Subject: ${subjectName}<br>
                    Time: ${data.time}<br>
                    Location: ${data.location_status}
                </div>
            `;
            
            // Update the button in the table
            updateAttendanceButton(subjectId, data.time);
            
            // Reload page after 2 seconds to update statistics
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            messageDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> ${data.message}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        spinner.style.display = 'none';
        messageDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Network Error:</strong> Please check your connection and try again.
            </div>
        `;
    });
}

// Update attendance button after successful marking
function updateAttendanceButton(subjectId, time) {
    const button = document.querySelector(`[data-subject-id="${subjectId}"]`);
    if (button) {
        const td = button.closest('td');
        td.innerHTML = `
            <span class="text-success">
                <i class="fas fa-check-circle me-1"></i>Marked at ${time}
            </span>
        `;
    }
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
    
    const container = document.querySelector('.container');
    container.insertBefore(alertContainer, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertContainer.parentNode) {
            alertContainer.remove();
        }
    }, 5000);
}

// Refresh location periodically
setInterval(() => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentLocation = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                };
                
                const wasInside = isInsideCampus;
                isInsideCampus = isLocationInsideCampus(currentLocation.latitude, currentLocation.longitude);
                
                // Only update if status changed
                if (wasInside !== isInsideCampus) {
                    updateLocationStatus(isInsideCampus);
                    updateAttendanceButtons();
                }
            },
            function(error) {
                console.error('Geolocation refresh error:', error);
            },
            {
                enableHighAccuracy: false,
                timeout: 5000,
                maximumAge: 60000 // 1 minute
            }
        );
    }
}, 30000); // Check every 30 seconds
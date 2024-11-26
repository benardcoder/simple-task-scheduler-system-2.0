// Form visibility toggles
function showLogin() {
    document.getElementById('loginForm').classList.remove('hidden');
    document.getElementById('signupForm').classList.add('hidden');
    document.getElementById('resetForm').classList.add('hidden');
}

function showSignup() {
    document.getElementById('signupForm').classList.remove('hidden');
    document.getElementById('loginForm').classList.add('hidden');
    document.getElementById('resetForm').classList.add('hidden');
}

function showResetPassword() {
    document.getElementById('resetForm').classList.remove('hidden');
    document.getElementById('loginForm').classList.add('hidden');
    document.getElementById('signupForm').classList.add('hidden');
}

// Message handling
function showMessage(message, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    
    // Insert message at the top of the container
    const container = document.querySelector('.container');
    container.insertBefore(messageDiv, container.firstChild);

    // Remove message after 5 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 5000);
}

// Form validation
function validateSignupForm(form) {
    const password = form.querySelector('input[name="password"]');
    if (password.value.length < 4) {
        showMessage('Password must be at least 4 characters long', 'error');
        return false;
    }
    return true;
}

// Add form submit handlers
document.addEventListener('DOMContentLoaded', function() {
    // Signup form validation
    const signupForm = document.querySelector('#signupForm form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            if (!validateSignupForm(this)) {
                e.preventDefault();
            }
        });
    }

    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.remove();
        }, 5000);
    });

    // Add loading indicator to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<div class="loading"></div> Processing...';
            btn.disabled = true;
        });
    });
});

// Task-related functions (for dashboard.php)
function confirmDelete(taskId) {
    if (confirm('Are you sure you want to delete this task?')) {
        window.location.href = `delete_task.php?id=${taskId}`;
    }
}

// Add these functions if you implement task status updates
function updateTaskStatus(taskId, status) {
    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('status', status);

    fetch('update_task_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Task status updated successfully', 'success');
            // Update UI
            document.querySelector(`#task-${taskId} .status`).textContent = status;
        } else {
            showMessage('Failed to update task status', 'error');
        }
    })
    .catch(error => {
        showMessage('An error occurred', 'error');
        console.error('Error:', error);
    });
}

// Dark mode toggle (optional)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDarkMode = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDarkMode);
}

// Check for saved dark mode preference
document.addEventListener('DOMContentLoaded', function() {
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'true') {
        document.body.classList.add('dark-mode');
    }
});
// Sidebar Toggle
document.getElementById('sidebar-toggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Search functionality
document.querySelector('.search-bar input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const title = task.querySelector('h3').textContent.toLowerCase();
        const description = task.querySelector('.task-description').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            task.style.display = '';
        } else {
            task.style.display = 'none';
        }
    });
});
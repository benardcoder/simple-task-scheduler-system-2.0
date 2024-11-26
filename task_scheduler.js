document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today for the due date input
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        const today = new Date();
        const minDateTime = today.toISOString().slice(0, 16);
        dueDateInput.min = minDateTime;
    }

    // Form validation and handling
    const taskForm = document.querySelector('.task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            if (!validateForm(e)) {
                e.preventDefault();
            }
        });
    }

    // Priority color indicators
    const prioritySelect = document.getElementById('priority');
    if (prioritySelect) {
        prioritySelect.addEventListener('change', updatePriorityColor);
        // Set initial color
        updatePriorityColor();
    }

    // Category custom input
    const categorySelect = document.getElementById('category');
    if (categorySelect) {
        categorySelect.addEventListener('change', handleCustomCategory);
    }

    // Auto-save draft
    const formInputs = document.querySelectorAll('.task-form input, .task-form textarea, .task-form select');
    formInputs.forEach(input => {
        input.addEventListener('change', saveDraft);
    });

    // Load draft if exists
    loadDraft();
});

// Form validation
function validateForm(e) {
    const title = document.getElementById('title').value.trim();
    const dueDate = new Date(document.getElementById('due_date').value);
    const now = new Date();
    let isValid = true;
    let errorMessages = [];

    // Title validation
    if (title.length < 3) {
        errorMessages.push("Title must be at least 3 characters long");
        isValid = false;
    }

    // Due date validation
    if (dueDate < now) {
        errorMessages.push("Due date cannot be in the past");
        isValid = false;
    }

    // Display error messages if any
    if (!isValid) {
        showErrorMessages(errorMessages);
    }

    return isValid;
}

// Show error messages
function showErrorMessages(messages) {
    // Remove existing error messages
    const existingErrors = document.querySelector('.error-messages');
    if (existingErrors) {
        existingErrors.remove();
    }

    // Create and show new error messages
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-messages message error';
    errorDiv.innerHTML = messages.map(msg => `<p>${msg}</p>`).join('');
    
    const form = document.querySelector('.task-form');
    form.insertBefore(errorDiv, form.firstChild);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

// Update priority color indicator
function updatePriorityColor() {
    const prioritySelect = document.getElementById('priority');
    const colors = {
        'Low': '#28a745',
        'Medium': '#ffc107',
        'High': '#dc3545'
    };
    
    prioritySelect.style.borderLeft = `5px solid ${colors[prioritySelect.value]}`;
}

// Handle custom category
function handleCustomCategory() {
    const categorySelect = document.getElementById('category');
    if (categorySelect.value === 'Other') {
        const customCategory = prompt('Enter custom category:');
        if (customCategory && customCategory.trim()) {
            // Add new option
            const option = new Option(customCategory, customCategory);
            categorySelect.add(option);
            categorySelect.value = customCategory;
        } else {
            categorySelect.value = 'Work'; // Default value
        }
    }
}

// Save form draft
function saveDraft() {
    const formData = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        dueDate: document.getElementById('due_date').value,
        priority: document.getElementById('priority').value,
        category: document.getElementById('category').value
    };
    
    localStorage.setItem('taskDraft', JSON.stringify(formData));
}

// Load form draft
function loadDraft() {
    const savedDraft = localStorage.getItem('taskDraft');
    if (savedDraft) {
        const formData = JSON.parse(savedDraft);
        
        document.getElementById('title').value = formData.title || '';
        document.getElementById('description').value = formData.description || '';
        document.getElementById('due_date').value = formData.dueDate || '';
        document.getElementById('priority').value = formData.priority || 'Medium';
        document.getElementById('category').value = formData.category || 'Work';
        
        updatePriorityColor();
    }
}

// Clear form and draft
function clearForm() {
    const form = document.querySelector('.task-form');
    form.reset();
    localStorage.removeItem('taskDraft');
    updatePriorityColor();
}

// Add this to your reset button
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to clear the form?')) {
        clearForm();
    }
});

// Character counter for description
const descriptionTextarea = document.getElementById('description');
if (descriptionTextarea) {
    const maxLength = 500; // Set your desired max length
    
    // Create counter element
    const counter = document.createElement('div');
    counter.className = 'character-counter';
    descriptionTextarea.parentNode.appendChild(counter);
    
    descriptionTextarea.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        counter.textContent = `${remaining} characters remaining`;
        counter.style.color = remaining < 50 ? '#dc3545' : '#666';
    });
}

// Add these styles to your CSS
const styles = `
    .character-counter {
        font-size: 12px;
        color: #666;
        text-align: right;
        margin-top: 5px;
    }

    .error-messages {
        margin-bottom: 20px;
    }

    .error-messages p {
        margin: 5px 0;
    }

    select#priority {
        border-left-width: 5px;
        border-left-style: solid;
    }
`;

// Add the styles to the document
const styleSheet = document.createElement('style');
styleSheet.textContent = styles;
document.head.appendChild(styleSheet);
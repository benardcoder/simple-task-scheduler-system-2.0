// Function to open a specific section on the dashboard
function openSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.add('hidden'); // Hide all sections
        section.classList.remove('active'); // Remove active class
    });

    const activeSection = document.getElementById(sectionId);
    if (activeSection) {
        activeSection.classList.remove('hidden'); // Show selected section
        activeSection.classList.add('active'); // Mark as active
    }
}

// Function to navigate to specific settings pages
function openSettingsPage(pageId) {
    openSection(pageId); // Reuses the openSection function for settings subsections
}

// Function to toggle notifications
function toggleNotifications() {
    const notificationStatus = document.getElementById('notificationsToggle');
    const status = notificationStatus.checked ? 'enabled' : 'disabled';
    alert ('Notifications ${status}!');
}

// Task Scheduler: Handle task addition
document.getElementById('taskForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent form submission

    const taskInput = document.getElementById('task');
    const dueDateInput = document.getElementById('dueDate');

    if (taskInput.value.trim() && dueDateInput.value) {
        const taskData = {
            task: taskInput.value.trim(),
            dueDate: dueDateInput.value
        };

        saveTaskToMyTasks(taskData); // Save the task to the "My Tasks" section
        taskInput.value = ''; // Reset input fields
        dueDateInput.value = '';
        alert('Task added successfully!');
    } else {
        alert('Please fill out both the task and due date fields.');
    }
});

// Save task to "My Tasks" section
function saveTaskToMyTasks(taskData) {
    const myTasksList = document.getElementById('taskCategoryList');
    const taskItem = document.createElement('li');
    taskItem.textContent = 'Task: ${taskData.task} | Due: ${new Date(taskData.dueDate).toLocaleString()}';
    myTasksList.appendChild(taskItem);
   
}



// Function to claim daily reward
function claimDailyReward() {
    alert('Daily reward claimed! Points added to your profile.');
}

// Filter tasks in the "My Tasks" section
function filterTasks(category) {
    const taskCategoryList = document.getElementById('taskCategoryList');
    // Simulate filtering logic (to be implemented as needed)
    alert('Filtering tasks by category: ${category}');
}

// Profile: Handle profile image upload
const profileImageInput = document.getElementById('profileImage');
if (profileImageInput) {
    profileImageInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            alert('Profile image uploaded successfully!');
        } else {
            alert('Failed to upload profile image.');
        }
    });
}

// Shop Section: Handle theme purchase
function purchaseTheme(themeName, price) {
    const currentPoints = parseInt(document.getElementById('pointsDisplay').textContent, 10);
    if (currentPoints >= price) {
        alert('You have successfully purchased the ${themeName} theme!');
        document.getElementById('pointsDisplay').textContent = currentPoints - price;
    } else {
        alert('You do not have enough points to purchase this theme.');
    }
}

// Initialize the dashboard (show the first section by default)
document.addEventListener('DOMContentLoaded', function () {
    openSection('taskScheduler'); // Show Task Scheduler as the default section
});
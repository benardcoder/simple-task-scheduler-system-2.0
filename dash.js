// Function to open the specific section
function openSection(sectionId) {
    const sections = document.querySelectorAll('.section');
    sections.forEach(section => {
        section.classList.remove('active');
    });

    const activeSection = document.getElementById(sectionId);
    activeSection.classList.add('active');
}

// Task Scheduler - Add task functionality
const taskForm = document.getElementById('taskForm');
const taskList = document.getElementById('taskList');
const allTasks = []; // Array to store all tasks

taskForm.addEventListener('submit', function(event) {
    event.preventDefault();

    const taskInput = document.getElementById('task');
    const dueDateInput = document.getElementById('dueDate');

    const taskData = {
        id: Date.now(), // Unique ID for each task
        task: taskInput.value,
        dueDate: dueDateInput.value,
        status: 'In Progress' // Default status
    };

    allTasks.push(taskData); // Store task in the array
    renderTasks('In Progress'); // Render tasks based on the default category

    taskInput.value = '';
    dueDateInput.value = '';
});

// Function to render tasks based on category
function renderTasks(status) {
    const taskCategoryList = document.getElementById('taskCategoryList');
    taskCategoryList.innerHTML = ''; // Clear previous list

    const filteredTasks = allTasks.filter(task => task.status === status);

    if (filteredTasks.length === 0) {
        'taskCategoryList.textContent = No tasks found for ${status}.';
        return;
    }

    filteredTasks.forEach(task => {
        const taskItem = document.createElement('li');
        'taskItem.textContent = Task: ${task.task} | Due: ${new Date(task.dueDate).toLocaleString()}';

        // Mark as Completed button
        if (status === 'In Progress') {
            const completeButton = document.createElement('button');
            completeButton.textContent = 'Mark as Completed';
            completeButton.classList.add('complete-btn');
            completeButton.addEventListener('click', () => {
                task.status = 'Completed';
                renderTasks('In Progress');
            });
            taskItem.appendChild(completeButton);
        }

        // Delete button
        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete';
        deleteButton.classList.add('delete-btn');
        deleteButton.addEventListener('click', () => {
            task.status = 'Deleted';
            renderTasks(status);
        });
        taskItem.appendChild(deleteButton);

        taskCategoryList.appendChild(taskItem);
    });
}

// Event listeners for filtering tasks by category
function filterTasks(category) {
    alert('Filtering tasks by category: ${category}');
    renderTasks(category);
}

// Theme selection
const themeButtons = document.querySelectorAll('.theme-button');
themeButtons.forEach(button => {
    button.addEventListener('click', function() {
        const theme = button.getAttribute('data-theme');
        changeTheme(theme);
    });
});

function changeTheme(theme) {
    document.body.classList.remove('default', 'dark', 'light', 'blue', 'green');
    document.body.classList.add(theme);
}

// Daily Reward claim functionality
function claimDailyReward() {
    alert("You have claimed your daily reward!");
}

// Save settings functionality
function saveSettings() {
    const theme = document.querySelector('input[name="theme"]:checked').value;
    const notificationsEnabled = document.getElementById('notificationsToggle').checked;

    localStorage.setItem('theme', theme);
    localStorage.setItem('notificationsEnabled', notificationsEnabled);

    alert('Settings saved!');
}

// Load saved settings on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'default';
    const notificationsEnabled = localStorage.getItem('notificationsEnabled') === 'true';

    document.getElementById('notificationsToggle').checked = notificationsEnabled;
    changeTheme(savedTheme);
});

// Profile update functionality
function updateProfile() {
    const username = document.getElementById('usernameDisplay').textContent;
    const points = document.getElementById('pointsDisplay').textContent;

    alert('Profile Updated: \nUsername: ${username}\nPoints: ${points}');
}

// Buy theme functionality in the shop
function buyTheme(theme) {
    const points = parseInt(document.getElementById('pointsDisplay').textContent, 10);
    if (points >= 100) {
        document.getElementById('pointsDisplay').textContent = points - 100;
        alert('You have bought the ${theme} theme!');
        changeTheme(theme);
    } else {
        alert('You do not have enough points to buy this theme.');
    }
}
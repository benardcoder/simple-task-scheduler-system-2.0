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

taskForm.addEventListener('submit', function(event) {
    event.preventDefault();

    const taskInput = document.getElementById('task');
    const dueDateInput = document.getElementById('dueDate');

    const taskData = {
        task: taskInput.value,
        dueDate: dueDateInput.value
    };

    const taskItem = document.createElement('li');
    taskItem.textContent = 'Task: ${taskData.task} | Due: ${new Date(taskData.dueDate).toLocaleString()}';

    const deleteButton = document.createElement('button');
    deleteButton.textContent = 'Delete';
    deleteButton.classList.add('delete-btn');
    deleteButton.addEventListener('click', () => taskItem.remove());

    taskItem.appendChild(deleteButton);
    taskList.appendChild(taskItem);

    taskInput.value = '';
    dueDateInput.value = '';
});

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
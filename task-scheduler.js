document.addEventListener("DOMContentLoaded", () => {
    const taskForm = document.getElementById("task-form");

    // Function to handle form submission
    taskForm.addEventListener("submit", (e) => {
        e.preventDefault();

        // Get task inputs
        const title = document.getElementById("task-title").value.trim();
        const date = document.getElementById("task-date").value;
        const time = document.getElementById("task-time").value;

        // Validate inputs
        if (!title || !date || !time) {
            alert("Please fill in all fields.");
            return;
        }

        // Create task object
        const task = { title, date, time };

        // Save task to localStorage
        const tasks = JSON.parse(localStorage.getItem("tasks")) || [];
        tasks.push(task);
        localStorage.setItem("tasks", JSON.stringify(tasks));

        // Notify the user and reset the form
        alert("Task added successfully! Check the 'My Tasks' section.");
        taskForm.reset();
    });
});
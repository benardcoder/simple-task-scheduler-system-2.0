document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const modal = document.getElementById('taskModal');
    const addTaskBtn = document.getElementById('addTaskBtn');
    const closeBtn = document.querySelector('.close');
    const taskForm = document.getElementById('taskForm');
    
    // Filter elements
    const filterBtns = document.querySelectorAll('.filter-btn');
    const searchInput = document.getElementById('taskSearch');
    
    // Modal controls
    addTaskBtn.onclick = function() {
        openModal();
    }
    
    closeBtn.onclick = function() {
        closeModal();
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Form submission
    taskForm.onsubmit = function(e) {
        e.preventDefault();
        const formData = new FormData(taskForm);
        const taskId = document.getElementById('taskId').value;
        
        fetch('tasks_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error saving task');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the task');
        });
    }
    
    // Filters
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterTasks();
        });
    });
    
    searchInput.addEventListener('input', filterTasks);
});

function openModal(taskId = null) {
    const modal = document.getElementById('taskModal');
    const modalTitle = document.getElementById('modalTitle');
    const taskForm = document.getElementById('taskForm');
    
    modalTitle.textContent = taskId ? 'Edit Task' : 'New Task';
    taskForm.reset();
    document.getElementById('taskId').value = taskId || '';
    
    if (taskId) {
        // Fetch task data and populate form
        fetch(`tasks_process.php?action=get_task&task_id=${taskId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const task = data.task;
                document.getElementById('title').value = task.title;
                document.getElementById('description').value = task.description;
                document.getElementById('category').value = task.category;
                document.getElementById('priority').value = task.priority;
                document.getElementById('due_date').value = task.due_date.slice(0, 16);
            }
        });
    }
    
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('taskModal');
    modal.style.display = 'none';
}

function editTask(taskId) {
    openModal(taskId);
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    fetch('tasks_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete&task_id=${taskId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting task');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the task');
    });
}

function updateTaskStatus(taskId, status) {
    fetch('tasks_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_status&task_id=${taskId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error updating task status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating task status');
    });
}

function filterTasks() {
    const activeFilter = document.querySelector('.filter-btn.active').dataset.filter;
    const searchTerm = document.getElementById('taskSearch').value.toLowerCase();
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const status = task.dataset.status;
        const title = task.querySelector('h3').textContent.toLowerCase();
        const description = task.querySelector('p').textContent.toLowerCase();
        
        const matchesFilter = activeFilter === 'all' || status === activeFilter;
        const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
        
        task.style.display = matchesFilter && matchesSearch ? 'block' : 'none';
    });
}
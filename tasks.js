document.addEventListener('DOMContentLoaded', function() {
    // Task status update function
    window.updateTaskStatus = async function(taskId, newStatus) {
        try {
            console.log('Updating task:', taskId, 'to status:', newStatus);

            const response = await fetch('api/task_operations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update_status',
                    task_id: taskId,
                    status: newStatus
                })
            });

            const data = await response.json();
            console.log('Server response:', data);

            if (!response.ok) {
                throw new Error(data.error || 'Failed to update task status');
            }

            if (data.success) {
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                const targetColumn = document.querySelector(`[data-status="${newStatus}"]`);
                
                if (taskCard && targetColumn) {
                    const taskList = targetColumn.querySelector('.task-list');
                    if (taskList) {
                        taskList.appendChild(taskCard);
                        
                        // Update the status select to match the new status
                        const statusSelect = taskCard.querySelector('.status-select');
                        if (statusSelect) {
                            statusSelect.value = newStatus;
                        }

                        // Update task counter
                        updateTaskCounters();
                        
                        showMessage('Task status updated successfully!', 'success');
                    }
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error updating task status: ' + error.message, 'error');
        }
    }

    // Delete task function
    window.deleteTask = async function(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) {
            return;
        }

        try {
            const response = await fetch(`api/task_operations.php?id=${taskId}`, {
                method: 'DELETE'
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Failed to delete task');
            }

            if (data.success) {
                const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                if (taskCard) {
                    // Animate removal
                    taskCard.style.opacity = '0';
                    taskCard.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        taskCard.remove();
                        updateTaskCounters();
                        showMessage('Task deleted successfully!', 'success');
                    }, 300);
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error deleting task: ' + error.message, 'error');
        }
    }

    // Update task counters
    function updateTaskCounters() {
        const statuses = ['pending', 'in_progress', 'completed'];
        statuses.forEach(status => {
            const column = document.querySelector(`[data-status="${status}"]`);
            if (column) {
                const count = column.querySelectorAll('.task-card').length;
                const counter = column.querySelector('.task-count');
                if (counter) {
                    counter.textContent = count;
                }
            }
        });
    }

    // Helper function to show messages
    function showMessage(message, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.textContent = message;
        
        let container = document.getElementById('messageContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'messageContainer';
            document.querySelector('.main-content').appendChild(container);
        }
        
        // Add animation class
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateY(-20px)';
        container.appendChild(messageDiv);
        
        // Trigger animation
        setTimeout(() => {
            messageDiv.style.opacity = '1';
            messageDiv.style.transform = 'translateY(0)';
        }, 10);
        
        // Remove message after delay
        setTimeout(() => {
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }

    // Initialize task counters
    updateTaskCounters();

    // Add event listeners for status selects
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const taskId = this.closest('.task-card').dataset.taskId;
            updateTaskStatus(taskId, this.value);
        });
    });

    // Add event listeners for delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.closest('.task-card').dataset.taskId;
            deleteTask(taskId);
        });
    });

    // Optional: Add drag and drop functionality if dragula is available
    if (typeof dragula !== 'undefined') {
        dragula([
            ...document.querySelectorAll('.task-list')
        ]).on('drop', function(el, target, source) {
            const taskId = el.dataset.taskId;
            const newStatus = target.closest('.task-column').dataset.status;
            updateTaskStatus(taskId, newStatus);
        });
    }
});
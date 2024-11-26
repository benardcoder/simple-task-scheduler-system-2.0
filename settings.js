document.addEventListener('DOMContentLoaded', function() {
    // Theme buttons
    const themeButtons = document.querySelectorAll('.theme-btn');
    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const theme = button.dataset.theme;
            updateSetting('theme', theme);
            themeButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
    });

    // Toggle switches
    const toggles = document.querySelectorAll('.switch input[type="checkbox"]');
    toggles.forEach(toggle => {
        toggle.addEventListener('change', () => {
            const setting = toggle.id;
            const value = toggle.checked;
            updateSetting(setting, value);
        });
    });

    // Password Modal
    const passwordModal = document.getElementById('passwordModal');
    const changePasswordBtn = document.getElementById('changePasswordBtn');
    const changePasswordForm = document.getElementById('changePasswordForm');

    changePasswordBtn.onclick = () => passwordModal.style.display = 'block';

    changePasswordForm.onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(changePasswordForm);
        
        if (formData.get('newPassword') !== formData.get('confirmPassword')) {
            alert('New passwords do not match!');
            return;
        }

        fetch('settings_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password changed successfully!');
                passwordModal.style.display = 'none';
                changePasswordForm.reset();
            } else {
                alert(data.message || 'Error changing password');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while changing password');
        });
    };

    // Delete Account Modal
    const deleteModal = document.getElementById('deleteModal');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAccountForm = document.getElementById('deleteAccountForm');

    deleteAccountBtn.onclick = () => deleteModal.style.display = 'block';

    deleteAccountForm.onsubmit = (e) => {
        e.preventDefault();
        if (!confirm('Are you absolutely sure you want to delete your account? This cannot be undone!')) {
            return;
        }

        const formData = new FormData(deleteAccountForm);
        formData.append('action', 'delete_account');

        fetch('settings_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'logout.php';
            } else {
                alert(data.message || 'Error deleting account');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting account');
        });
    };

    // Close modals
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.onclick = function() {
            this.closest('.modal').style.display = 'none';
        }
    });

    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
});

function updateSetting(setting, value) {
    fetch('settings_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_setting&setting=${setting}&value=${value}`
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Error updating setting');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating settings');
    });
}
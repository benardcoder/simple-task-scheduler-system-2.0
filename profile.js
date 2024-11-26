document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editProfileModal');
    const btn = document.getElementById('editProfileBtn');
    const span = document.getElementsByClassName('close')[0];
    const form = document.getElementById('editProfileForm');

    // Open modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // Close modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Handle form submission
    form.onsubmit = function(e) {
        e.preventDefault();
        
        fetch('profile_process.php', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error updating profile');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating your profile');
        });
    }
});
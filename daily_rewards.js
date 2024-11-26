document.addEventListener('DOMContentLoaded', function() {
    // Start countdown if reward is not available
    if (document.getElementById('countdown')) {
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
});

function updateCountdown() {
    const now = new Date().getTime();
    const timeLeft = nextClaimTime - now;

    if (timeLeft <= 0) {
        location.reload();
        return;
    }

    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

    document.getElementById('countdown').textContent = 
        `${hours}h ${minutes}m ${seconds}s`;
}

function claimReward() {
    fetch('daily_rewards_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=claim_reward'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Congratulations! You've earned ${data.points} points!`);
            location.reload();
        } else {
            alert(data.message || 'Error claiming reward');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while claiming your reward');
    });
}
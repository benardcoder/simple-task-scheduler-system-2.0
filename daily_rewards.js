// Function to format numbers with commas
function formatNumber(number) {
    return new Intl.NumberFormat().format(number);
}

// Function to show a custom styled alert
function showCustomAlert(message, type = 'info') {
    // Remove any existing alerts
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `custom-alert ${type}`;
    
    let icon = '🕒'; // Default icon
    if (type === 'success') icon = '🎉';
    if (type === 'error') icon = '⚠️';
    
    alertDiv.innerHTML = `
        <div class="alert-content">
            <span class="alert-icon">${icon}</span>
            <span class="alert-message">${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Remove the alert after 3 seconds
    setTimeout(() => {
        alertDiv.classList.add('fade-out');
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

// Function to update reward card states
function updateRewardCards(currentStreak, canClaim) {
    const rewardCards = document.querySelectorAll('.reward-card');
    
    rewardCards.forEach((card, index) => {
        const day = index + 1;
        const statusDiv = card.querySelector('.reward-status');
        
        // Remove all state classes
        card.classList.remove('claimed', 'available', 'locked');
        
        if (day < currentStreak) {
            // Previous days
            card.classList.add('claimed');
            statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> Claimed';
        } else if (day === currentStreak && canClaim) {
            // Current day and can claim
            card.classList.add('available');
            statusDiv.innerHTML = `
                <button class="claim-btn" data-day="${day}">
                    Claim Reward
                </button>
            `;
        } else {
            // Future days or can't claim yet
            card.classList.add('locked');
            statusDiv.innerHTML = '<i class="fas fa-lock"></i> Locked';
        }
    });

    // Reattach event listeners to new buttons
    attachClaimButtonListeners();
}

// Function to handle reward claiming
function claimReward() {
    console.log('Starting reward claim process...');
    
    const claimBtn = document.querySelector('.claim-btn');
    if (claimBtn) {
        claimBtn.disabled = true;
        claimBtn.textContent = 'Claiming...';
    }

    fetch('test_reward.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Update points display
            const pointsDisplay = document.querySelector('.user-points span');
            if (pointsDisplay) {
                pointsDisplay.textContent = `${formatNumber(data.newPoints)} Points`;
            }

            // Update streak display
            const streakDisplay = document.querySelector('.streak-count span');
            if (streakDisplay) {
                streakDisplay.textContent = `Day ${data.newStreak}`;
            }

            // Show success message
            showCustomAlert(`Reward Claimed Successfully!\nPoints Earned: ${formatNumber(data.pointsAdded)}\nNew Total: ${formatNumber(data.newPoints)}\nCurrent Streak: Day ${data.newStreak}`, 'success');

            // Update reward cards
            updateRewardCards(data.newStreak, false);

            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showCustomAlert(data.message, 'info');
            
            if (data.message.includes('wait')) {
                const countdownElement = document.getElementById('countdown');
                if (countdownElement) {
                    countdownElement.textContent = data.message.split('wait ')[1];
                }
            }
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        showCustomAlert('Error claiming reward: ' + error.message, 'error');
    })
    .finally(() => {
        if (claimBtn) {
            claimBtn.disabled = false;
            claimBtn.textContent = 'Claim Reward';
        }
    });
}

// Function to attach claim button listeners
function attachClaimButtonListeners() {
    const claimButtons = document.querySelectorAll('.claim-btn');
    claimButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Claim button clicked');
            claimReward();
        });
    });
}

// Countdown timer function
function updateCountdown() {
    const countdownElement = document.getElementById('countdown');
    if (!countdownElement) return;

    const now = new Date().getTime();
    const timeLeft = nextClaimTime - now;

    if (timeLeft <= 0) {
        countdownElement.textContent = 'Available!';
        countdownElement.classList.add('available');
        // Update reward cards when countdown reaches zero
        const streakCount = document.querySelector('.streak-count span');
        const currentStreak = parseInt(streakCount.textContent.split(' ')[1]) || 1;
        updateRewardCards(currentStreak, true);
        return;
    }

    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

    const formatTime = (num) => num.toString().padStart(2, '0');
    countdownElement.textContent = 
        `${formatTime(hours)}h ${formatTime(minutes)}m ${formatTime(seconds)}s`;
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Daily rewards script loaded');
    
    // Initial setup
    const streakCount = document.querySelector('.streak-count span');
    const currentStreak = parseInt(streakCount.textContent.split(' ')[1]) || 1;
    const canClaim = document.querySelector('.reward-status.available') !== null;
    
    updateRewardCards(currentStreak, canClaim);
    
    // Initialize countdown
    if (document.getElementById('countdown')) {
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
});
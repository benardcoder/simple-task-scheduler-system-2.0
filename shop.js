document.addEventListener('DOMContentLoaded', function() {
    // Category filtering
    const categoryButtons = document.querySelectorAll('.category-btn');
    const shopItems = document.querySelectorAll('.shop-item');

    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Update active button
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Filter items
            const category = button.dataset.category;
            shopItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});

// Purchase function
function purchaseItem(itemId, price) {
    if (!confirm('Are you sure you want to purchase this item?')) {
        return;
    }

    fetch('shop_process.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update user's points display
            document.querySelector('.user-points span').textContent = 
                `${Number(data.newPoints).toLocaleString()} Points`;
            
            // Show success message
            alert(data.message);
            
            // Refresh the page or update UI
            location.reload();
        } else {
            alert(data.message || 'Error purchasing item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your purchase');
    });
}
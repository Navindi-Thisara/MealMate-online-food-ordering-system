let pendingCartId = null;

// Show alert messages
function showAlert(message, isError = false) {
    const alertBox = document.createElement('div');
    alertBox.textContent = message;
    alertBox.classList.add('custom-alert');
    if (isError) alertBox.classList.add('error');
    document.body.appendChild(alertBox);

    setTimeout(() => {
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 2000);
}

// Show confirmation modal for item removal
function showConfirmationModal(cartId, itemName) {
    const modal = document.getElementById('confirmationModal');
    const message = document.getElementById('confirmationMessage');
    message.textContent = itemName
        ? `Are you sure you want to remove "${itemName}" from your cart?`
        : 'Are you sure you want to remove this item from your cart?';
    pendingCartId = cartId;
    modal.classList.add('active');
}

// Hide confirmation modal
function hideConfirmationModal() {
    document.getElementById('confirmationModal').classList.remove('active');
    pendingCartId = null;
}

// Update the sliding cart drawer
function updateSlidingCart() {
    fetch('../cart/cart_items_api.php')
        .then(res => res.json())
        .then(data => {
            const cartContainer = document.getElementById('cart-items-container');
            const cartTotal = document.getElementById('cart-total-price');
            cartContainer.innerHTML = '';

            if (!data.success || data.items.length === 0) {
                cartContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty.</p>';
                cartTotal.textContent = 'Rs.0.00';
                return;
            }

            data.items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.innerHTML = `
                    <div class="item-image">
                        <img src="../assets/images/menu/${item.image}" 
                             alt="${item.food_name}" 
                             onerror="this.src='../assets/images/menu/default.jpg';">
                    </div>
                    <div class="item-details">
                        <h3>${item.food_name}</h3>
                        <p class="item-price">Rs.${parseFloat(item.price).toFixed(2)} each</p>
                        <div class="quantity-controls">
                            <button class="qty-btn" onclick="updateCartQuantity(${item.cart_id}, -1)">-</button>
                            <input class="qty-input" type="text" value="${item.quantity}" readonly>
                            <button class="qty-btn" onclick="updateCartQuantity(${item.cart_id}, 1)">+</button>
                            <button class="delete-btn" onclick="showConfirmationModal(${item.cart_id}, '${item.food_name.replace(/'/g, "\\'")}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                cartContainer.appendChild(div);
            });
            cartTotal.textContent = `Rs.${parseFloat(data.total).toFixed(2)}`;
        })
        .catch(err => {
            console.error('Cart fetch error:', err);
            showAlert('Failed to load cart', true);
        });
}

// Update quantity
function updateCartQuantity(cartId, change) {
    fetch('../cart/update_cart_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cart_id=${cartId}&change=${change}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) updateSlidingCart();
        else showAlert(`Error: ${data.message}`, true);
    })
    .catch(err => {
        console.error('Quantity update error:', err);
        showAlert('Network error', true);
    });
}

// Remove cart item
function removeCartItem(cartId) {
    fetch('../cart/remove_from_cart_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cart_id=${cartId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateSlidingCart();
            showAlert('Item removed successfully!');
        } else showAlert(`Error: ${data.message}`, true);
    })
    .catch(err => {
        console.error('Remove item error:', err);
        showAlert('Network error', true);
    });
}

// Add to cart
function addToCart(foodId) {
    fetch('../cart/add_to_cart_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `food_id=${foodId}&quantity=1`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAlert('Added to cart!');
            updateSlidingCart();
        } else {
            showAlert(data.message.includes('login') ? 'Please log in first.' : data.message, true);
            if (data.message.includes('login')) setTimeout(() => window.location.href='../users/login.php', 1500);
        }
    })
    .catch(err => {
        console.error('Add to cart error:', err);
        showAlert('Failed to connect to server', true);
    });
}

// Toggle cart drawer
function toggleCart() {
    const cart = document.getElementById('sliding-cart');
    const overlay = document.querySelector('.sliding-cart-overlay');
    cart.classList.toggle('active');
    overlay.style.display = cart.classList.contains('active') ? 'block' : 'none';
    if (cart.classList.contains('active')) updateSlidingCart();
}

// DOM initialization
document.addEventListener('DOMContentLoaded', () => {
    updateSlidingCart();

    // Confirmation modal buttons
    document.getElementById('confirmRemove').addEventListener('click', () => {
        if (pendingCartId) {
            removeCartItem(pendingCartId);
            hideConfirmationModal();
        }
    });
    document.getElementById('cancelRemove').addEventListener('click', hideConfirmationModal);

    // Close modal when clicking outside
    document.getElementById('confirmationModal').addEventListener('click', e => {
        if (e.target.id === 'confirmationModal') hideConfirmationModal();
    });
});

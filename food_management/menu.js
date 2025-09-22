// Global variable to store the cart ID for removal
let pendingCartId = null;

function showAlert(message, isError = false) {
    const alertBox = document.createElement('div');
    alertBox.textContent = message;
    alertBox.classList.add('custom-alert');
    if (isError) {
        alertBox.classList.add('error');
    }
    document.body.appendChild(alertBox);

    setTimeout(() => {
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 2000);
}

// Show beautiful confirmation modal
function showConfirmationModal(cartId, itemName) {
    const modal = document.getElementById('confirmationModal');
    const message = document.getElementById('confirmationMessage');
    
    // Update the message to include the item name if available
    if (itemName) {
        message.textContent = `Are you sure you want to remove "${itemName}" from your cart?`;
    } else {
        message.textContent = 'Are you sure you want to remove this item from your cart?';
    }
    
    // Store the cart ID for the confirmation action
    pendingCartId = cartId;
    
    // Show the modal with animation
    modal.classList.add('active');
}

// Hide confirmation modal
function hideConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('active');
    pendingCartId = null;
}

function updateSlidingCart() {
    fetch('../cart/cart_items_api.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load cart data');
            }

            const cartContainer = document.getElementById('cart-items-container');
            const cartTotal = document.getElementById('cart-total-price');
            cartContainer.innerHTML = '';

            if (data.items.length === 0) {
                cartContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty.</p>';
                cartTotal.textContent = 'Rs.0.00';
            } else {
                data.items.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'cart-item';
                    itemDiv.setAttribute('data-cart-id', item.cart_id);
                    itemDiv.innerHTML = `
                        <div class="cart-item-image">
                            <img src="../assets/images/menu/${item.image}" alt="${item.food_name}" 
                                 onerror="this.src='../assets/images/menu/default.jpg';">
                        </div>
                        <div class="cart-item-details">
                            <h4>${item.food_name}</h4>
                            <p class="price">Rs.${parseFloat(item.price).toFixed(2)} each</p>
                            <div class="cart-item-controls">
                                <button class="cart-qty-btn" onclick="updateCartQuantity(${item.cart_id}, -1)">-</button>
                                <span class="cart-item-quantity">${item.quantity}</span>
                                <button class="cart-qty-btn" onclick="updateCartQuantity(${item.cart_id}, 1)">+</button>
                                <button class="cart-remove-btn" onclick="showConfirmationModal(${item.cart_id}, '${item.food_name.replace(/'/g, "\\'")}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    cartContainer.appendChild(itemDiv);
                });
                cartTotal.textContent = `Rs.${parseFloat(data.total).toFixed(2)}`;
            }
        })
        .catch(error => {
            console.error('Error fetching cart data:', error);
            showAlert('Error: Failed to load cart.', true);
        });
}

function updateCartQuantity(cartId, change) {
    fetch('../cart/update_cart_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartId}&change=${change}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSlidingCart();
            if (data.action === 'removed') {
                showAlert('Item removed from cart.');
            }
        } else {
            showAlert(`Error: ${data.message}`, true);
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        showAlert('Network error occurred. Please try again.', true);
    });
}

function removeCartItem(cartId) {
    fetch('../cart/remove_from_cart_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cart_id=${cartId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateSlidingCart();
            showAlert('Item removed from cart successfully!');
        } else {
            showAlert(`Error: ${data.message}`, true);
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        showAlert('Network error occurred. Please try again.', true);
    });
}

function addToCart(foodId) {
    fetch('../cart/add_to_cart_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `food_id=${foodId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Added to cart successfully!');
            updateSlidingCart();
        } else {
            if (data.message.includes('login') || data.message.includes('logged')) {
                showAlert('Please log in to add items to cart.', true);
                setTimeout(() => {
                    window.location.href = '../users/login.php';
                }, 2000);
            } else {
                showAlert('Error: ' + data.message, true);
            }
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showAlert('Error: Failed to connect to server.', true);
    });
}

function toggleCart() {
    const cart = document.getElementById('sliding-cart');
    const overlay = document.querySelector('.sliding-cart-overlay');
    const cartIcon = document.getElementById('main-cart-icon');
    
    cart.classList.toggle('open');
    overlay.style.display = cart.classList.contains('open') ? 'block' : 'none';
    
    // Show/hide cart icon
    if (cart.classList.contains('open')) {
        cartIcon.classList.add('hidden');
        updateSlidingCart();
    } else {
        cartIcon.classList.remove('hidden');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    updateSlidingCart();
    
    // Add event listeners for the confirmation modal
    document.getElementById('confirmRemove').addEventListener('click', () => {
        if (pendingCartId) {
            removeCartItem(pendingCartId);
            hideConfirmationModal();
        }
    });
    
    document.getElementById('cancelRemove').addEventListener('click', hideConfirmationModal);
    
    // Close modal when clicking outside the content
    document.getElementById('confirmationModal').addEventListener('click', (e) => {
        if (e.target.id === 'confirmationModal') {
            hideConfirmationModal();
        }
    });
});
// Global variable to store the cart ID for removal
let pendingCartId = null;

// Enhanced alert with icon
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
    }, 3000);
}

// Show beautiful confirmation modal
function showConfirmationModal(cartId, itemName) {
    const modal = document.getElementById('confirmationModal');
    const message = document.getElementById('confirmationMessage');
    
    if (itemName) {
        message.textContent = `Are you sure you want to remove "${itemName}" from your cart?`;
    } else {
        message.textContent = 'Are you sure you want to remove this item from your cart?';
    }
    
    pendingCartId = cartId;
    modal.classList.add('active');
}

// Hide confirmation modal
function hideConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('active');
    pendingCartId = null;
}

// Update cart badge count
function updateCartBadge(count) {
    let badge = document.querySelector('.cart-badge');
    const cartIcon = document.getElementById('main-cart-icon');
    
    if (count > 0) {
        if (!badge) {
            // Check if cart-badge-container already exists
            let container = document.querySelector('.cart-badge-container');
            if (!container && cartIcon) {
                container = document.createElement('div');
                container.className = 'cart-badge-container';
                cartIcon.parentNode.insertBefore(container, cartIcon);
                container.appendChild(cartIcon);
            }
            
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            if (container) {
                container.appendChild(badge);
            }
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

// Update sliding cart with enhanced visuals
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

            // Update cart badge
            updateCartBadge(data.items.length);

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
                                <button class="cart-qty-btn" onclick="updateCartQuantity(${item.cart_id}, -1)" aria-label="Decrease quantity">-</button>
                                <span class="cart-item-quantity">${item.quantity}</span>
                                <button class="cart-qty-btn" onclick="updateCartQuantity(${item.cart_id}, 1)" aria-label="Increase quantity">+</button>
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
            showAlert('Failed to load cart.', true);
        });
}

// Update cart quantity
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

// Remove item from cart
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

// Add to cart with visual feedback
function addToCart(foodId, buttonElement) {
    // Visual feedback on button
    if (buttonElement) {
        buttonElement.disabled = true;
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        
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
                
                buttonElement.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = originalText;
                }, 1500);
            } else {
                if (data.message.includes('login') || data.message.includes('logged')) {
                    showAlert('Please log in to add items to cart.', true);
                    setTimeout(() => {
                        window.location.href = '../users/login.php';
                    }, 2000);
                } else {
                    showAlert('Error: ' + data.message, true);
                }
                
                buttonElement.disabled = false;
                buttonElement.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            showAlert('Failed to connect to server.', true);
            
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalText;
        });
    }
}

// Toggle cart
function toggleCart() {
    const cart = document.getElementById('sliding-cart');
    const overlay = document.querySelector('.sliding-cart-overlay');
    const cartIcon = document.getElementById('main-cart-icon');
    
    cart.classList.toggle('open');
    overlay.style.display = cart.classList.contains('open') ? 'block' : 'none';
    
    if (cart.classList.contains('open')) {
        cartIcon.classList.add('hidden');
        updateSlidingCart();
        document.body.style.overflow = 'hidden';
    } else {
        cartIcon.classList.remove('hidden');
        document.body.style.overflow = '';
    }
}

// Search and filter functionality
function initializeSearch() {
    const searchInput = document.getElementById('menuSearch');
    const categoryButtons = document.querySelectorAll('.category-btn');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterMenu);
    }
    
    if (categoryButtons.length > 0) {
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                // Filter menu
                filterMenu();
            });
        });
    }
}

// Filter menu items
function filterMenu() {
    const searchInput = document.getElementById('menuSearch');
    const activeButton = document.querySelector('.category-btn.active');
    const noResultsDiv = document.getElementById('noResultsMessage');
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const selectedCategory = activeButton ? activeButton.getAttribute('data-category') : 'all';
    
    let visibleCount = 0;
    const categorySections = document.querySelectorAll('.category-section');
    
    categorySections.forEach(section => {
        const sectionCategory = section.getAttribute('data-category');
        const menuItems = section.querySelectorAll('.menu-item');
        let visibleInCategory = 0;
        
        menuItems.forEach(item => {
            const itemName = item.querySelector('h3').textContent.toLowerCase();
            const itemDesc = item.querySelector('p').textContent.toLowerCase();
            
            const matchesSearch = searchTerm === '' || itemName.includes(searchTerm) || itemDesc.includes(searchTerm);
            const matchesCategory = selectedCategory === 'all' || sectionCategory === selectedCategory;
            
            if (matchesSearch && matchesCategory) {
                item.style.display = 'flex';
                visibleInCategory++;
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Hide category if no items visible
        section.style.display = visibleInCategory > 0 ? 'block' : 'none';
    });
    
    // Show/hide no results message
    if (noResultsDiv) {
        if (visibleCount === 0) {
            noResultsDiv.classList.add('show');
        } else {
            noResultsDiv.classList.remove('show');
        }
    }
}

// Back to top button
function initializeBackToTop() {
    let backToTopBtn = document.querySelector('.back-to-top');
    
    if (!backToTopBtn) {
        backToTopBtn = document.createElement('button');
        backToTopBtn.className = 'back-to-top';
        backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        backToTopBtn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(backToTopBtn);
    }
    
    // Show/hide on scroll
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });
    
    // Scroll to top on click
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Update cart on page load
    updateSlidingCart();
    
    // Initialize search and filter
    initializeSearch();
    
    // Initialize back to top button
    initializeBackToTop();
    
    // Add event listeners for confirmation modal
    const confirmBtn = document.getElementById('confirmRemove');
    const cancelBtn = document.getElementById('cancelRemove');
    const modal = document.getElementById('confirmationModal');
    const closeBtn = document.querySelector('.close-confirm-btn');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (pendingCartId) {
                removeCartItem(pendingCartId);
                hideConfirmationModal();
            }
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', hideConfirmationModal);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', hideConfirmationModal);
    }
    
    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target.id === 'confirmationModal') {
                hideConfirmationModal();
            }
        });
    }
    
    // Close cart with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const cart = document.getElementById('sliding-cart');
            if (cart && cart.classList.contains('open')) {
                toggleCart();
            }
            if (modal && modal.classList.contains('active')) {
                hideConfirmationModal();
            }
        }
    });
    
    // Update all "Add to Cart" buttons to pass button element
    document.querySelectorAll('.add-to-cart').forEach(button => {
        const originalOnclick = button.getAttribute('onclick');
        if (originalOnclick) {
            const foodId = originalOnclick.match(/\d+/)[0];
            button.removeAttribute('onclick');
            button.addEventListener('click', function() {
                addToCart(foodId, this);
            });
        }
    });
});
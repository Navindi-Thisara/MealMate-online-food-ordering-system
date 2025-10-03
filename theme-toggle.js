// Theme Toggle System for MealMate
// This file handles dark/light mode switching with localStorage persistence

(function() {
    'use strict';

    // Helper: read a cookie by name
    const readCookie = (name) => {
        const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.*+?^${}()|[\]\\])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    };

    // Get the current theme from localStorage or cookie or default to 'dark'
    const getCurrentTheme = () => {
        return localStorage.getItem('mealmate-theme') || readCookie('mealmate-theme') || 'dark';
    };

    // Set theme on the document
    const setTheme = (theme) => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('mealmate-theme', theme);
        // persist in cookie for server-side rendering (1 year)
        try {
            document.cookie = 'mealmate-theme=' + encodeURIComponent(theme) + ';path=/;max-age=' + (60*60*24*365) + ';SameSite=Lax';
        } catch (e) { /* no-op */ }

        // update meta theme-color for mobile UI
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.setAttribute('content', theme === 'light' ? '#fafafa' : '#0d0d0d');
        }

        // Update button icon
        updateThemeIcon(theme);

        // Dispatch custom event for other scripts
        window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    };

    // Update the theme toggle button icon
    const updateThemeIcon = (theme) => {
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');

        if (sunIcon && moonIcon) {
            if (theme === 'light') {
                sunIcon.style.opacity = '1';
                sunIcon.style.transform = 'rotate(0deg) scale(1)';
                moonIcon.style.opacity = '0';
                moonIcon.style.transform = 'rotate(90deg) scale(0)';
            } else {
                sunIcon.style.opacity = '0';
                sunIcon.style.transform = 'rotate(-90deg) scale(0)';
                moonIcon.style.opacity = '1';
                moonIcon.style.transform = 'rotate(0deg) scale(1)';
            }
        }
    };

    // Toggle between themes
    const toggleTheme = () => {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);

        // Add animation effect
        animateThemeChange();
    };

    // Add visual feedback when theme changes
    const animateThemeChange = () => {
        const btn = document.querySelector('.theme-toggle-btn');
        if (btn) {
            btn.style.transform = 'scale(1.2) rotate(360deg)';
            setTimeout(() => {
                btn.style.transform = '';
            }, 300);
        }
    };

    // Initialize theme on page load
    const initTheme = () => {
        const theme = getCurrentTheme();
        setTheme(theme);
        console.log('Theme initialized:', theme);
    };

    // Create and inject the theme toggle button
    const createThemeToggleButton = () => {
        // Check if button already exists
        if (document.querySelector('.theme-toggle-container')) {
            return;
        }

        const container = document.createElement('div');
        container.className = 'theme-toggle-container';
        container.innerHTML = `
            <button class="theme-toggle-btn" aria-label="Toggle theme" title="Switch theme">
                <i class="fas fa-sun theme-icon sun-icon"></i>
                <i class="fas fa-moon theme-icon moon-icon"></i>
            </button>
        `;

        document.body.appendChild(container);

        // Add click event listener
        const btn = container.querySelector('.theme-toggle-btn');
        btn.addEventListener('click', toggleTheme);

        // Add keyboard support
        btn.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleTheme();
            }
        });
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            createThemeToggleButton();
        });
    } else {
        initTheme();
        createThemeToggleButton();
    }

    // Re-apply theme if page is shown from cache (e.g., browser back button)
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            initTheme();
        }
    });

    // Expose theme functions to global scope for external use
    window.MealMateTheme = {
        toggle: toggleTheme,
        setTheme: setTheme,
        getTheme: getCurrentTheme
    };

})();

// Add smooth scroll behavior for theme transitions
document.addEventListener('themechange', (e) => {
    console.log('Theme changed to:', e.detail.theme);

    // You can add custom logic here when theme changes
    // For example, update charts, images, etc.
});

// Detect system theme preference changes
if (window.matchMedia) {
    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');

    darkModeQuery.addEventListener('change', (e) => {
        // Only auto-switch if user hasn't manually set a preference
        const hasManualPreference = localStorage.getItem('mealmate-theme') || (document.cookie && document.cookie.indexOf('mealmate-theme=') !== -1);
        if (!hasManualPreference) {
            const newTheme = e.matches ? 'dark' : 'light';
            window.MealMateTheme.setTheme(newTheme);
        }
    });
}
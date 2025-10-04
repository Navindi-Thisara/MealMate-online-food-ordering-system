// Theme Toggle System for MealMate
// This file handles dark/light mode switching with localStorage persistence

(function() {
    'use strict';

    console.log('MealMate theme-toggle.js loaded');

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
        try {
            document.cookie = 'mealmate-theme=' + encodeURIComponent(theme) + ';path=/;max-age=' + (60*60*24*365) + ';SameSite=Lax';
        } catch (e) { /* no-op */ }

        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', theme === 'light' ? '#fafafa' : '#0d0d0d');

        updateThemeIcon(theme);

        const btn = document.querySelector('.theme-toggle-btn');
        if (btn) {
            try {
                btn.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
                btn.setAttribute('aria-label', theme === 'light' ? 'Switch to dark theme' : 'Switch to light theme');
            } catch (e) { /* no-op */ }
        }

        window.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
    };

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

    // guard to avoid double-triggering rapid toggles
    let toggleLock = false;
    const toggleTheme = () => {
        if (toggleLock) {
            console.debug('toggleTheme: locked, ignoring duplicate event');
            return;
        }
        toggleLock = true;
        setTimeout(() => { toggleLock = false; }, 350); // small debounce window

        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);

        animateThemeChange();
    };

    const animateThemeChange = () => {
        const btn = document.querySelector('.theme-toggle-btn');
        if (btn) {
            btn.style.transform = 'scale(1.2) rotate(360deg)';
            setTimeout(() => { btn.style.transform = ''; }, 300);
        }
    };

    const initTheme = () => {
        const theme = getCurrentTheme();
        setTheme(theme);
        console.log('Theme initialized:', theme);
    };

    const createThemeToggleButton = () => {
        const attachListeners = (btn) => {
            if (!btn) return;
            if (btn.dataset.themeListenerAttached) return;

            if (!btn.hasAttribute('type')) {
                try { btn.setAttribute('type', 'button'); } catch (e) { /* ignore */ }
            }

            // Keep per-button listeners minimal (keyboard support) — click handled by delegation below
            btn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleTheme();
                }
            });

            try {
                const pressed = getCurrentTheme() === 'light' ? 'true' : 'false';
                btn.setAttribute('aria-pressed', pressed);
                btn.setAttribute('aria-label', getCurrentTheme() === 'light' ? 'Switch to dark theme' : 'Switch to light theme');
            } catch (e) { /* no-op */ }

            btn.dataset.themeListenerAttached = '1';
        };

        let container = document.querySelector('.theme-toggle-container');
        if (container) {
            let btn = container.querySelector('.theme-toggle-btn') || document.querySelector('.theme-toggle-btn');
            attachListeners(btn);
            return;
        }

        container = document.createElement('div');
        container.className = 'theme-toggle-container';
        container.innerHTML = `
            <button class="theme-toggle-btn" aria-label="Toggle theme" title="Switch theme" type="button">
                <i class="fas fa-sun theme-icon sun-icon"></i>
                <i class="fas fa-moon theme-icon moon-icon"></i>
            </button>
        `;
        document.body.appendChild(container);

        const btn = container.querySelector('.theme-toggle-btn');
        attachListeners(btn);
    };

    // Delegated click/keydown handlers so clicks always register even if button added later
    const delegateHandlers = () => {
        document.addEventListener('click', (e) => {
            const target = e.target.closest && e.target.closest('.theme-toggle-btn');
            if (target) {
                e.preventDefault();
                toggleTheme();
            }
        });

        document.addEventListener('keydown', (e) => {
            const target = e.target.closest && e.target.closest('.theme-toggle-btn');
            if (target && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                toggleTheme();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initTheme();
            createThemeToggleButton();
            delegateHandlers();
        });
    } else {
        initTheme();
        createThemeToggleButton();
        delegateHandlers();
    }

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) initTheme();
    });

    window.MealMateTheme = {
        toggle: toggleTheme,
        setTheme: setTheme,
        getTheme: getCurrentTheme
    };

})(); // end IIFE

document.addEventListener('themechange', (e) => {
    console.log('Theme changed to:', e.detail.theme);
});

if (window.matchMedia) {
    const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    darkModeQuery.addEventListener('change', (e) => {
        const hasManualPreference = localStorage.getItem('mealmate-theme') || (document.cookie && document.cookie.indexOf('mealmate-theme=') !== -1);
        if (!hasManualPreference) {
            const newTheme = e.matches ? 'dark' : 'light';
            if (window.MealMateTheme && typeof window.MealMateTheme.setTheme === 'function') {
                window.MealMateTheme.setTheme(newTheme);
            } else {
                try { document.documentElement.setAttribute('data-theme', newTheme); } catch (err) {}
            }
        }
    });
}
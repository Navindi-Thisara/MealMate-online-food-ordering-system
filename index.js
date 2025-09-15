// Sidebar toggle  
const sideNav = document.getElementById('sideNav');
const menuBtn = document.getElementById('menuBtn');

menuBtn.addEventListener('click', () => {
    sideNav.style.width = '250px';
});

// Close sidebar when clicking outside of it
document.addEventListener('click', (event) => {
    // Check if the click is outside the sidebar and not on the menu button
    if (sideNav.style.width === '250px' && !sideNav.contains(event.target) && !menuBtn.contains(event.target)) {
        sideNav.style.width = '0';
    }
});

// Services Slideshow
const featuresContainer = document.querySelector('.features');
const featureBoxes = document.querySelectorAll('.features .feature-box');
const dots = document.querySelectorAll('.carousel-dot');
const totalSlides = dots.length; // Use the number of dots for the true slide count
let currentIndex = 0;

function updateSlideshow() {
    // Determine the transition duration based on whether we're resetting the loop
    const transitionDuration = featuresContainer.classList.contains('no-transition') ? '0s' : '0.6s';
    featuresContainer.style.transition = `transform ${transitionDuration} ease-in-out`;

    const boxWidth = featureBoxes[0].offsetWidth + 40;
    const offset = -currentIndex * boxWidth;
    featuresContainer.style.transform = `translateX(${offset}px)`;

    // If we've reached a duplicated slide, instantly reset the position back to the start
    if (currentIndex >= totalSlides) {
        setTimeout(() => {
            featuresContainer.style.transition = 'none';
            featuresContainer.style.transform = `translateX(0px)`;
            currentIndex = 0;
            updateDots();
        }, 600); // Match the CSS transition duration
    } else {
        updateDots();
    }
}

function updateDots() {
    dots.forEach((dot, i) => {
        dot.classList.remove('active');
        if (i === currentIndex) {
            dot.classList.add('active');
        }
    });
}

// Click events for dots
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        currentIndex = index;
        updateSlideshow();
    });
});

// Auto-rotate slideshow
setInterval(() => {
    currentIndex++;
    updateSlideshow();
}, 3000);

// Initial call to set up the first slide
updateSlideshow();
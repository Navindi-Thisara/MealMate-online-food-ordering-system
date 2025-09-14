// Sidebar toggle
const sideNav = document.getElementById('sideNav');
const menuBtn = document.getElementById('menuBtn');
const closeBtn = document.getElementById('closeBtn');

menuBtn.addEventListener('click', () => {
  sideNav.style.width = '250px';
});

closeBtn.addEventListener('click', () => {
  sideNav.style.width = '0';
});

// 3D Carousel
const boxes = document.querySelectorAll('.feature-box');
let currentIndex = 0;

function showBox(index) {
    boxes.forEach((box, i) => {
        box.classList.remove('active');
        if(i === index) box.classList.add('active');
    });
}

// Show first card
showBox(currentIndex);

// Auto rotate carousel
setInterval(() => {
    currentIndex++;
    if(currentIndex >= boxes.length) currentIndex = 0;
    showBox(currentIndex);
}, 3000);

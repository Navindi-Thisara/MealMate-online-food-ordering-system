// Single card carousel: show one card at a time
const boxes = document.querySelectorAll('.feature-box');
let currentIndex = 0;

function showBox(index) {
    boxes.forEach((box, i) => {
        if(i === index) {
            box.classList.add('active');
        } else {
            box.classList.remove('active');
        }
    });
}

// Show first card initially
showBox(currentIndex);

// Auto rotate every 3 seconds
setInterval(() => {
    currentIndex++;
    if(currentIndex >= boxes.length) currentIndex = 0;
    showBox(currentIndex);
}, 3000);
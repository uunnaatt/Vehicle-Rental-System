// ===== CAROUSEL FUNCTIONALITY =====
let currentCar = 0;
const cars = ['car1.png', 'car2.png', 'car3.png']; // Add your car images

const leftArrow = document.querySelector('.carousel-arrow.left');
const rightArrow = document.querySelector('.carousel-arrow.right');
const carImage = document.querySelector('.car-image');

if (leftArrow && rightArrow) {
    leftArrow.addEventListener('click', () => {
        currentCar = (currentCar - 1 + cars.length) % cars.length;
        carImage.src = `../assets/images/${cars[currentCar]}`;
    });

    rightArrow.addEventListener('click', () => {
        currentCar = (currentCar + 1) % cars.length;
        carImage.src = `../assets/images/${cars[currentCar]}`;
    });
}

// ===== DATE PICKER VALIDATION =====
const pickupDate = document.getElementById('pickup-date');
const returnDate = document.getElementById('return-date');

if (pickupDate && returnDate) {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    pickupDate.min = today;
    returnDate.min = today;

    // Update return date min when pickup date changes
    pickupDate.addEventListener('change', () => {
        returnDate.min = pickupDate.value;
    });
}

// ===== SEARCH BUTTON =====
const searchBtn = document.querySelector('.btn-search');
if (searchBtn) {
    searchBtn.addEventListener('click', () => {
        const pickup = pickupDate?.value;
        const returnD = returnDate?.value;
        
        if (pickup && returnD) {
            alert(`Searching cars from ${pickup} to ${returnD}`);
            // Later: Redirect to search results page
        } else {
            alert('Please select both dates');
        }
    });
}
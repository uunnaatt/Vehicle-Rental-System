document.addEventListener('DOMContentLoaded', () => {
    // ===== CAROUSEL FUNCTIONALITY =====
    let currentCar = 0;
    const cars = ['car1.png', 'car2.png', 'car3.png', 'mazda-blue.png', 'tesla-black.png', 'lamborghini.png'];

    const leftArrow = document.querySelector('.carousel-arrow.left');
    const rightArrow = document.querySelector('.carousel-arrow.right');
    const carImage = document.querySelector('.car-image');

    if (leftArrow && rightArrow && carImage) {
        leftArrow.addEventListener('click', () => {
            currentCar = (currentCar - 1 + cars.length) % cars.length;
            carImage.style.opacity = 0;
            setTimeout(() => {
                carImage.src = `../assets/images/${cars[currentCar]}`;
                carImage.style.opacity = 1;
            }, 200);
        });

        rightArrow.addEventListener('click', () => {
            currentCar = (currentCar + 1) % cars.length;
            carImage.style.opacity = 0;
            setTimeout(() => {
                carImage.src = `../assets/images/${cars[currentCar]}`;
                carImage.style.opacity = 1;
            }, 200);
        });
    }

    // Book Now button redirects to browse
    const bookNowBtn = document.querySelector('.btn-book');
    if (bookNowBtn) {
        bookNowBtn.addEventListener('click', () => {
            window.location.href = 'browse.php';
        });
    }
});

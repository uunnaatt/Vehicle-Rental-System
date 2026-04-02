// Get car name from URL
const urlParams = new URLSearchParams(window.location.search);
const carSlug = urlParams.get('car') || 'tesla-model-s';

// Car data with more details
const carsData = {
    'tesla-model-s': {
        name: 'TESLA MODEL S',
        image: '../assets/images/tesla-black.png',
        price: 'Rs. 1000'
    },
    'ferrari-laferrari': {
        name: 'FERRARI LAFERRARI',
        image: '../assets/images/ferrari-red.png',
        price: 'Rs. 2500'
    },
    'lamborghini': {
        name: 'LAMBORGHINI',
        image: '../assets/images/lamborghini.png',
        price: 'Rs. 3000'
    },
    'lamborghini-aventador': {
        name: 'LAMBORGHINI AVENTADOR',
        image: '../assets/images/lamborghini.png',
        price: 'Rs. 3000'
    },
    'bmw-gts3-m2': {
        name: 'BMW GTS3 M2',
        image: '../assets/images/bmw-white.png',
        price: 'Rs. 2000'
    },
    'ferrari-ff': {
        name: 'FERRARI-FF',
        image: '../assets/images/ferrari-ff.png',
        price: 'Rs. 2500'
    },
    'kala-gadi': {
        name: 'KALO GADI',
        image: '../assets/images/car1.png',
        price: 'Rs. 1000'
    },
    'nilo-gadi': {
        name: 'NILO GADI',
        image: '../assets/images/mazda-blue.png',
        price: 'Rs. 1000'
    },
    'hariyo-gadi': {
        name: 'HARIYO GAADI',
        image: '../assets/images/car3.png',
        price: 'Rs. 1000'
    }
};

// Update page with car data
const carData = carsData[carSlug] || carsData['tesla-model-s'];
document.getElementById('car-name').textContent = carData.name;
document.getElementById('car-image').src = carData.image;

// Also update the book now button
const bookNowBtn = document.querySelector('.btn-book-now');
if (bookNowBtn) {
    bookNowBtn.onclick = () => {
        window.location.href = `booking.php?car=${carSlug}`;
    };
}

// Load more cars (static for demo)
const moreCars = document.getElementById('more-cars');
if (moreCars) {
    const moreCarsList = [
        { name: 'Tesla Model S', image: '../assets/images/tesla-black.png', slug: 'tesla-model-s' },
        { name: 'Ferrari-FF', image: '../assets/images/ferrari-ff.png', slug: 'ferrari-ff' },
        { name: 'Lamborghini', image: '../assets/images/lamborghini.png', slug: 'lamborghini' },
        { name: 'BMW GTS3 M2', image: '../assets/images/bmw-white.png', slug: 'bmw-gts3-m2' }
    ];
    
    moreCars.innerHTML = moreCarsList.map(car => `
        <div class="car-card-mini" onclick="window.location.href='car-details.php?car=${car.slug}'" style="cursor: pointer;">
            <img src="${car.image}" alt="${car.name}">
            <button class="heart-btn" onclick="event.stopPropagation(); toggleHeart(this)">♡</button>
        </div>
    `).join('');
}
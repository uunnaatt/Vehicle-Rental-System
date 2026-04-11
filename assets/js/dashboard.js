// assets/js/dashboard.js
// Immediate auth check securely
fetch('../api/auth/me.php').then(res => {
    if (!res.ok) window.location.href = 'login.php';
}).catch(() => window.location.href = 'login.php');
// Dashboard – loads vehicles from the live API

let allVehicles = [];
let isLoggedIn = false; // Cached auth state

// Check auth status on load (non-blocking)
fetch('../api/auth/me.php').then(r => r.ok ? r.json() : null).then(d => {
    isLoggedIn = d?.authenticated === true;
}).catch(() => {});

function bookVehicle(e, vehicleId) {
    e.stopPropagation();
    if (!isLoggedIn) {
        window.location.href = 'login.php';
        return;
    }
    window.location.href = `booking.php?vehicle_id=${vehicleId}`;
}

async function loadAllVehicles() {
    try {
        const response = await fetch('../api/vehicles/read.php');
        if (!response.ok) throw new Error('No vehicles');
        const data = await response.json();
        allVehicles = data.records || [];

        // Render sections (slice for variety)
        renderSection('nearby-cars', allVehicles.slice(0, 6));
        renderSection('popular-cars', [...allVehicles].sort(() => Math.random() - 0.5).slice(0, 6));
        renderSection('recommend-cars', allVehicles.slice(4, 10));
        renderSection('best-cars', allVehicles.slice(0, 8));
    } catch (e) {
        console.error('Failed to load vehicles:', e);
    }
}

function createCarCard(car) {
    return `
        <div class="car-card" onclick="window.location.href='car-details.php?vehicle_id=${car.id}'" style="cursor:pointer;">
            <div class="car-card-image">
                <img src="${car.image_url}" alt="${car.name}" onerror="this.src='../assets/images/car1.png'">
                <button class="heart-btn" onclick="event.stopPropagation(); toggleHeart(this)">♡</button>
            </div>
            <div class="car-card-details">
                <h3 class="car-name">${car.name}</h3>
                <div class="car-meta-row">
                    <span class="car-category">${car.category_name || ''}</span>
                    <div class="car-rating">
                        <span class="rating-value">4.8</span>
                        <span class="star">★</span>
                    </div>
                </div>
                <div class="car-location">
                    <span class="location-icon">📍</span>
                    <span>${car.location_name}</span>
                </div>
                <div class="car-footer">
                    <span class="car-price">Rs. ${Number(car.daily_rate).toLocaleString()}<small>/Day</small></span>
                    <button class="book-now-btn" onclick="event.stopPropagation(); bookVehicle(event, ${car.id})">Book now</button>
                </div>
            </div>
        </div>
    `;
}

function renderSection(containerId, cars) {
    const el = document.getElementById(containerId);
    if (!el) return;
    if (!cars || cars.length === 0) {
        el.innerHTML = '<p style="padding:20px; opacity:0.6;">No vehicles available.</p>';
        return;
    }
    el.innerHTML = cars.map(createCarCard).join('');
}

function scrollCars(section, direction) {
    const carousel = document.getElementById(`${section}-cars`);
    carousel.scrollBy({ left: direction * 320, behavior: 'smooth' });
}

function toggleHeart(btn) {
    if (btn.textContent === '♡') {
        btn.textContent = '♥';
        btn.style.color = '#ef4444';
    } else {
        btn.textContent = '♡';
        btn.style.color = '#000';
    }
}

// Search
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = allVehicles.filter(c =>
            c.name.toLowerCase().includes(term) || c.brand.toLowerCase().includes(term)
        );
        renderSection('nearby-cars', filtered.slice(0, 6));
        renderSection('popular-cars', filtered.slice(0, 6));
        renderSection('recommend-cars', filtered.slice(0, 6));
        renderSection('best-cars', filtered.slice(0, 8));
    });
}

loadAllVehicles();
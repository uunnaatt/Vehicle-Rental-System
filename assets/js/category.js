// assets/js/category.js
// Immediate auth check securely
fetch('../api/auth/me.php').then(res => {
    if (!res.ok) window.location.href = 'login.php';
}).catch(() => window.location.href = 'login.php');
// Get category from URL parameter
const urlParams = new URLSearchParams(window.location.search);
const categoryParam = urlParams.get('type') || '';

// Update category title
if (categoryParam) {
    document.getElementById('category-name').textContent = "Vehicles in " + categoryParam;
} else {
    document.getElementById('category-name').textContent = "All Vehicles";
}

let allVehicles = [];

// Load cars from API
async function loadCars() {
    const carGrid = document.getElementById('car-grid');
    carGrid.innerHTML = '<p style="grid-column: 1/-1; text-align:center;">Loading vehicles...</p>';
    
    try {
        let url = '../api/vehicles/read.php';
        if (categoryParam) {
            url += '?category=' + encodeURIComponent(categoryParam);
        }
        
        const response = await fetch(url);
        if(!response.ok) {
            if(response.status === 404) {
                carGrid.innerHTML = '<p style="grid-column: 1/-1; text-align:center;">No vehicles found.</p>';
                return;
            }
            throw new Error("API Error");
        }
        
        const data = await response.json();
        allVehicles = data.records;
        renderCars(allVehicles);
        
    } catch(e) {
        carGrid.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:red;">Failed to load vehicles from server.</p>';
        console.error(e);
    }
}

function renderCars(cars) {
    const carGrid = document.getElementById('car-grid');
    if(cars.length === 0) {
        carGrid.innerHTML = '<p style="grid-column: 1/-1; text-align:center;">No cars match your search.</p>';
        return;
    }
    
    carGrid.innerHTML = cars.map(car => `
        <div class="car-card">
            <div class="car-card-image" style="background:#f1f5f9; border-radius:15px; padding:20px; text-align:center; position:relative;">
                <img src="${car.image_url}" alt="${car.name}" style="max-width:100%; height:150px; object-fit:contain; transition: transform 0.3s ease;">
                <button class="heart-btn" style="position:absolute; top:15px; right:15px; background:white; border:none; width:35px; height:35px; border-radius:50%; font-size:18px; color:#ef4444; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.1);">♡</button>
            </div>
            <div class="car-card-details" style="padding: 20px 0;">
                <h3 class="car-name" style="font-size:18px; font-weight:700; color:#1e293b; margin-bottom:10px;">${car.name} <span style="font-size:14px; font-weight:400; color:#64748b;">(${car.model_year})</span></h3>
                <div class="car-meta" style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:14px; color:#64748b;">
                    <div class="car-location">
                        <span class="location-icon">📍</span> ${car.location_name}
                    </div>
                    <div>
                        ${car.transmission}
                    </div>
                </div>
                <div class="car-footer" style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:15px;">
                    <span class="car-price" style="font-size:18px; font-weight:700; color:#2563eb;">Rs. ${car.daily_rate}<span style="font-size:13px; color:#64748b; font-weight:400;">/Day</span></span>
                    <button class="book-now-btn" style="background:#0f172a; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; transition: background 0.3s;" onclick="location.href='booking.php?vehicle_id=${car.id}'">Book now</button>
                </div>
            </div>
        </div>
    `).join('');
}

// Load cars on page load
loadCars();

// Search functionality
const searchInput = document.querySelector('.search-input');
const searchBtn = document.querySelector('.search-btn');

if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const filtered = allVehicles.filter(car => 
            car.name.toLowerCase().includes(searchTerm) || 
            car.brand.toLowerCase().includes(searchTerm)
        );
        renderCars(filtered);
    });
}
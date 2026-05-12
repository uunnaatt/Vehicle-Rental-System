// assets/js/category.js
// Immediate auth check securely
fetch('../api/auth/me.php').then(res => {
    if (!res.ok) window.location.href = 'login.php';
}).catch(() => window.location.href = 'login.php');
// Get category from URL parameter
const urlParams = new URLSearchParams(window.location.search);
const categoryParam = urlParams.get('type') || '';

// Update category title
const categoryTitleEl = document.getElementById('category-name');
if (categoryParam) {
    if (categoryTitleEl) categoryTitleEl.textContent = "Vehicles in " + categoryParam;
} else {
    if (categoryTitleEl) categoryTitleEl.textContent = "All Vehicles";
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
        <div class="car-card" onclick="window.location.href='car-details.php?vehicle_id=${car.id}'" style="cursor:pointer;">
            <div class="car-card-image" style="background:#f1f5f9; border-radius:15px; padding:20px; text-align:center; position:relative;">
                <img src="${car.image_url}" alt="${car.name}" style="width:100%; height:170px; object-fit:cover; border-radius:10px; transition: transform 0.3s ease;">
                <button class="heart-btn" data-vehicle-id="${car.id}" onclick="event.stopPropagation(); toggleFavoriteVehicle(${car.id}, this)" aria-label="Save vehicle"><i class="fa-regular fa-heart"></i></button>
            </div>
            <div class="car-card-details" style="padding: 20px;">
                <h3 class="car-name" style="font-size:18px; font-weight:700; color:#1e293b; margin-bottom:10px;">${car.name} <span style="font-size:14px; font-weight:400; color:#64748b;">(${car.model_year})</span></h3>
                <div class="car-meta" style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:14px; color:#64748b;">
                    <div class="car-location">
                        <span class="location-icon"><i class="fa-solid fa-location-dot"></i></span> ${car.location_name}
                    </div>
                    <div>
                        ${car.transmission}
                    </div>
                </div>
                <div class="car-footer" style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:15px;">
                    <span class="car-price" style="font-size:18px; font-weight:700; color:#2563eb;">Rs. ${car.daily_rate}<span style="font-size:13px; color:#64748b; font-weight:400;">/Day</span></span>
                    <button class="book-now-btn" style="background:#0f172a; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; transition: background 0.3s;" onclick="event.stopPropagation(); location.href='booking.php?vehicle_id=${car.id}'">Book now</button>
                </div>
            </div>
        </div>
    `).join('');
    hydrateFavoriteButtons(carGrid);
}

function setFavoriteVisual(btn, favorited) {
    btn.classList.toggle('is-favorite', favorited);
    btn.innerHTML = favorited ? '<i class="fa-solid fa-heart"></i>' : '<i class="fa-regular fa-heart"></i>';
}

async function toggleFavoriteVehicle(vehicleId, btn) {
    try {
        const res = await fetch('../api/favorites/toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ vehicle_id: vehicleId })
        });
        if (res.status === 401) {
            window.location.href = 'login.php';
            return;
        }
        const data = await res.json();
        if (res.ok) setFavoriteVisual(btn, data.favorited);
    } catch (e) {
        console.error(e);
    }
}

async function hydrateFavoriteButtons(root = document) {
    const buttons = [...root.querySelectorAll('.heart-btn[data-vehicle-id]')];
    await Promise.all(buttons.map(async (btn) => {
        try {
            const res = await fetch(`../api/favorites/status.php?vehicle_id=${btn.dataset.vehicleId}`);
            const data = await res.json();
            setFavoriteVisual(btn, data.favorited === true);
        } catch {}
    }));
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
            (car.name || '').toLowerCase().includes(searchTerm) || 
            (car.brand || '').toLowerCase().includes(searchTerm)
        );
        renderCars(filtered);
    });
}

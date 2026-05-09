let currentVehicleId = null;
let isAuthenticated = false;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function starIcons(rating) {
    const full = Math.max(0, Math.min(5, Number(rating) || 0));
    return `${'<i class="fa-solid fa-star"></i>'.repeat(full)}${'<i class="fa-regular fa-star"></i>'.repeat(5 - full)}`;
}

function setFavoriteButton(button, favorited) {
    if (!button) return;
    button.classList.toggle('is-favorite', favorited);
    button.innerHTML = favorited ? '<i class="fa-solid fa-heart"></i>' : '<i class="fa-regular fa-heart"></i>';
    button.setAttribute('aria-label', favorited ? 'Remove from favorites' : 'Save vehicle');
}

async function toggleFavorite(vehicleId, button) {
    if (!isAuthenticated) {
        window.location.href = 'login.php';
        return;
    }

    const response = await fetch('../api/favorites/toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ vehicle_id: vehicleId })
    });
    const data = await response.json();
    if (response.ok) {
        setFavoriteButton(button, data.favorited);
    }
}

async function hydrateFavoriteStatus(vehicleId, button) {
    if (!button || !isAuthenticated) return;
    try {
        const response = await fetch(`../api/favorites/status.php?vehicle_id=${vehicleId}`);
        const data = await response.json();
        setFavoriteButton(button, data.favorited === true);
    } catch {}
}

function renderSpecs(vehicle) {
    const specs = [
        { icon: 'fa-user-group', label: 'Capacity', value: `${vehicle.seats || 4} seats` },
        { icon: 'fa-gears', label: 'Transmission', value: vehicle.transmission || 'Manual' },
        { icon: 'fa-gas-pump', label: 'Fuel', value: vehicle.fuel_type || 'Petrol' },
        { icon: 'fa-location-dot', label: 'Hub', value: vehicle.location_name || 'SAWARI hub' },
        { icon: 'fa-calendar', label: 'Model year', value: vehicle.model_year || '-' },
        { icon: 'fa-circle-check', label: 'Status', value: vehicle.status || 'available' }
    ];

    document.getElementById('vehicle-spec-grid').innerHTML = specs.map(spec => `
        <div class="vehicle-spec-card">
            <i class="fa-solid ${spec.icon}"></i>
            <span>${escapeHtml(spec.label)}</span>
            <strong>${escapeHtml(spec.value)}</strong>
        </div>
    `).join('');
}

function renderReviews(reviews) {
    const list = document.getElementById('reviews-list');
    const count = document.getElementById('reviews-count');

    if (!reviews.length) {
        count.textContent = '0 reviews';
        list.innerHTML = '<p class="muted-text">No reviews yet. Be the first to review this vehicle.</p>';
        return;
    }

    count.textContent = `${reviews.length} review${reviews.length === 1 ? '' : 's'}`;
    list.innerHTML = reviews.map(review => `
        <article class="review-card">
            <div class="reviewer-info">
                <div class="reviewer-avatar">${escapeHtml((review.reviewer_name || 'U').charAt(0).toUpperCase())}</div>
                <div>
                    <span class="reviewer-name">${escapeHtml(review.reviewer_name || 'SAWARI user')}</span>
                    <div class="reviewer-rating">${starIcons(review.rating)}</div>
                </div>
            </div>
            <p class="review-text">${escapeHtml(review.comment || 'No comment provided.')}</p>
            <small>${escapeHtml((review.created_at || '').split(' ')[0])}</small>
        </article>
    `).join('');
}

async function loadReviews(vehicleId) {
    try {
        const response = await fetch(`../api/reviews/read.php?vehicle_id=${vehicleId}`);
        if (!response.ok) {
            renderReviews([]);
            return;
        }
        const data = await response.json();
        renderReviews(data.records || []);
    } catch {
        renderReviews([]);
    }
}

function renderMiniCars(cars) {
    const moreCars = document.getElementById('more-cars');
    if (!moreCars) return;

    moreCars.innerHTML = cars.map(car => `
        <article class="car-card-mini" onclick="window.location.href='car-details.php?vehicle_id=${car.id}'">
            <img src="${escapeHtml(car.image_url)}" alt="${escapeHtml(car.name)}" onerror="this.src='../assets/images/car1.png'">
            <strong>${escapeHtml(car.name)}</strong>
            <span>Rs. ${Number(car.daily_rate || 0).toLocaleString()}/day</span>
        </article>
    `).join('');
}

async function loadMoreCars(currentId) {
    try {
        const response = await fetch('../api/vehicles/read.php');
        if (!response.ok) return;
        const data = await response.json();
        const cars = (data.records || []).filter(car => Number(car.id) !== Number(currentId)).slice(0, 4);
        renderMiniCars(cars);
    } catch {}
}

async function submitReview(event) {
    event.preventDefault();
    const message = document.getElementById('review-message');

    if (!isAuthenticated) {
        window.location.href = 'login.php';
        return;
    }

    try {
        const response = await fetch('../api/reviews/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                vehicle_id: currentVehicleId,
                rating: Number(document.getElementById('review-rating').value),
                comment: document.getElementById('review-comment').value.trim()
            })
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Review could not be submitted.');

        message.className = 'auth-message success';
        message.textContent = data.message;
        document.getElementById('review-comment').value = '';
        loadReviews(currentVehicleId);
    } catch (error) {
        message.className = 'auth-message error';
        message.textContent = error.message;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const authRes = await fetch('../api/auth/me.php');
        if (authRes.ok) {
            const authData = await authRes.json();
            isAuthenticated = authData.authenticated === true;
        }
    } catch {}

    const urlParams = new URLSearchParams(window.location.search);
    currentVehicleId = urlParams.get('vehicle_id') || 12;
    const favoriteBtn = document.getElementById('detail-favorite-btn');

    try {
        const response = await fetch(`../api/vehicles/read_single.php?id=${currentVehicleId}`);
        if (!response.ok) throw new Error('Vehicle not found');
        const vehicle = await response.json();

        document.title = `${vehicle.name} | SAWARI`;
        document.getElementById('car-name').textContent = vehicle.name;
        document.getElementById('vehicle-category').textContent = `${vehicle.brand} ${vehicle.category_name || ''}`.trim();
        document.getElementById('vehicle-description').textContent = vehicle.description || 'A reliable rental vehicle from SAWARI inventory.';
        document.getElementById('vehicle-price').textContent = `Rs. ${Number(vehicle.daily_rate || 0).toLocaleString()}`;
        const image = document.getElementById('car-image');
        image.src = vehicle.image_url || '../assets/images/car1.png';
        image.alt = vehicle.name;
        renderSpecs(vehicle);

        const bookBtn = document.getElementById('detail-book-btn');
        bookBtn.onclick = () => {
            if (!isAuthenticated) {
                window.location.href = 'login.php';
                return;
            }
            window.location.href = `booking.php?vehicle_id=${vehicle.id}`;
        };

        if (favoriteBtn) {
            favoriteBtn.onclick = () => toggleFavorite(vehicle.id, favoriteBtn);
            hydrateFavoriteStatus(vehicle.id, favoriteBtn);
        }

        loadReviews(vehicle.id);
        loadMoreCars(vehicle.id);
    } catch {
        document.getElementById('car-name').textContent = 'Vehicle not found';
        document.getElementById('vehicle-description').textContent = 'This vehicle could not be loaded.';
    }

    const reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', submitReview);
    }
});

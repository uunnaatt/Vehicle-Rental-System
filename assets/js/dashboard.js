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
                <button class="heart-btn" data-vehicle-id="${car.id}" onclick="event.stopPropagation(); toggleFavoriteVehicle(${car.id}, this)" aria-label="Save vehicle"><i class="fa-regular fa-heart"></i></button>
            </div>
            <div class="car-card-details">
                <h3 class="car-name">${car.name}</h3>
                <div class="car-meta-row">
                    <span class="car-category">${car.category_name || ''}</span>
                    <div class="car-rating">
                        <span class="rating-value">4.8</span>
                        <span class="star"><i class="fa-solid fa-star"></i></span>
                    </div>
                </div>
                <div class="car-location">
                    <span class="location-icon"><i class="fa-solid fa-location-dot"></i></span>
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

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function createAiRecommendationCard(recommendation) {
    const car = recommendation.vehicle;
    if (!car) return '';

    return `
        <article class="ai-result-card" onclick="window.location.href='car-details.php?vehicle_id=${car.id}'">
            <div class="ai-score">
                <span>${Number(recommendation.match_score || 0)}</span>
                <small>match</small>
            </div>
            <div class="ai-result-image">
                <img src="${escapeHtml(car.image_url)}" alt="${escapeHtml(car.name)}" onerror="this.src='../assets/images/car1.png'">
            </div>
            <div class="ai-result-body">
                <div class="ai-result-topline">
                    <span>${escapeHtml(car.category_name || 'Vehicle')}</span>
                    <span>${escapeHtml(car.fuel_type || '')}</span>
                </div>
                <h3>${escapeHtml(car.name)}</h3>
                <p class="ai-headline">${escapeHtml(recommendation.headline)}</p>
                <p>${escapeHtml(recommendation.reason)}</p>
                <div class="ai-result-meta">
                    <span><i class="fa-solid fa-user-group"></i> ${Number(car.seats || 0)} seats</span>
                    <span><i class="fa-solid fa-location-dot"></i> ${escapeHtml(car.location_name || 'SAWARI hub')}</span>
                </div>
                <div class="ai-result-footer">
                    <strong>Rs. ${Number(car.daily_rate || 0).toLocaleString()}<small>/Day</small></strong>
                    <button type="button" onclick="event.stopPropagation(); bookVehicle(event, ${Number(car.id)})">Book now</button>
                </div>
            </div>
        </article>
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
    hydrateFavoriteButtons(el);
}

function renderAiRecommendations(data) {
    const resultsSection = document.getElementById('ai-results-section');
    const resultsGrid = document.getElementById('ai-results-grid');
    const status = document.getElementById('ai-advisor-status');
    const recommendTitle = document.getElementById('recommend-title');

    if (!resultsSection || !resultsGrid) return;

    const recommendations = data.recommendations || [];
    if (recommendations.length === 0) {
        resultsSection.style.display = 'none';
        if (status) status.textContent = 'No matches found for those details.';
        return;
    }

    resultsSection.style.display = '';
    resultsGrid.innerHTML = recommendations.map(createAiRecommendationCard).join('');
    renderSection('recommend-cars', recommendations.map(item => item.vehicle).filter(Boolean));

    if (recommendTitle) {
        recommendTitle.textContent = 'AI Recommended For You';
    }

    if (status) {
        const engineLabel = data.engine === 'mistral' ? 'Mistral AI' : 'local AI ranking';
        status.textContent = `${data.summary || 'Recommendations ready.'} (${engineLabel})`;
    }
}

function getAiAdvisorPayload() {
    return {
        trip_type: document.getElementById('ai-trip-type')?.value || 'general trip',
        destination: document.getElementById('ai-destination')?.value || '',
        terrain: document.getElementById('ai-terrain')?.value || 'mixed',
        travelers: Number(document.getElementById('ai-travelers')?.value || 4),
        days: Number(document.getElementById('ai-days')?.value || 2),
        budget_per_day: Number(document.getElementById('ai-budget')?.value || 0),
        notes: document.getElementById('ai-notes')?.value || ''
    };
}

async function requestAiRecommendations(event) {
    event.preventDefault();

    const button = document.getElementById('ai-advisor-btn');
    const status = document.getElementById('ai-advisor-status');

    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Thinking';
    }
    if (status) status.textContent = 'Checking the fleet against your trip...';

    try {
        const response = await fetch('../api/ai/recommend.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(getAiAdvisorPayload())
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'AI advisor failed');
        }

        renderAiRecommendations(data);
    } catch (error) {
        console.error(error);
        if (status) status.textContent = 'Could not prepare AI recommendations right now.';
    } finally {
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Recommend';
        }
    }
}

function scrollCars(section, direction) {
    const carousel = document.getElementById(`${section}-cars`);
    carousel.scrollBy({ left: direction * 320, behavior: 'smooth' });
}

function setFavoriteVisual(btn, favorited) {
    if (!btn) return;
    btn.classList.toggle('is-favorite', favorited);
    btn.innerHTML = favorited ? '<i class="fa-solid fa-heart"></i>' : '<i class="fa-regular fa-heart"></i>';
}

async function toggleFavoriteVehicle(vehicleId, btn) {
    if (!isLoggedIn) {
        window.location.href = 'login.php';
        return;
    }

    try {
        const res = await fetch('../api/favorites/toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ vehicle_id: vehicleId })
        });
        const data = await res.json();
        if (res.ok) {
            setFavoriteVisual(btn, data.favorited);
            loadFavoriteVehicles();
        }
    } catch (e) {
        console.error('Favorite error:', e);
    }
}

async function hydrateFavoriteButtons(root = document) {
    if (!isLoggedIn) return;
    const buttons = [...root.querySelectorAll('.heart-btn[data-vehicle-id]')];
    await Promise.all(buttons.map(async (btn) => {
        try {
            const vehicleId = btn.dataset.vehicleId;
            const res = await fetch(`../api/favorites/status.php?vehicle_id=${vehicleId}`);
            const data = await res.json();
            setFavoriteVisual(btn, data.favorited === true);
        } catch {}
    }));
}

async function loadFavoriteVehicles() {
    const section = document.getElementById('favorites-section');
    const grid = document.getElementById('favorite-cars');
    if (!section || !grid || !isLoggedIn) return;

    try {
        const res = await fetch('../api/favorites/list.php');
        if (!res.ok) {
            section.style.display = 'none';
            return;
        }
        const data = await res.json();
        const favorites = data.records || [];
        if (!favorites.length) {
            section.style.display = 'none';
            return;
        }
        section.style.display = '';
        renderSection('favorite-cars', favorites);
    } catch {
        section.style.display = 'none';
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

const aiAdvisorForm = document.getElementById('ai-advisor-form');
if (aiAdvisorForm) {
    aiAdvisorForm.addEventListener('submit', requestAiRecommendations);
}

loadAllVehicles();
setTimeout(loadFavoriteVehicles, 400);

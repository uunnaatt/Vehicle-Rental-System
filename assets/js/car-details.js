document.addEventListener('DOMContentLoaded', async () => {
    // Check auth status (non-blocking — we recheck on Book Now click)
    let isAuthenticated = false;
    try {
        const authRes = await fetch('../api/auth/me.php');
        if (authRes.ok) {
            const authData = await authRes.json();
            isAuthenticated = authData.authenticated === true;
        }
    } catch {}
    const urlParams = new URLSearchParams(window.location.search);
    let vehicleId = urlParams.get('vehicle_id');
    const carSlug = urlParams.get('car');

    // Fallback logic for legacy hardcoded links
    if (!vehicleId && carSlug) {
        const slugMap = {
            'tesla-model-s': 12, // Model S Plaid
            'ferrari-laferrari': 13, // M340i
            'lamborghini': 1, // Land Cruiser
            'bmw-gts3-m2': 13,
            'ferrari-ff': 12,
            'kala-gadi': 1,
            'nilo-gadi': 2,
            'hariyo-gadi': 3
        };
        vehicleId = slugMap[carSlug] || 12;
    }

    if (!vehicleId) vehicleId = 12; // Base default

    try {
        const res = await fetch(`../api/vehicles/read_single.php?id=${vehicleId}`);
        if(res.ok) {
            const data = await res.json();
            document.getElementById('car-name').textContent = data.name.toUpperCase();
            document.getElementById('car-image').src = data.image_url;
            
            // Set dynamic pricing text on features block if exists, or do any custom binds
            const featureTitle = document.querySelector('.section-heading');
            if(featureTitle && data.daily_rate) {
                // optional UI tweak
                // featureTitle.innerHTML += ` <span style="float:right; color:#22c55e;">Rs. ${data.daily_rate}/Day</span>`;
            }

            // Update the book now button — guard auth at click time
            const bookNowBtn = document.querySelector('.btn-book-now');
            if (bookNowBtn) {
                bookNowBtn.onclick = () => {
                    if (!isAuthenticated) {
                        window.location.href = 'login.php';
                        return;
                    }
                    window.location.href = `booking.php?vehicle_id=${data.id}`;
                };
            }
        }
    } catch (e) {
        console.error("Failed to load car details via API", e);
    }

    // Load more cars from API
    const moreCars = document.getElementById('more-cars');
    if (moreCars) {
        try {
            const allRes = await fetch('../api/vehicles/read.php');
            if (allRes.ok) {
                const allData = await allRes.json();
                const selected = allData.records.slice(0, 4); // get first 4
                moreCars.innerHTML = selected.map(car => `
                    <div class="car-card-mini" onclick="window.location.href='car-details.php?vehicle_id=${car.id}'" style="cursor: pointer;">
                        <img src="${car.image_url}" alt="${car.name}">
                        <button class="heart-btn" onclick="event.stopPropagation(); toggleHeart(this)">♡</button>
                    </div>
                `).join('');
            }
        } catch(e) {}
    }
});
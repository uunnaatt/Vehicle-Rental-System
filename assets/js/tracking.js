let userMap = null;
let userMarker = null;

document.addEventListener('DOMContentLoaded', () => {
    if (window.L) {
        userMap = L.map('user-tracking-map').setView([27.700769, 85.300140], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(userMap);
    }

    fetchUserTracking();
    window.userTrackingPoll = setInterval(fetchUserTracking, 15000);
});

async function fetchUserTracking() {
    const infoEl = document.getElementById('tracking-info');

    try {
        const res = await fetch('../api/tracking/user_active.php');
        const data = await res.json();

        if (!res.ok) {
            infoEl.textContent = data.message || 'No active booking found.';
            return;
        }

        infoEl.innerHTML = `
            <div><strong>Vehicle:</strong> ${data.vehicle_name}</div>
            <div><strong>Booked Date:</strong> ${data.booked_date}</div>
            <div><strong>Return Date:</strong> ${data.return_date}</div>
            <div><strong>Payment:</strong> ${data.payment_status}</div>
            <div><strong>Status:</strong> ${data.booking_status}</div>
            <div><strong>Location:</strong> ${data.location_label}</div>
            <div><strong>Last Update:</strong> ${data.last_updated}</div>
        `;

        const lat = Number(data.lat);
        const lng = Number(data.lng);
        if (!Number.isFinite(lat) || !Number.isFinite(lng) || !userMap) return;

        if (userMarker) userMap.removeLayer(userMarker);
        userMarker = L.marker([lat, lng]).addTo(userMap);
        userMarker.bindPopup(`<strong>${data.vehicle_name}</strong>`).openPopup();
        userMap.setView([lat, lng], 13);
    } catch (e) {
        infoEl.textContent = 'Unable to load tracking right now.';
    }
}

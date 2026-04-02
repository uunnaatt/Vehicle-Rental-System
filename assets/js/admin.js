// assets/js/admin.js – Enhanced Admin Panel

document.addEventListener("DOMContentLoaded", () => {
    const userName = localStorage.getItem('user_name') || 'Admin';
    document.getElementById('admin-name').innerText = userName;

    fetchAdminStats();
    fetchRecentBookings();
    loadCategoryBreakdown();
    loadReviewsPreview();
});

function showSection(sectionId, el) {
    document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.sidebar-menu li').forEach(li => li.classList.remove('active'));
    document.getElementById('section-' + sectionId).style.display = 'block';
    document.getElementById('section-heading').textContent =
        { overview: 'Overview', vehicles: 'Manage Vehicles', bookings: 'All Bookings', reviews: 'Customer Reviews' }[sectionId] || sectionId;
    if (el) el.closest('li').classList.add('active');

    if (sectionId === 'vehicles') fetchAdminVehicles();
    if (sectionId === 'bookings') fetchAllBookings();
    if (sectionId === 'reviews') fetchAllReviews();
}

async function fetchAdminStats() {
    try {
        const res = await fetch('../api/admin/stats.php');
        const data = await res.json();
        document.getElementById('stat-vehicles').innerText = data.total_vehicles || 0;
        document.getElementById('stat-bookings').innerText = data.total_bookings || 0;
        document.getElementById('stat-revenue').innerText = 'Rs. ' + Number(data.total_revenue || 0).toLocaleString();
    } catch (e) { console.error('Stats error:', e); }
}

async function fetchRecentBookings() {
    const tbody = document.getElementById('recent-bookings-body');
    try {
        const res = await fetch('../api/bookings/admin_all.php');
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (!data.records?.length) { tbody.innerHTML = '<tr><td colspan="7" class="text-center">No bookings yet.</td></tr>'; return; }
        tbody.innerHTML = data.records.map(b => `
            <tr>
                <td>#${b.id}</td>
                <td>${b.user_name}</td>
                <td>${b.vehicle_name}</td>
                <td>${b.start_date}</td>
                <td>${b.end_date}</td>
                <td>Rs. ${Number(b.total_price).toLocaleString()}</td>
                <td><span class="badge ${b.status}">${b.status}</span></td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No bookings found.</td></tr>';
    }
}

let allAdminVehicles = [];

async function fetchAdminVehicles() {
    const tbody = document.getElementById('admin-vehicles-body');
    if (allAdminVehicles.length > 0) { renderVehicleTable(allAdminVehicles); return; }
    try {
        const res = await fetch('../api/vehicles/read.php');
        if (!res.ok) throw new Error();
        const data = await res.json();
        allAdminVehicles = data.records || [];
        renderVehicleTable(allAdminVehicles);
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No vehicles found.</td></tr>';
    }
}

function renderVehicleTable(vehicles) {
    const tbody = document.getElementById('admin-vehicles-body');
    if (!vehicles.length) { tbody.innerHTML = '<tr><td colspan="7" class="text-center">No matches.</td></tr>'; return; }
    tbody.innerHTML = vehicles.map(v => `
        <tr>
            <td><img src="${v.image_url}" class="vehicle-img-small" alt="${v.name}" onerror="this.src='../assets/images/car1.png'"></td>
            <td><strong>${v.name}</strong><br><small style="opacity:0.6">${v.model_year} · ${v.fuel_type}</small></td>
            <td>${v.brand}</td>
            <td>${v.category_name || '–'}</td>
            <td>${v.transmission}</td>
            <td>Rs. ${Number(v.daily_rate).toLocaleString()}</td>
            <td><span class="badge ${v.status}">${v.status}</span></td>
        </tr>
    `).join('');
}

function filterVehicles(category) {
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active-chip'));
    event.target.classList.add('active-chip');
    const filtered = category ? allAdminVehicles.filter(v => v.category_name === category) : allAdminVehicles;
    renderVehicleTable(filtered);
}

async function fetchAllBookings() {
    const tbody = document.getElementById('admin-bookings-body');
    try {
        const res = await fetch('../api/bookings/admin_all.php');
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (!data.records?.length) { tbody.innerHTML = '<tr><td colspan="7" class="text-center">No bookings.</td></tr>'; return; }
        tbody.innerHTML = data.records.map(b => `
            <tr>
                <td>#${b.id}</td>
                <td>${b.user_name}</td>
                <td>${b.vehicle_name}</td>
                <td>${b.start_date}</td>
                <td>${b.end_date}</td>
                <td>Rs. ${Number(b.total_price).toLocaleString()}</td>
                <td><span class="badge ${b.status}">${b.status}</span></td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No bookings found.</td></tr>';
    }
}

async function fetchAllReviews() {
    const container = document.getElementById('admin-all-reviews');
    try {
        const res = await fetch('../api/reviews/read.php');
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (!data.records?.length) { container.innerHTML = '<p style="opacity:0.6">No reviews yet.</p>'; return; }
        container.innerHTML = data.records.map(r => `
            <div style="background:rgba(30,41,59,0.5); border-radius:16px; padding:20px; border:1px solid rgba(255,255,255,0.05);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:8px;">
                    <div><strong style="color:#f8fafc;">${r.reviewer_name}</strong><span style="opacity:0.5; margin-left:10px; font-size:13px;">on ${r.vehicle_name || 'Vehicle'}</span></div>
                    <span style="color:#f59e0b; font-size:16px; letter-spacing:2px">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</span>
                </div>
                <p style="color:#cbd5e1; font-size:14px; line-height:1.7;">"${r.comment}"</p>
                <p style="font-size:12px; color:#64748b; margin-top:8px;">${r.created_at?.split(' ')[0] || ''}</p>
            </div>
        `).join('');
    } catch (e) {
        container.innerHTML = '<p style="opacity:0.6;">Could not load reviews.</p>';
    }
}

async function loadCategoryBreakdown() {
    const container = document.getElementById('category-breakdown');
    try {
        const res = await fetch('../api/categories/read.php');
        if (!res.ok) throw new Error();
        const catData = await res.json();
        const vRes = await fetch('../api/vehicles/read.php');
        const vData = await vRes.json();
        const vehicles = vData.records || [];
        container.innerHTML = catData.records.map(c => {
            const count = vehicles.filter(v => v.category_name === c.name).length;
            const pct = vehicles.length ? Math.round((count / vehicles.length) * 100) : 0;
            return `
                <div style="margin-bottom:14px">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:14px; color:#cbd5e1;">
                        <span>${c.name}</span><span>${count} vehicles</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.08); border-radius:10px; height:8px; overflow:hidden;">
                        <div style="background:linear-gradient(90deg,#38bdf8,#818cf8); width:${pct}%; height:100%; border-radius:10px; transition:width 0.6s;"></div>
                    </div>
                </div>
            `;
        }).join('');
    } catch (e) { container.innerHTML = '<p style="opacity:0.5">Could not load.</p>'; }
}

async function loadReviewsPreview() {
    const container = document.getElementById('admin-reviews-preview');
    try {
        const res = await fetch('../api/reviews/read.php');
        if (!res.ok) throw new Error();
        const data = await res.json();
        if (!data.records?.length) { container.innerHTML = '<p style="opacity:0.5">No reviews yet.</p>'; return; }
        container.innerHTML = data.records.slice(0, 3).map(r => `
            <div style="margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,0.05);">
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-weight:600; font-size:14px; color:#f8fafc;">${r.reviewer_name}</span>
                    <span style="color:#f59e0b; font-size:13px">${'★'.repeat(r.rating)}</span>
                </div>
                <p style="font-size:13px; color:#94a3b8; line-height:1.5; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">"${r.comment}"</p>
            </div>
        `).join('');
    } catch (e) { container.innerHTML = '<p style="opacity:0.5">Could not load.</p>'; }
}

function toggleSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    sidebar.style.width = sidebar.style.width === '0px' ? '280px' : '0px';
}

function logout() {
    localStorage.clear();
    window.location.href = '../views/login.php';
}

<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/style.css">

<main class="category-page" style="min-height:100vh;">
    <div class="category-container" style="max-width:900px; margin:0 auto; padding:40px 30px;">
        <div class="profile-header" style="display:flex; align-items:center; gap:30px; background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); border-radius:24px; padding:30px; margin-bottom:40px; box-shadow:0 8px 32px rgba(0,0,0,0.1);">
            <div class="avatar-wrap" style="width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,#38bdf8,#818cf8); display:flex; align-items:center; justify-content:center; font-size:36px; color:white; font-weight:700; flex-shrink:0;" id="profile-avatar">?</div>
            <div>
                <h1 id="profile-name" style="font-size:28px; font-weight:700; margin-bottom:6px;">Loading...</h1>
                <p id="profile-email" style="opacity:0.7; font-size:15px;"></p>
                <span id="profile-role" class="badge" style="display:inline-block; margin-top:6px; background:rgba(56,189,248,0.2); color:#38bdf8; padding:4px 12px; border-radius:20px; font-size:12px; text-transform:uppercase; font-weight:600;"></span>
            </div>
        </div>

        <!-- Tabs -->
        <div style="display:flex; gap:10px; margin-bottom:30px; flex-wrap:wrap;">
            <button class="tab-btn active" onclick="switchTab('bookings', this)" style="padding:10px 22px; border-radius:20px; border:none; background:rgba(255,255,255,0.3); color:#000; font-weight:600; cursor:pointer;">📅 My Bookings</button>
            <button class="tab-btn" onclick="switchTab('reviews', this)" style="padding:10px 22px; border-radius:20px; border:none; background:rgba(255,255,255,0.15); color:#000; font-weight:600; cursor:pointer;">⭐ My Reviews</button>
        </div>

        <!-- My Bookings -->
        <div id="tab-bookings" class="profile-tab">
            <div id="bookings-list" style="display:flex; flex-direction:column; gap:20px;">
                <p>Loading bookings...</p>
            </div>
        </div>

        <!-- My Reviews -->
        <div id="tab-reviews" class="profile-tab" style="display:none;">
            <div id="reviews-list" style="display:flex; flex-direction:column; gap:20px;">
                <p>Loading reviews...</p>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const userId = localStorage.getItem('user_id') || '2';
    const userName = localStorage.getItem('user_name') || 'Guest';
    const userEmail = localStorage.getItem('user_email') || '';
    const userRole = localStorage.getItem('user_role') || 'user';

    document.getElementById('profile-name').textContent = userName;
    document.getElementById('profile-email').textContent = userEmail;
    document.getElementById('profile-role').textContent = userRole;
    document.getElementById('profile-avatar').textContent = userName.charAt(0).toUpperCase();

    loadBookings(userId);
    loadReviews(userId);
});

function switchTab(tab, btn) {
    document.querySelectorAll('.profile-tab').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.style.background = 'rgba(255,255,255,0.15)';
    });
    document.getElementById('tab-' + tab).style.display = 'block';
    btn.style.background = 'rgba(255,255,255,0.4)';
}

async function loadBookings(userId) {
    const container = document.getElementById('bookings-list');
    try {
        const res = await fetch(`../api/bookings/user_bookings.php?user_id=${userId}`);
        if(!res.ok) throw new Error('none');
        const data = await res.json();
        if(!data.records?.length) { container.innerHTML = '<p style="opacity:0.7">No bookings yet. <a href="category.php" style="font-weight:700">Browse vehicles →</a></p>'; return; }
        container.innerHTML = data.records.map(b => `
            <div style="background:rgba(255,255,255,0.2); border-radius:16px; padding:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; box-shadow:0 4px 15px rgba(0,0,0,0.08);">
                <div>
                    <h3 style="font-size:18px; font-weight:700; margin-bottom:4px;">🚘 ${b.vehicle_name}</h3>
                    <p style="font-size:14px; opacity:0.75;">📅 ${b.start_date} → ${b.end_date}</p>
                    <p style="font-size:14px; font-weight:600; margin-top:4px;">Rs. ${Number(b.total_price).toLocaleString()}</p>
                </div>
                <span style="padding:8px 18px; border-radius:20px; font-size:12px; font-weight:700; text-transform:uppercase; background:${b.status==='completed'?'rgba(52,211,153,0.2)':b.status==='confirmed'?'rgba(56,189,248,0.2)':'rgba(251,191,36,0.2)'}; color:${b.status==='completed'?'#059669':b.status==='confirmed'?'#0284c7':'#d97706'};">${b.status}</span>
            </div>
        `).join('');
    } catch(e) {
        container.innerHTML = '<p style="opacity:0.7;">Could not load bookings.</p>';
    }
}

async function loadReviews(userId) {
    const container = document.getElementById('reviews-list');
    try {
        const res = await fetch('../api/reviews/read.php');
        if(!res.ok) throw new Error('none');
        const data = await res.json();
        if(!data.records?.length) { container.innerHTML = '<p style="opacity:0.7">No reviews yet.</p>'; return; }
        container.innerHTML = data.records.slice(0, 5).map(r => `
            <div style="background:rgba(255,255,255,0.2); border-radius:16px; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.08);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <h4 style="font-weight:700;">🚘 ${r.vehicle_name || 'Vehicle'}</h4>
                    <span style="color:#f59e0b; font-size:16px;">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</span>
                </div>
                <p style="opacity:0.85; font-size:14px; line-height:1.6;">${r.comment || 'No comment.'}</p>
                <p style="font-size:12px; opacity:0.5; margin-top:8px;">${r.created_at?.split(' ')[0] || ''}</p>
            </div>
        `).join('');
    } catch(e) {
        container.innerHTML = '<p style="opacity:0.7;">Could not load reviews.</p>';
    }
}
</script>

</body>
</html>

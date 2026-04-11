// assets/js/admin.js – Enhanced Admin Panel

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const res = await fetch('../api/auth/me.php');
        const data = await res.json();
        if (!data.authenticated || data.role !== 'admin') {
            window.location.href = 'login.php';
            return;
        }
        document.getElementById('admin-name').innerText = data.full_name || 'Admin';
    } catch (e) {
        window.location.href = 'login.php';
        return;
    }

    fetchAdminStats();
    fetchRecentBookings();
    loadCategoryBreakdown();
    loadReviewsPreview();
});

function showSection(sectionId, el) {
    document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.sidebar-menu li').forEach(li => li.classList.remove('active'));
    
    const section = document.getElementById('section-' + sectionId);
    if (section) section.style.display = 'block';
    
    document.getElementById('section-heading').textContent =
        { overview: 'Overview', vehicles: 'Manage Vehicles', bookings: 'All Bookings', reviews: 'Customer Reviews', inbox: 'Support Inbox' }[sectionId] || sectionId;
    if (el) el.closest('li').classList.add('active');

    if (sectionId === 'vehicles') fetchAdminVehicles();
    if (sectionId === 'bookings') fetchAllBookings();
    if (sectionId === 'reviews') fetchAllReviews();
    if (sectionId === 'inbox') fetchInboxList();
    
    // Cleanup polling
    if(sectionId !== 'inbox') {
        if(window.adminChatPoll) clearInterval(window.adminChatPoll);
        window.adminChatPoll = null;
    }
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
        // Populate shared cache so Review modal works from Overview tab too
        if (data.records?.length) adminBookingsCache = data.records;
        if (!data.records?.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center">No bookings yet.</td></tr>'; return; }
        tbody.innerHTML = data.records.map(b => `
            <tr>
                <td>#${b.id}</td>
                <td><a href="#" onclick="openUserModal(event, ${b.user_id}, ${b.id})" style="color:#38bdf8; text-decoration:none; font-weight:600;">${b.user_name}</a></td>
                <td style="font-size:12px;">${b.user_account_email || '—'}</td>
                <td>${b.vehicle_name}</td>
                <td>${b.start_date}</td>
                <td>${b.end_date}</td>
                <td>Rs. ${Number(b.total_price).toLocaleString()}</td>
                <td><span class="badge ${b.status}">${b.status}</span></td>
                <td>
                    <button onclick='openCollateralModal(${b.id})' style="background:none; border:none; color:#38bdf8; cursor:pointer;"><i class="fa-solid fa-magnifying-glass"></i> Review</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No bookings found.</td></tr>';
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
            <td>
                <select class="status-dropdown badge ${v.status}" onchange="updateVehicleStatus(${v.id}, this)">
                    <option value="Available" ${v.status === 'Available' ? 'selected' : ''}>Available</option>
                    <option value="Maintenance" ${v.status === 'Maintenance' ? 'selected' : ''}>Maintenance</option>
                    <option value="Rented" ${v.status === 'Rented' ? 'selected' : ''}>Rented</option>
                </select>
            </td>
            <td>
                <button onclick='editVehicle(${JSON.stringify(v).replace(/'/g, "&#39;")})' style="background:none;border:none;color:#38bdf8;cursor:pointer;margin-right:10px;">✎ Edit</button>
                <button onclick="deleteVehicle(${v.id})" style="background:none;border:none;color:#ef4444;cursor:pointer;">🗑 Delete</button>
            </td>
        </tr>
    `).join('');
}

async function updateVehicleStatus(id, selectElement) {
    const newStatus = selectElement.value;
    selectElement.className = `status-dropdown badge ${newStatus}`;
    try {
        const res = await fetch('../api/vehicles/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: newStatus })
        });
        const data = await res.json();
        if(!res.ok || !data.success) {
            alert(data.message || 'Failed to update status');
            // Revert changes in front end by re-fetching
            fetchAdminVehicles();
        }
    } catch(e) {
        alert('An error occurred while updating the status.');
    }
}

function filterVehicles(category) {
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active-chip'));
    event.target.classList.add('active-chip');
    const filtered = category ? allAdminVehicles.filter(v => v.category_name === category) : allAdminVehicles;
    renderVehicleTable(filtered);
}

let adminBookingsCache = [];

async function fetchAllBookings() {
    const tbody = document.getElementById('admin-bookings-body');
    try {
        const res = await fetch('../api/bookings/admin_all.php');
        if (!res.ok) throw new Error();
        const data = await res.json();
        adminBookingsCache = data.records || [];
        if (!adminBookingsCache.length) { tbody.innerHTML = '<tr><td colspan="8" class="text-center">No bookings.</td></tr>'; return; }
        tbody.innerHTML = adminBookingsCache.map(b => `
            <tr>
                <td>#${b.id}</td>
                <td><a href="#" onclick="openUserModal(event, ${b.user_id}, ${b.id})" style="color:#38bdf8; text-decoration:none; font-weight:600;">${b.user_name}</a></td>
                <td style="font-size:12px;">${b.user_account_email || '—'}</td>
                <td>${b.vehicle_name}</td>
                <td>${b.start_date}</td>
                <td>${b.end_date}</td>
                <td>Rs. ${Number(b.total_price).toLocaleString()}</td>
                <td><span class="badge ${b.status}">${b.status}</span></td>
                <td>
                    <button onclick='openCollateralModal(${b.id})' style="background:none; border:none; color:#38bdf8; cursor:pointer;"><i class="fa-solid fa-magnifying-glass"></i> Review</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No bookings found.</td></tr>';
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
                    <span style="color:#f59e0b; font-size:16px; letter-spacing:2px">${'<i class="fa-solid fa-star"></i>'.repeat(r.rating)}${'<i class="fa-regular fa-star"></i>'.repeat(5-r.rating)}</span>
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
                    <span style="color:#f59e0b; font-size:13px">${'<i class="fa-solid fa-star"></i>'.repeat(r.rating)}</span>
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

async function logout() {
    try {
        await fetch('../api/auth/logout.php');
        localStorage.clear();
        window.location.href = '../views/login.php';
    } catch (e) {
        window.location.href = '../views/login.php';
    }
}

// Vehicle CRUD logic
function openVehicleModal() {
    document.getElementById('vehicleForm').reset();
    document.getElementById('vehicle_id').value = '';
    document.getElementById('existing_image_url').value = '';
    document.getElementById('vehicleModalTitle').innerText = 'Add Vehicle';
    document.getElementById('vehicleModal').style.display = 'flex';
}

function closeVehicleModal() {
    document.getElementById('vehicleModal').style.display = 'none';
}

function editVehicle(v) {
    document.getElementById('vehicleModalTitle').innerText = 'Edit Vehicle';
    document.getElementById('vehicle_id').value = v.id;
    document.getElementById('v_name').value = v.name;
    document.getElementById('v_brand').value = v.brand;
    document.getElementById('v_year').value = v.model_year;
    // Map category string to roughly an ID if category is string, fallback to 1 
    document.getElementById('v_category').value = v.category_id || 1; 
    document.getElementById('v_location').value = v.location_id || 1;
    document.getElementById('v_rate').value = v.daily_rate;
    document.getElementById('v_seats').value = v.seats || 4;
    document.getElementById('v_transmission').value = v.transmission;
    document.getElementById('v_fuel').value = v.fuel_type;
    document.getElementById('v_status').value = v.status;
    document.getElementById('v_desc').value = v.description || '';
    document.getElementById('existing_image_url').value = v.image_url || '';
    document.getElementById('v_image_url').value = '';
    document.getElementById('v_image_upload').value = '';
    
    document.getElementById('vehicleModal').style.display = 'flex';
}

async function saveVehicle(e) {
    e.preventDefault();
    const form = document.getElementById('vehicleForm');
    const formData = new FormData(form);
    
    const id = formData.get('id');
    const endpoint = id ? '../api/vehicles/update.php' : '../api/vehicles/create.php';

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            closeVehicleModal();
            fetchAdminVehicles(); // refresh list
        } else {
            alert(data.message || 'Failed to save vehicle');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred.');
    }
}

async function deleteVehicle(id) {
    if (!confirm("Are you sure you want to delete this vehicle?")) return;
    
    try {
        const res = await fetch('../api/vehicles/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        
        if (data.success) {
            alert(data.message);
            fetchAdminVehicles(); // refresh list
        } else {
            alert(data.message || 'Failed to delete vehicle');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred.');
    }
}

// Collateral Review Logic — now receives full booking object
function openCollateralModal(id) {
    const b = adminBookingsCache.find(x => x.id == id);
    if (!b) return;

    const bookingId  = b.id;
    const type       = b.collateral_type;
    const imageUrl   = b.collateral_image;
    const userName   = b.user_name;
    const status     = b.status;

    document.getElementById('collateralModalTitle').innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Booking #' + bookingId + ' &ndash; ' + userName;

    // Populate booker info panel
    const infoHtml = `
        <table style="width:100%; border-collapse:collapse;">
            <tr><td style="opacity:0.6; width:140px;"><i class="fa-solid fa-user"></i> Account Name</td><td><strong><a href="#" onclick="openUserModal(event, ${b.user_id}, ${b.id})" style="color:#38bdf8; text-decoration:none;">${userName || '—'}</a></strong></td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-solid fa-envelope"></i> Account Email</td><td>${b.user_account_email || '—'}</td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-regular fa-clipboard"></i> Booking Name</td><td>${b.booking_name || '—'}</td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-regular fa-envelope"></i> Booking Email</td><td>${b.booking_email || '—'}</td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-solid fa-phone"></i> Phone</td><td>${b.booking_phone || '—'}</td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-solid fa-id-card"></i> Collateral</td><td>${type || '—'}</td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-regular fa-calendar"></i> Dates</td><td>${b.start_date} &rarr; ${b.end_date}</td></tr>
            <tr><td style="opacity:0.6;"><i class="fa-solid fa-money-bill"></i> Total</td><td>Rs. ${Number(b.total_price).toLocaleString()}</td></tr>
        </table>
    `;
    document.getElementById('collateralBookerInfo').innerHTML = infoHtml;

    // Use the secure proxy to fetch the image
    const fileName = imageUrl ? imageUrl.split('/').pop() : '';
    document.getElementById('collateralModalImage').src = fileName ? `../api/bookings/get_collateral.php?file=${fileName}` : '../assets/images/car1.png';

    document.getElementById('collateralModalDetails').innerText = 'Current Status: ' + status;

    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');

    if (status !== 'pending') {
        approveBtn.style.display = 'none';
        rejectBtn.style.display = 'none';
        document.getElementById('collateralModalDetails').innerText += ' (Action already taken)';
    } else {
        approveBtn.style.display = 'block';
        rejectBtn.style.display = 'block';
        approveBtn.onclick = () => reviewBookingApp(bookingId, 'confirmed');
        rejectBtn.onclick = () => reviewBookingApp(bookingId, 'cancelled');
    }

    document.getElementById('collateralModal').style.display = 'flex';
}

function closeCollateralModal() {
    document.getElementById('collateralModal').style.display = 'none';
}

// ==========================================
// User Modal Logic
// ==========================================
function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

async function openUserModal(e, userId, bookingId = null) {
    e.preventDefault();
    const modal = document.getElementById('userModal');
    const content = document.getElementById('userModalContent');
    
    modal.style.display = 'flex';
    content.innerHTML = '<p style="text-align:center; opacity:0.7;">Fetching user details...</p>';

    if (!userId) {
        content.innerHTML = '<p style="color:#ef4444;">Error: User ID is missing.</p>';
        return;
    }

    try {
        const res = await fetch(`../api/users/read_single.php?id=${userId}`);
        const data = await res.json();
        
        if (!res.ok) {
            content.innerHTML = `<p style="color:#ef4444;">${data.message || 'Error fetching user'}</p>`;
            return;
        }

        let collateralHtml = '';
        if (bookingId && typeof adminBookingsCache !== 'undefined') {
            const booking = adminBookingsCache.find(b => b.id == bookingId);
            if (booking && booking.collateral_type) {
                let imgHtml = '';
                if (booking.collateral_image) {
                    imgHtml = `<img src="${booking.collateral_image}" alt="Collateral" style="max-width:100%; max-height:200px; border-radius:8px; margin-top:10px; border:1px solid rgba(255,255,255,0.1);">`;
                }
                collateralHtml = `
                    <div style="background:rgba(56,189,248,0.1); border:1px solid rgba(56,189,248,0.2); padding:15px; border-radius:10px; margin-top:15px;">
                        <h4 style="margin:0 0 10px 0; color:#e0f2fe;"><i class="fa-solid fa-id-card"></i> Booking #${bookingId} Collateral</h4>
                        <p style="margin:0; font-size:14px;"><strong style="color:#94a3b8;">Type:</strong> <span style="text-transform:capitalize;">${booking.collateral_type}</span></p>
                        ${imgHtml}
                    </div>
                `;
            }
        }

        content.innerHTML = `
            <div style="background:rgba(255,255,255,0.05); padding:15px; border-radius:10px;">
                <p style="margin-bottom:10px;"><strong style="color:#94a3b8; display:inline-block; width:100px;"><i class="fa-solid fa-hashtag"></i> ID:</strong> ${data.id}</p>
                <p style="margin-bottom:10px;"><strong style="color:#94a3b8; display:inline-block; width:100px;"><i class="fa-solid fa-user"></i> Name:</strong> <span style="font-size:16px; font-weight:600; color:#fff;">${data.full_name}</span></p>
                <p style="margin-bottom:10px;"><strong style="color:#94a3b8; display:inline-block; width:100px;"><i class="fa-solid fa-envelope"></i> Contact:</strong> ${data.phone_or_email}</p>
                <p style="margin-bottom:10px;"><strong style="color:#94a3b8; display:inline-block; width:100px;"><i class="fa-solid fa-shield-halved"></i> Role:</strong> <span style="text-transform:uppercase; font-size:12px; background:#1e293b; padding:4px 8px; border-radius:4px; border:1px solid #334155;">${data.role}</span></p>
                <p style="margin-bottom:0;"><strong style="color:#94a3b8; display:inline-block; width:100px;"><i class="fa-regular fa-clock"></i> Joined:</strong> ${data.created_at}</p>
            </div>
            ${collateralHtml}
        `;

    } catch (err) {
        content.innerHTML = '<p style="color:#ef4444;">Failed to communicate with server.</p>';
    }
}

// ===== ADMIN INBOX CHAT SYSTEM =====
let activeChatUserId = null;

async function fetchInboxList() {
    const list = document.getElementById('inbox-list');
    try {
        const res = await fetch('../api/chat/inbox.php');
        const data = await res.json();
        if(data.records && data.records.length > 0) {
            list.innerHTML = data.records.map(r => `
                <div onclick="openAdminChat(${r.id}, '${r.full_name}')" style="padding:15px 20px; border-bottom:1px solid rgba(255,255,255,0.05); cursor:pointer; hover:background:rgba(255,255,255,0.1);">
                    <div style="font-weight:700; color:#e2e8f0;">${r.full_name}</div>
                    <div style="font-size:12px; opacity:0.5; margin-top:4px;">User ID: ${r.id} | Active: ${r.last_msg_time || 'Just now'}</div>
                </div>
            `).join('');
        } else {
            list.innerHTML = '<div style="padding:20px; opacity:0.5;">No active conversations.</div>';
        }
    } catch(e) { 
        list.innerHTML = '<div style="padding:20px; opacity:0.5; color:#ef4444;">Error loading conversations.</div>';
    }
}

function openAdminChat(userId, userName) {
    activeChatUserId = userId;
    document.getElementById('active-chat-header').textContent = 'Chat with ' + userName;
    document.getElementById('admin-chat-input').disabled = false;
    document.getElementById('admin-chat-sendbtn').disabled = false;
    
    loadAdminChatMessages();
    if(window.adminChatPoll) clearInterval(window.adminChatPoll);
    window.adminChatPoll = setInterval(loadAdminChatMessages, 3000);
}

async function loadAdminChatMessages() {
    if(!activeChatUserId) return;
    const container = document.getElementById('admin-chat-messages');
    try {
        const res = await fetch(`../api/chat/get.php?user2=${activeChatUserId}`);
        const data = await res.json();
        if(data.records) {
            // Note: In secure session mode, user1 is always the person logged in (Admin)
            // So we check sender_id == data.current_user_id or similar if we had it, 
            // but for now we know if sender matches activeChatUserId it's the customer.
            container.innerHTML = data.records.map(m => {
                const isCustomer = (m.sender_id == activeChatUserId);
                return `<div style="max-width:80%; align-self:${isCustomer?'flex-start':'flex-end'}; background:${isCustomer?'rgba(255,255,255,0.1)':'#38bdf8'}; color:${isCustomer?'#fff':'#0f172a'}; padding:10px 15px; border-radius:15px; font-size:14px;">${m.message}</div>`;
            }).join('');
            container.scrollTop = container.scrollHeight;
        }
    } catch(e) {}
}

async function sendAdminChat() {
    if(!activeChatUserId) return;
    const input = document.getElementById('admin-chat-input');
    const msg = input.value.trim();
    if(!msg) return;
    input.value = '';
    
    try {
        await fetch('../api/chat/send.php', {
            method: 'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ receiver_id: activeChatUserId, message: msg })
        });
        loadAdminChatMessages();
    } catch(e) {}
}

async function reviewBookingApp(id, newStatus) {
    if (!confirm('Are you sure you want to mark this application as ' + newStatus + '?')) return;
    
    try {
        const res = await fetch('../api/bookings/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: newStatus })
        });
        const data = await res.json();
        
        if (data.success) {
            alert('Application ' + newStatus + ' successfully.');
            closeCollateralModal();
            fetchRecentBookings();
            fetchAllBookings();
            fetchAdminStats();
        } else {
            alert(data.message || 'Failed to update application');
        }
    } catch (err) {
        console.error(err);
        alert('An error occurred while updating the status.');
    }
}

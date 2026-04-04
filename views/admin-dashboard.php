<?php include '../includes/header.php'; ?>

<link rel="stylesheet" href="../assets/css/admin.css">

<main class="admin-dashboard">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-header">
            <h2>🚗 Sawari Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li class="active" id="menu-overview"><a href="#" onclick="showSection('overview', this)"><i class="icon">📊</i> Overview</a></li>
            <li id="menu-vehicles"><a href="#" onclick="showSection('vehicles', this)"><i class="icon">🚘</i> Vehicles</a></li>
            <li id="menu-bookings"><a href="#" onclick="showSection('bookings', this)"><i class="icon">📅</i> Bookings</a></li>
            <li id="menu-reviews"><a href="#" onclick="showSection('reviews', this)"><i class="icon">⭐</i> Reviews</a></li>
            <li><a href="index.php"><i class="icon">🏠</i> Go to Site</a></li>
            <li><a href="#" onclick="logout()"><i class="icon">🚪</i> Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <section class="admin-main">
        <header class="admin-topbar">
            <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
            <h2 style="font-size:18px; color:#e2e8f0; font-weight:600;" id="section-heading">Overview</h2>
            <div class="user-info">
                <span id="admin-name" style="color:#94a3b8; font-size:14px;">Admin</span>
                <img src="https://ui-avatars.com/api/?name=Admin&background=38bdf8&color=fff&size=80" alt="Admin" class="avatar">
            </div>
        </header>

        <div class="admin-content">

            <!-- ===== OVERVIEW ===== -->
            <div id="section-overview" class="admin-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon vehicles">🚘</div>
                        <div class="stat-details"><h3>Total Vehicles</h3><p id="stat-vehicles">–</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon bookings">📅</div>
                        <div class="stat-details"><h3>Total Bookings</h3><p id="stat-bookings">–</p></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon revenue">💰</div>
                        <div class="stat-details"><h3>Total Revenue</h3><p id="stat-revenue">–</p></div>
                    </div>
                </div>

                <!-- Quick charts placeholder -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-top:40px;">
                    <div style="background:rgba(30,41,59,0.5); border-radius:20px; padding:30px; border:1px solid rgba(255,255,255,0.05);">
                        <h3 style="margin-bottom:20px; color:#f8fafc;">📋 Category Breakdown</h3>
                        <div id="category-breakdown">Loading...</div>
                    </div>
                    <div style="background:rgba(30,41,59,0.5); border-radius:20px; padding:30px; border:1px solid rgba(255,255,255,0.05);">
                        <h3 style="margin-bottom:20px; color:#f8fafc;">⭐ Latest Reviews</h3>
                        <div id="admin-reviews-preview">Loading...</div>
                    </div>
                </div>

                <div class="mt-40">
                    <h2>All Bookings</h2>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Start</th><th>End</th><th>Total</th><th>Status</th><th>Review App</th></tr>
                            </thead>
                            <tbody id="recent-bookings-body">
                                <tr><td colspan="8" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===== VEHICLES ===== -->
            <div id="section-vehicles" class="admin-section" style="display:none;">
                <div class="section-header-flex">
                    <h1 class="section-title">Manage Vehicles</h1>
                    <button class="btn-primary" onclick="openVehicleModal()">+ Add Vehicle</button>
                </div>
                <!-- Category filter -->
                <div style="display:flex; gap:10px; margin-bottom:25px; flex-wrap:wrap;">
                    <button class="filter-chip active-chip" onclick="filterVehicles('')">All</button>
                    <button class="filter-chip" onclick="filterVehicles('SUV')">SUV</button>
                    <button class="filter-chip" onclick="filterVehicles('Sedan')">Sedan</button>
                    <button class="filter-chip" onclick="filterVehicles('Hatchback')">Hatchback</button>
                    <button class="filter-chip" onclick="filterVehicles('Pickup')">Pickup</button>
                </div>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Image</th><th>Name</th><th>Brand</th><th>Category</th><th>Transmission</th><th>Daily Rate</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="admin-vehicles-body">
                            <tr><td colspan="7" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== BOOKINGS ===== -->
            <div id="section-bookings" class="admin-section" style="display:none;">
                <h1 class="section-title">All Bookings</h1>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Start Date</th><th>End Date</th><th>Total Price</th><th>Status</th><th>Review App</th></tr>
                        </thead>
                        <tbody id="admin-bookings-body">
                            <tr><td colspan="8" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== REVIEWS ===== -->
            <div id="section-reviews" class="admin-section" style="display:none;">
                <h1 class="section-title">Customer Reviews</h1>
                <div id="admin-all-reviews" style="display:flex; flex-direction:column; gap:20px;">Loading...</div>
            </div>

        </div>
    </section>

    <!-- Vehicle Modal -->
    <div id="vehicleModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#1e293b; padding:30px; border-radius:15px; width:90%; max-width:600px; color:#fff; max-height:90vh; overflow-y:auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 id="vehicleModalTitle">Add Vehicle</h2>
                <button onclick="closeVehicleModal()" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            <form id="vehicleForm" onsubmit="saveVehicle(event)" enctype="multipart/form-data">
                <input type="hidden" id="vehicle_id" name="id">
                <input type="hidden" id="existing_image_url" name="existing_image_url">
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label>Name</label>
                        <input type="text" id="v_name" name="name" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Brand</label>
                        <input type="text" id="v_brand" name="brand" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Model Year</label>
                        <input type="number" id="v_year" name="model_year" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Category (ID 1=SUV, 2=Sedan, 3=Hatchback, 4=Pickup)</label>
                        <input type="number" id="v_category" name="category_id" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Location (ID 1-6)</label>
                        <input type="number" id="v_location" name="location_id" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Daily Rate (Rs.)</label>
                        <input type="number" step="0.01" id="v_rate" name="daily_rate" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Seats</label>
                        <input type="number" id="v_seats" name="seats" value="4" style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                    </div>
                    <div>
                        <label>Transmission</label>
                        <select id="v_transmission" name="transmission" style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                            <option value="Manual">Manual</option>
                            <option value="Automatic">Automatic</option>
                        </select>
                    </div>
                    <div>
                        <label>Fuel Type</label>
                        <select id="v_fuel" name="fuel_type" style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label>Status</label>
                        <select id="v_status" name="status" style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                            <option value="Available">Available</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Rented">Rented</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label>Description</label>
                    <textarea id="v_desc" name="description" rows="3" style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;"></textarea>
                </div>

                <div style="margin-bottom:15px; border:1px dashed #334155; padding:15px; border-radius:10px;">
                    <label style="display:block; margin-bottom:5px;">Vehicle Image</label>
                    <small style="color:#94a3b8; display:block; margin-bottom:10px;">Upload an image or provide a link.</small>
                    <input type="file" id="v_image_upload" name="image_upload" accept="image/*" style="width:100%; margin-bottom:10px; color:#fff;">
                    <div style="text-align:center; color:#64748b; margin-bottom:10px;">OR</div>
                    <input type="text" id="v_image_url" name="image_url" placeholder="Paste image URL here" style="width:100%; padding:8px; border-radius:5px; border:1px solid #334155; background:#0f172a; color:#fff;">
                </div>
                
                <button type="submit" class="btn-primary" style="width:100%;">Save Vehicle</button>
            </form>
        </div>
    </div>

    <!-- Collateral Review Modal -->
    <div id="collateralModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:#1e293b; padding:20px; border-radius:15px; width:90%; max-width:600px; color:#fff; text-align:center;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <h3 id="collateralModalTitle">Review Application</h3>
                <button onclick="closeCollateralModal()" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            <img id="collateralModalImage" src="" alt="Collateral" style="max-width:100%; max-height:400px; object-fit:contain; border-radius:10px; margin-bottom:20px; background:#0f172a; padding:10px;">
            <p id="collateralModalDetails" style="color:#94a3b8; font-size:14px; margin-bottom:20px;"></p>
            <div style="display:flex; gap:10px; justify-content:center;" id="collateralActionBtns">
                <button id="approveBtn" class="btn-primary" style="background:#10b981; border:none; cursor:pointer;">Approve</button>
                <button id="rejectBtn" class="btn-primary" style="background:#ef4444; border:none; cursor:pointer;">Reject</button>
            </div>
        </div>
    </div>

</main>

<script src="../assets/js/admin.js"></script>
</body>
</html>

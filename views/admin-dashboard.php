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
                                <tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Start</th><th>End</th><th>Total</th><th>Status</th></tr>
                            </thead>
                            <tbody id="recent-bookings-body">
                                <tr><td colspan="7" class="text-center">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ===== VEHICLES ===== -->
            <div id="section-vehicles" class="admin-section" style="display:none;">
                <div class="section-header-flex">
                    <h1 class="section-title">Manage Vehicles</h1>
                    <button class="btn-primary" onclick="alert('Vehicle add form coming soon!')">+ Add Vehicle</button>
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
                            <tr><th>Image</th><th>Name</th><th>Brand</th><th>Category</th><th>Transmission</th><th>Daily Rate</th><th>Status</th></tr>
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
                            <tr><th>ID</th><th>Customer</th><th>Vehicle</th><th>Start Date</th><th>End Date</th><th>Total Price</th><th>Status</th></tr>
                        </thead>
                        <tbody id="admin-bookings-body">
                            <tr><td colspan="7" class="text-center">Loading...</td></tr>
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
</main>

<script src="../assets/js/admin.js"></script>
</body>
</html>

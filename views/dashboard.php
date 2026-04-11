<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../includes/header.php'; ?>


<main class="dashboard-page">
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-heading">WELCOME TO SAWARI</h1>
                <p class="dashboard-subtitle">Nepal's No.1 rental platform</p>
            </div>
            
            <!-- Search & Filter -->
            <div class="search-filter-container">
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search your dream car..." id="search-input">
                    <button class="search-icon-btn">🔍</button>
                </div>
                <button class="filter-btn">⚙️</button>
            </div>
        </div>

        <!-- Nearby Section -->
        <section class="car-section">
            <div class="section-header">
                <h2 class="section-title">Nearby</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('nearby', -1)">&#10094;</button>
                    <button class="arrow-btn right" onclick="scrollCars('nearby', 1)">&#10095;</button>
                </div>
            </div>
            <div class="car-carousel" id="nearby-cars">
                <!-- Cars loaded by JS -->
            </div>
        </section>

        <!-- Most Popular Section -->
        <section class="car-section">
            <div class="section-header">
                <h2 class="section-title">Most Popular</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('popular', -1)">&#10094;</button>
                    <button class="arrow-btn right" onclick="scrollCars('popular', 1)">&#10095;</button>
                </div>
            </div>
            <div class="car-carousel" id="popular-cars">
                <!-- Cars loaded by JS -->
            </div>
        </section>

        <!-- Recommend For You Section -->
        <section class="car-section">
            <div class="section-header">
                <h2 class="section-title">Recommend For You</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('recommend', -1)">&#10094;</button>
                    <button class="arrow-btn right" onclick="scrollCars('recommend', 1)">&#10095;</button>
                </div>
            </div>
            <div class="car-carousel" id="recommend-cars">
                <!-- Cars loaded by JS -->
            </div>
        </section>

        <!-- Best Cars Section -->
        <section class="car-section">
            <div class="section-header">
                <h2 class="section-title">Best cars</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('best', -1)">&#10094;</button>
                    <button class="arrow-btn right" onclick="scrollCars('best', 1)">&#10095;</button>
                </div>
            </div>
            <div class="car-carousel" id="best-cars">
                <!-- Cars loaded by JS -->
            </div>
        </section>
    </div>
</main>

<script src="../assets/js/dashboard.js"></script>
</body>
</html>
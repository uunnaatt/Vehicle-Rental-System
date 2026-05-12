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
                    <button class="search-icon-btn" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                <button class="filter-btn" onclick="window.location.href='tracking.php'" title="Track Active Rental">📍</button>
            </div>
        </div>

        <section class="ai-advisor-section" aria-labelledby="ai-advisor-title">
            <div class="ai-advisor-copy">
                <span class="ai-kicker"><i class="fa-solid fa-wand-magic-sparkles"></i> AI Advisor</span>
                <h2 id="ai-advisor-title">Find the right vehicle for this trip</h2>
            </div>

            <form class="ai-advisor-form" id="ai-advisor-form">
                <label class="ai-field">
                    <span>Trip</span>
                    <select id="ai-trip-type" name="trip_type">
                        <option value="family mountain trip">Family mountain trip</option>
                        <option value="city business travel">City business travel</option>
                        <option value="airport pickup">Airport pickup</option>
                        <option value="adventure with luggage">Adventure with luggage</option>
                        <option value="premium event ride">Premium event ride</option>
                        <option value="budget city commute">Budget city commute</option>
                    </select>
                </label>

                <label class="ai-field">
                    <span>Route</span>
                    <input type="text" id="ai-destination" name="destination" placeholder="Kathmandu to Pokhara">
                </label>

                <label class="ai-field compact">
                    <span>People</span>
                    <input type="number" id="ai-travelers" name="travelers" min="1" max="12" value="4">
                </label>

                <label class="ai-field compact">
                    <span>Days</span>
                    <input type="number" id="ai-days" name="days" min="1" max="60" value="2">
                </label>

                <label class="ai-field compact">
                    <span>Budget/day</span>
                    <input type="number" id="ai-budget" name="budget_per_day" min="0" step="500" placeholder="10000">
                </label>

                <label class="ai-field">
                    <span>Terrain</span>
                    <select id="ai-terrain" name="terrain">
                        <option value="mixed">Mixed</option>
                        <option value="city">City</option>
                        <option value="highway">Highway</option>
                        <option value="mountain roads">Mountain roads</option>
                        <option value="off-road">Off-road</option>
                    </select>
                </label>

                <label class="ai-field wide">
                    <span>Notes</span>
                    <input type="text" id="ai-notes" name="notes" placeholder="Need automatic, extra bags, eco ride...">
                </label>

                <button type="submit" class="ai-advisor-btn" id="ai-advisor-btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Recommend
                </button>
            </form>

            <div class="ai-advisor-status" id="ai-advisor-status" role="status"></div>
        </section>

        <section class="car-section ai-results-section" id="ai-results-section" style="display:none;">
            <div class="section-header">
                <h2 class="section-title">AI Picks</h2>
            </div>
            <div class="ai-results-grid" id="ai-results-grid"></div>
        </section>

        <section class="car-section" id="favorites-section" style="display:none;">
            <div class="section-header">
                <h2 class="section-title">Favorite Vehicles</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('favorite', -1)" aria-label="Scroll favorites left"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="arrow-btn right" onclick="scrollCars('favorite', 1)" aria-label="Scroll favorites right"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="car-carousel" id="favorite-cars"></div>
        </section>

        <!-- Nearby Section -->
        <section class="car-section">
            <div class="section-header">
                <h2 class="section-title">Nearby</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('nearby', -1)" aria-label="Scroll nearby left"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="arrow-btn right" onclick="scrollCars('nearby', 1)" aria-label="Scroll nearby right"><i class="fa-solid fa-chevron-right"></i></button>
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
                    <button class="arrow-btn left" onclick="scrollCars('popular', -1)" aria-label="Scroll popular left"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="arrow-btn right" onclick="scrollCars('popular', 1)" aria-label="Scroll popular right"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="car-carousel" id="popular-cars">
                <!-- Cars loaded by JS -->
            </div>
        </section>

        <!-- Recommend For You Section -->
        <section class="car-section">
            <div class="section-header">
                <h2 class="section-title" id="recommend-title">Recommend For You</h2>
                <div class="section-arrows">
                    <button class="arrow-btn left" onclick="scrollCars('recommend', -1)" aria-label="Scroll recommended left"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="arrow-btn right" onclick="scrollCars('recommend', 1)" aria-label="Scroll recommended right"><i class="fa-solid fa-chevron-right"></i></button>
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
                    <button class="arrow-btn left" onclick="scrollCars('best', -1)" aria-label="Scroll best left"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="arrow-btn right" onclick="scrollCars('best', 1)" aria-label="Scroll best right"><i class="fa-solid fa-chevron-right"></i></button>
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

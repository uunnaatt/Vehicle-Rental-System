<?php include '../includes/header.php'; ?>

<main class="car-details-page">
    <div class="details-container">
        <!-- Left Content -->
        <div class="details-left">
            <h1 class="car-title" id="car-name">TESLA MODEL S</h1>
            
            <!-- Car Features -->
            <div class="features-section">
                <h3 class="section-heading">Car features</h3>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <span class="feature-label">Capacity</span>
                        <span class="feature-value">5 Seats</span>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <span class="feature-label">Engine Out</span>
                        <span class="feature-value">670 HP</span>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🚀</div>
                        <span class="feature-label">Max Speed</span>
                        <span class="feature-value">250km/h</span>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <span class="feature-label">Advance</span>
                        <span class="feature-value">Autopilot</span>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔋</div>
                        <span class="feature-label">Single Charge</span>
                        <span class="feature-value">405 Miles</span>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🅿️</div>
                        <span class="feature-label">Advance</span>
                        <span class="feature-value">Auto Parking</span>
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            <div class="reviews-section">
                <div class="reviews-header">
                    <h3 class="section-heading">Review (125)</h3>
                    <a href="#" class="see-all">See All</a>
                </div>
                <div class="reviews-grid">
                    <div class="review-card">
                        <div class="reviewer-info">
                            <img src="../assets/images/user1.png" alt="User" class="reviewer-img">
                            <div>
                                <span class="reviewer-name">Mr. Jack</span>
                                <div class="reviewer-rating">5.0 ★</div>
                            </div>
                        </div>
                        <p class="review-text">The rental car was clean, reliable, and the service was quick and efficient.</p>
                    </div>
                    <div class="review-card">
                        <div class="reviewer-info">
                            <img src="../assets/images/user2.png" alt="User" class="reviewer-img">
                            <div>
                                <span class="reviewer-name">Robert</span>
                                <div class="reviewer-rating">5.0 ★</div>
                            </div>
                        </div>
                        <p class="review-text">The rental car was clean, reliable, and the service was quick and efficient.</p>
                    </div>
                </div>
            </div>

            <!-- Book Now Button -->
            <button class="btn-book-now" onclick="window.location.href='booking.php?car=tesla-model-s'">BOOK NOW</button>

            <!-- More Cars -->
            <div class="more-cars-section">
                <a href="dashboard.php" class="more-link">More>>></a>
                <div class="more-cars-grid" id="more-cars">
                    <!-- Loaded by JS -->
                </div>
            </div>
        </div>

        <!-- Right Car Image -->
        <div class="details-right">
            <img src="../assets/images/tesla-black.png" alt="Tesla Model S" class="car-detail-image" id="car-image">
        </div>
    </div>
</main>

<script src="../assets/js/car-details.js"></script>
</body>
</html>
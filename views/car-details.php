<?php include '../includes/header.php'; ?>

<main class="vehicle-details-page">
    <section class="vehicle-detail-shell">
        <div class="vehicle-detail-media">
            <button class="favorite-action" id="detail-favorite-btn" type="button" aria-label="Save vehicle">
                <i class="fa-regular fa-heart"></i>
            </button>
            <img src="../assets/images/car1.png" alt="Vehicle" id="car-image" class="vehicle-detail-image">
        </div>

        <div class="vehicle-detail-content">
            <div class="vehicle-detail-kicker" id="vehicle-category">Vehicle</div>
            <h1 id="car-name">Loading vehicle...</h1>
            <p id="vehicle-description" class="vehicle-description">Fetching vehicle details from SAWARI inventory.</p>

            <div class="vehicle-price-row">
                <strong id="vehicle-price">Rs. 0</strong>
                <span>per day</span>
            </div>

            <div class="vehicle-spec-grid" id="vehicle-spec-grid">
                <div class="vehicle-spec-card"><i class="fa-solid fa-spinner fa-spin"></i><span>Loading</span></div>
            </div>

            <div class="vehicle-detail-actions">
                <button class="btn-book-now" id="detail-book-btn" type="button">Book now</button>
                <a href="dashboard.php" class="btn-detail-secondary">Browse more</a>
            </div>
        </div>
    </section>

    <section class="vehicle-reviews-layout">
        <div class="reviews-panel">
            <div class="reviews-header">
                <h2>Customer Reviews</h2>
                <span id="reviews-count">Loading</span>
            </div>
            <div class="reviews-grid" id="reviews-list">
                <p class="muted-text">Loading reviews...</p>
            </div>
        </div>

        <aside class="review-form-panel">
            <h2>Leave a Review</h2>
            <p class="muted-text">Share your experience after using this vehicle.</p>
            <form id="review-form" class="review-form">
                <label>
                    Rating
                    <select id="review-rating" required>
                        <option value="5">5 stars</option>
                        <option value="4">4 stars</option>
                        <option value="3">3 stars</option>
                        <option value="2">2 stars</option>
                        <option value="1">1 star</option>
                    </select>
                </label>
                <label>
                    Comment
                    <textarea id="review-comment" rows="5" placeholder="Write a helpful review..." required></textarea>
                </label>
                <button type="submit" class="btn-auth">Submit review</button>
                <div id="review-message" class="auth-message"></div>
            </form>
        </aside>
    </section>

    <section class="more-cars-section vehicle-more-section">
        <div class="section-header">
            <h2 class="section-title">More Vehicles</h2>
            <a href="dashboard.php" class="more-link">View all</a>
        </div>
        <div class="more-cars-grid" id="more-cars"></div>
    </section>
</main>

<script src="../assets/js/car-details.js"></script>
</body>
</html>

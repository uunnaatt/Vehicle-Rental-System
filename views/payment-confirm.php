<?php include '../includes/header.php'; ?>

<main class="payment-confirm-page">
    <div class="payment-confirm-container">
        <!-- Car Details Header -->
        <div class="car-details-header">
            <div class="car-info">
                <h1 class="car-name" id="confirm-car-name">Tesla Model S</h1>
                <p class="car-description">A car with high specs that are rented at an affordable price.</p>
                <div class="car-rating-display">
                    <span class="rating">5.0 ★</span>
                    <span class="review-count">(100+ Reviews)</span>
                </div>
            </div>
            <img src="../assets/images/tesla-black.png" alt="Tesla Model S" class="car-confirm-image" id="confirm-car-image">
        </div>

        <!-- Three Column Layout -->
        <div class="payment-grid">
            <!-- Booking Information -->
            <div class="payment-card booking-info">
                <h3 class="card-title">Booking Information</h3>
                <div class="info-row">
                    <span class="info-label">Booking ID</span>
                    <span class="info-value" id="booking-id">00451</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value" id="booking-name">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pick up Date</span>
                    <span class="info-value" id="booking-pickup">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Return Date</span>
                    <span class="info-value" id="booking-return">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Location</span>
                    <span class="info-value" id="booking-location">@Shore Dr, Chicago 6062 Usa</span>
                </div>
            </div>

            <!-- Stripe Payment Info -->
            <div class="payment-card card-info">
                <h3 class="card-title">Payment Method</h3>
                <div style="text-align: center; padding: 2rem 0;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" style="width: 150px; margin-bottom: 1rem;">
                    <p style="color: #666; font-size: 1.1rem;">You will be redirected to Stripe to securely complete your payment.</p>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="payment-card payment-summary">
                <h3 class="card-title">Payment</h3>
                <div class="info-row">
                    <span class="info-label">Trx ID</span>
                    <span class="info-value" id="trx-id">#141mtslv5854d58</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Amount</span>
                    <span class="info-value" id="payment-amount">Rs. xxx</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Service fee</span>
                    <span class="info-value" id="service-fee">Rs. xxx</span>
                </div>
                <div class="info-row total">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value" id="total-amount">Rs. xxx</span>
                </div>
            </div>
        </div>

        <!-- Confirm Payment Button -->
        <button class="btn-confirm-payment" id="confirm-payment-btn">CONFIRM PAYMENT</button>
    </div>
</main>

<script src="../assets/js/payment-confirm.js"></script>
</body>
</html>
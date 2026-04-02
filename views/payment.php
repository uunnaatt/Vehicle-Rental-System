<?php include '../includes/header.php'; ?>

<main class="payment-page">
    <div class="payment-container">
        <div class="payment-content">
            <div class="success-icon">✅</div>
            <h1 class="payment-title">Payment Successful!</h1>
            <p class="payment-message">Your booking has been confirmed.</p>
            
            <div class="booking-summary">
                <h3>Booking Summary</h3>
                <div class="summary-row">
                    <span>Car:</span>
                    <span id="summary-car">Tesla Model S</span>
                </div>
                <div class="summary-row">
                    <span>Amount:</span>
                    <span id="summary-amount">Rs. 1000</span>
                </div>
                <div class="summary-row">
                    <span>Pickup:</span>
                    <span id="summary-pickup">-</span>
                </div>
                <div class="summary-row">
                    <span>Return:</span>
                    <span id="summary-return">-</span>
                </div>
            </div>

            <div class="payment-actions">
                <button class="btn-download" onclick="alert('Booking confirmation downloaded!')">📥 Download Receipt</button>
                <button class="btn-home" onclick="window.location.href='index.php'">🏠 Go to Home</button>
            </div>
        </div>
    </div>
</main>

<script src="../assets/js/payment.js"></script>
</body>
</html>
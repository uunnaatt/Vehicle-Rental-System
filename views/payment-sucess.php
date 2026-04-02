<?php include '../includes/header.php'; ?>

<main class="payment-success-page">
    <div class="payment-success-container">
        <div class="success-icon">✅</div>
        <h1 class="payment-success-title">Payment Successful!</h1>
        <p class="payment-success-message">Your booking has been confirmed.</p>
        
        <div class="booking-summary">
            <h3>Booking Summary</h3>
            <div class="summary-row">
                <span>Booking ID:</span>
                <span id="summary-booking-id">-</span>
            </div>
            <div class="summary-row">
                <span>Car:</span>
                <span id="summary-car">Tesla Model S</span>
            </div>
            <div class="summary-row">
                <span>Trx ID:</span>
                <span id="summary-trx">-</span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span id="summary-amount">Rs. 1000</span>
            </div>
        </div>

        <div class="payment-success-actions">
            <button class="btn-download" onclick="alert('📥 Booking confirmation downloaded!')">Download Receipt</button>
            <button class="btn-home" onclick="window.location.href='index.php'">🏠 Go to Home</button>
        </div>
    </div>
</main>

<script src="../assets/js/payment-success.js"></script>
</body>
</html>
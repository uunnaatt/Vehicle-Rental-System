<?php include '../includes/header.php'; ?>

<main class="booking-page">
    <div class="booking-container">
        <!-- Left Form -->
        <div class="booking-form">
            <h1 class="booking-title" id="booking-car-name">TESLA MODEL - S</h1>

            <!-- Gender Selection -->
            <div class="form-group">
                <label class="form-label">Gender</label>
                <div class="gender-options">
                    <label class="gender-option">
                        <input type="radio" name="gender" value="male">
                        <span>👨 Male</span>
                    </label>
                    <label class="gender-option">
                        <input type="radio" name="gender" value="female">
                        <span>👩 Female</span>
                    </label>
                    <label class="gender-option">
                        <input type="radio" name="gender" value="others">
                        <span>👤 Others</span>
                    </label>
                </div>
            </div>

            <!-- Book with Driver Toggle -->
            <div class="form-group">
                <div class="driver-toggle">
                    <div>
                        <span class="toggle-label">Book with driver</span>
                        <p class="toggle-subtext">Don't have a driver? book with driver.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="driver-toggle">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- Personal Info -->
            <div class="form-group">
                <input type="text" class="form-input" placeholder="Full Name*" id="full-name" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-input" placeholder="Email Address*" id="email" required>
            </div>
            <div class="form-group">
                <input type="tel" class="form-input" placeholder="Contact*" id="contact" required>
            </div>

            <!-- Rental Date & Time -->
            <div class="form-group">
                <label class="form-label">Rental Date & Time</label>
                <div class="rental-options">
                    <button class="rental-btn active">Hour</button>
                    <button class="rental-btn">Day</button>
                    <button class="rental-btn">Weekly</button>
                    <button class="rental-btn">Monthly</button>
                </div>
            </div>

            <!-- Car Location -->
            <div class="form-group">
                <label class="form-label">Car Location</label>
                <input type="text" class="form-input" placeholder="📍 Dharan, Sunsari" id="location">
            </div>

            <!-- Pick Up & Return Date -->
            <div class="form-group">
                <div class="date-fields">
                    <div class="date-field">
                        <label>Pick up Date</label>
                        <input type="date" id="pickup-date" required>
                    </div>
                    <div class="date-field">
                        <label>Return Date</label>
                        <input type="date" id="return-date" required>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="error-message" id="error-message">
                ⚠️ PLEASE FILL UP THE INFORMATION!!!
            </div>

            <!-- Back Button -->
            <button class="btn-back" onclick="window.location.href='car-details.php'">BACK</button>
        </div>

        <!-- Right Car Image & Payment -->
        <div class="booking-right">
            <img src="../assets/images/tesla-black.png" alt="Tesla Model S" class="booking-car-image" id="booking-car-image">
    
            <!-- THIS BUTTON MUST HAVE id="pay-btn" -->
            <button type="button" class="btn-pay" id="pay-btn">
                <span class="pay-amount">Rs. 1000</span>
                <span class="pay-text">Pay Now</span>
            </button>
        </div>
    </div>
</main>

<script src="../assets/js/booking.js"></script>
</body>
</html>
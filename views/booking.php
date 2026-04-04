<?php 
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
include '../includes/header.php'; 
?>
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

            <!-- Collateral Upload -->
            <div class="form-group" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <label class="form-label">Requirement / Collateral</label>
                <select id="collateral-type" class="form-input" style="margin-bottom: 10px; background: #1e293b; color: #fff;" required>
                    <option value="" disabled selected>Select ID Type*</option>
                    <option value="Citizenship Card">Citizenship Card</option>
                    <option value="Passport">Passport</option>
                    <option value="Driving License">Driving License</option>
                </select>
                <label class="form-label" style="font-size: 12px; opacity: 0.8; margin-top: 10px;">Upload ID Photo*</label>
                <input type="file" id="collateral-image" class="form-input" accept="image/*" required style="padding-top: 8px;">
            </div>

            <!-- User Agreement -->
            <div class="form-group" style="background: rgba(30,41,59,0.5); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <label class="form-label">Rental User Agreement</label>
                <div style="height: 80px; overflow-y: auto; font-size: 12px; color: #94a3b8; margin-bottom: 10px; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 4px;">
                    1. The Renter agrees to return the vehicle in the same condition as received.<br>
                    2. The uploaded collateral serves as security and must be authentic.<br>
                    3. Any damages or traffic violations are the sole responsibility of the Renter.<br>
                    4. The platform reserves the right to reject any application based on collateral review.
                </div>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; cursor: pointer;">
                    <input type="checkbox" id="agreement-checkbox" required>
                    <span>I have read and agree to the User Agreement.*</span>
                </label>
            </div>

            <!-- Error Message -->
            <div class="error-message" id="error-message" style="display: none;">
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

<script>
    const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";
</script>
<script src="../assets/js/booking.js"></script>
</body>
</html>
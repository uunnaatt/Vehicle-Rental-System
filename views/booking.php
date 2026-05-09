<?php 
include '../includes/auth_guard.php';
include '../includes/header.php'; 
?>
<main class="booking-page">
    <div class="booking-container">
        <!-- Left Form -->
        <div class="booking-form">
            <h1 class="booking-title" id="booking-car-name">TESLA MODEL - S</h1>




            <!-- Personal Info -->
            <div class="form-group">
                <input type="text" class="form-input" placeholder="Full Name*" id="full-name" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-input" placeholder="Email Address*" id="email" required>
            </div>
            <div class="form-group">
                <input type="tel" class="form-input" placeholder="Contact*" id="contact" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" required>
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
                <input type="text" class="form-input" placeholder="Select pickup location" id="location">
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
            <div class="form-group collateral-section">
                <label class="form-label">Requirement / Collateral</label>
                <select id="collateral-type" class="form-input" required>
                    <option value="" disabled selected>Select ID Type*</option>
                    <option value="Citizenship Card">Citizenship Card</option>
                    <option value="Passport">Passport</option>
                    <option value="Driving License">Driving License</option>
                </select>
                <label class="form-label compact-label">Upload ID Photo*</label>
                <input type="file" id="collateral-image" class="form-input file-input" accept="image/*" required>
            </div>

            <!-- User Agreement -->
            <div class="form-group rental-agreement">
                <div class="agreement-heading">
                    <i class="fa-solid fa-file-signature"></i>
                    <div>
                        <label class="form-label">Rental User Agreement</label>
                        <p>Review these terms before submitting the rental request.</p>
                    </div>
                </div>
                <div class="agreement-list">
                    <div><span>1</span><p>The renter must return the vehicle on time and in the same condition as received.</p></div>
                    <div><span>2</span><p>The uploaded ID is held only for verification and collateral review.</p></div>
                    <div><span>3</span><p>Traffic fines, misuse, late return, or damage charges remain the renter's responsibility.</p></div>
                    <div><span>4</span><p>SAWARI may reject a booking if details or collateral cannot be verified.</p></div>
                </div>
                <label class="agreement-check">
                    <input type="checkbox" id="agreement-checkbox" required>
                    <span>I have read and agree to the User Agreement.*</span>
                </label>
            </div>

            <!-- Error Message -->
            <div class="error-message" id="error-message" style="display: none;">
                Please fill up the required information.
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

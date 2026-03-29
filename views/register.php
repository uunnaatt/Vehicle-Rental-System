<?php include '../includes/header.php'; ?>

<main class="auth-section">
    <div class="auth-container">
        <!-- Top Logo -->
        <div class="auth-logo">
            <img src="../assets/images/LOGO.png" alt="SAWARI" class="auth-logo-img">
        </div>

        <!-- Sign Up Form -->
        <div class="auth-form">
            <h1 class="auth-title">SIGN UP</h1>
            
            <form class="form" id="register-form">
                <input type="text" id="reg-phone-email" class="form-input" placeholder="Enter Your Phone/Email" required>
                <input type="text" id="reg-fullname" class="form-input" placeholder="FULL NAME" required>
                <input type="password" id="reg-password" class="form-input" placeholder="Enter your password" required>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password</a>
                </div>

                <button type="button" class="btn-auth" onclick="window.location.href='login.php'">LOGIN</button>
                <button type="submit" class="btn-auth btn-secondary">SIGNUP</button>
            </form>
            <div id="reg-message" style="color: red; margin-top: 10px; display: none; text-align: center;"></div>

            <!-- Divider -->
            <div class="divider">
                <span></span>
                <span class="divider-text">Or</span>
                <span></span>
            </div>

            <!-- Google Login -->
            <button class="btn-google">
                <img src="../assets/images/google-icon.png" alt="Google" class="google-icon">
                Continue with Google
            </button>

            <!-- Back Link -->
            <a href="index.php" class="back-link">Back</a>
        </div>
    </div>
</main>

<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phoneOrEmail = document.getElementById('reg-phone-email').value;
    const fullName = document.getElementById('reg-fullname').value;
    const password = document.getElementById('reg-password').value;
    const messageDiv = document.getElementById('reg-message');
    
    fetch('../api/auth/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            phone_or_email: phoneOrEmail,
            full_name: fullName,
            password: password
        })
    })
    .then(response => response.json().then(data => ({status: response.status, body: data})))
    .then(res => {
        if (res.status === 201) {
            // Success
            window.location.href = 'dashboard.php';
        } else {
            // Error
            messageDiv.style.display = 'block';
            messageDiv.innerText = res.body.message || 'Registration failed.';
        }
    })
    .catch(error => {
        messageDiv.style.display = 'block';
        messageDiv.innerText = 'An error occurred. Please try again.';
    });
});
</script>

</body>
</html>
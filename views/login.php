<?php include '../includes/header.php'; ?>

<main class="auth-section">
    <div class="auth-container">
        <!-- Top Logo -->
        <div class="auth-logo">
            <img src="../assets/images/LOGO.png" alt="SAWARI" class="auth-logo-img">
        </div>

        <!-- Login Form -->
        <div class="auth-form">
            <h1 class="auth-title">WELCOME BACK<br>READY TO HIT THE ROAD?</h1>
            
            <form class="form" id="login-form">
                <input type="text" id="log-phone-email" class="form-input" placeholder="Enter Phone/Email" required>
                <input type="password" id="log-password" class="form-input" placeholder="Enter your password" required>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password</a>
                </div>

                <button type="submit" class="btn-auth">LOGIN</button>
                <button type="button" class="btn-auth btn-secondary" onclick="window.location.href='register.php'">SIGNUP</button>
            </form>
            <div id="log-message" style="color: red; margin-top: 10px; display: none; text-align: center;"></div>

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
document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phoneOrEmail = document.getElementById('log-phone-email').value;
    const password = document.getElementById('log-password').value;
    const messageDiv = document.getElementById('log-message');
    
    fetch('../api/auth/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            phone_or_email: phoneOrEmail,
            password: password
        })
    })
    .then(response => response.json().then(data => ({status: response.status, body: data})))
    .then(res => {
        if (res.status === 200) {
            // Success
            window.location.href = 'dashboard.php';
        } else {
            // Error
            messageDiv.style.display = 'block';
            messageDiv.innerText = res.body.message || 'Login failed.';
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
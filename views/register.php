<?php include '../includes/header.php'; ?>

<style>.navbar { display: none; }</style>

<main class="auth-section">
    <div class="auth-container auth-container-register">
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

                <button type="submit" class="btn-auth">SIGNUP</button>
            </form>
            <div id="reg-message" class="auth-message"></div>

            <!-- Back Link -->
            <a href="index.php" class="back-link">Back</a>
        </div>
    </div>
</main>

<script>
// Dynamic Phone/Email input length check
document.getElementById('reg-phone-email')?.addEventListener('input', function() {
    if (/^\d+$/.test(this.value) && this.value.length > 10) {
        this.value = this.value.replace(/\D/g, '').slice(0, 10);
    }
});

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
            // Success – send to login
            messageDiv.className = 'auth-message success';
            messageDiv.innerText = 'Account created. Please log in.';
            setTimeout(() => window.location.href = 'login.php', 1500);
        } else {
            // Error
            messageDiv.className = 'auth-message error';
            messageDiv.innerText = res.body.message || 'Registration failed.';
        }
    })
    .catch(error => {
        messageDiv.className = 'auth-message error';
        messageDiv.innerText = 'An error occurred. Please try again.';
    });
});
</script>

</body>
</html>

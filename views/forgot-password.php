<?php include '../includes/header.php'; ?>

<style>.navbar { display: none; }</style>

<main class="auth-section">
    <div class="auth-container auth-container-login">
        <div class="auth-logo">
            <img src="../assets/images/LOGO.png" alt="SAWARI" class="auth-logo-img">
        </div>

        <div class="auth-form">
            <h1 class="auth-title">RESET PASSWORD</h1>
            <p class="auth-subtitle">Enter your account email to get a reset link.</p>

            <form class="form" id="forgot-form">
                <input type="email" id="forgot-email" class="form-input" placeholder="Enter your email" required>
                <button type="submit" class="btn-auth">SEND RESET LINK</button>
            </form>

            <div id="forgot-message" class="auth-message"></div>
            <div id="reset-link-box" class="reset-link-box" style="display:none;"></div>

            <a href="login.php" class="back-link">Back to login</a>
        </div>
    </div>
</main>

<script>
document.getElementById('forgot-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const messageDiv = document.getElementById('forgot-message');
    const linkBox = document.getElementById('reset-link-box');
    messageDiv.className = 'auth-message';
    messageDiv.textContent = 'Preparing reset link...';
    linkBox.style.display = 'none';
    linkBox.innerHTML = '';

    try {
        const response = await fetch('../api/auth/forgot_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone_or_email: document.getElementById('forgot-email').value })
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Unable to request reset link.');
        }

        messageDiv.className = 'auth-message success';
        messageDiv.textContent = data.message;

        if (data.reset_link) {
            linkBox.style.display = 'block';
            linkBox.innerHTML = `<span>Local reset link</span><a href="${data.reset_link}">${data.reset_link}</a>`;
        }
    } catch (error) {
        messageDiv.className = 'auth-message error';
        messageDiv.textContent = error.message;
    }
});
</script>

</body>
</html>

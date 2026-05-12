<?php include '../includes/header.php'; ?>

<style>.navbar { display: none; }</style>

<main class="auth-section">
    <div class="auth-container auth-container-login">
        <div class="auth-logo">
            <img src="../assets/images/LOGO.png" alt="SAWARI" class="auth-logo-img">
        </div>

        <div class="auth-form">
            <h1 class="auth-title">NEW PASSWORD</h1>
            <p class="auth-subtitle">Choose a password with at least 6 characters.</p>

            <form class="form" id="reset-form">
                <input type="password" id="reset-password" class="form-input" placeholder="New password" required minlength="6">
                <input type="password" id="reset-confirm" class="form-input" placeholder="Confirm new password" required minlength="6">
                <button type="submit" class="btn-auth">RESET PASSWORD</button>
            </form>

            <div id="reset-message" class="auth-message"></div>

            <a href="login.php" class="back-link">Back to login</a>
        </div>
    </div>
</main>

<script>
const resetToken = new URLSearchParams(window.location.search).get('token') || '';

document.getElementById('reset-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const password = document.getElementById('reset-password').value;
    const confirm = document.getElementById('reset-confirm').value;
    const messageDiv = document.getElementById('reset-message');

    if (password !== confirm) {
        messageDiv.className = 'auth-message error';
        messageDiv.textContent = 'Passwords do not match.';
        return;
    }

    try {
        const response = await fetch('../api/auth/reset_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: resetToken, password })
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Unable to reset password.');
        }

        messageDiv.className = 'auth-message success';
        messageDiv.textContent = data.message;
        setTimeout(() => window.location.href = 'login.php', 1400);
    } catch (error) {
        messageDiv.className = 'auth-message error';
        messageDiv.textContent = error.message;
    }
});
</script>

</body>
</html>

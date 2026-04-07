<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAWARI – Nepal's #1 Vehicle Rental</title>
    <meta name="description" content="SAWARI - Nepal's leading vehicle rental platform. Book SUVs, Sedans, Hatchbacks and Pickups across all major cities.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
    <div class="nav-container">
        <ul class="nav-menu">
            <li><a href="index.php" class="nav-link">HOME</a></li>
            <li><a href="dashboard.php" class="nav-link">BROWSE</a></li>
            <li class="dropdown">
                <a href="#" class="nav-link">CATEGORY ▾</a>
                <div class="dropdown-menu">
                    <a href="category.php?type=SUV" class="dropdown-item">🚙 SUV</a>
                    <a href="category.php?type=Sedan" class="dropdown-item">🚗 Sedan</a>
                    <a href="category.php?type=Hatchback" class="dropdown-item">🚘 Hatchback</a>
                    <a href="category.php?type=Pickup" class="dropdown-item">🛻 Pickup</a>
                </div>
            </li>
            <li><a href="about.php" class="nav-link">ABOUT US</a></li>
            <li><a href="contact.php" class="nav-link">CONTACT</a></li>
        </ul>
        
        <div class="nav-right">
            <div id="nav-auth-guest" style="display:flex; align-items:center; gap:10px;">
                <a href="login.php" class="btn-login">LOGIN / SIGNUP</a>
            </div>
            <div id="nav-auth-user" style="display:none; align-items:center; gap:15px;">
                <a href="profile.php" class="nav-link" id="nav-username" style="font-weight:700;">Profile</a>
                <button onclick="logoutUser()" class="btn-login" style="cursor:pointer; border:none;">LOGOUT</button>
            </div>
            <div id="nav-auth-admin" style="display:none; align-items:center; gap:15px;">
                <a href="admin-dashboard.php" class="nav-link" style="font-weight:700; color:#38bdf8;">ADMIN</a>
                <button onclick="logoutUser()" class="btn-login" style="cursor:pointer; border:none;">LOGOUT</button>
            </div>
            <div class="logo">
                <img src="../assets/images/LOGO.png" alt="SAWARI" class="logo-img">
            </div>
        </div>
    </div>
    </nav>
    <script>
    // Show correct nav state based on secure session
    (function() {
        fetch('../api/auth/me.php')
            .then(res => res.json())
            .then(data => {
                if (data.authenticated) {
                    if (data.role === 'admin') {
                        document.getElementById('nav-auth-guest').style.display = 'none';
                        document.getElementById('nav-auth-admin').style.display = 'flex';
                    } else {
                        document.getElementById('nav-auth-guest').style.display = 'none';
                        document.getElementById('nav-auth-user').style.display = 'flex';
                        document.getElementById('nav-username').textContent = data.full_name || 'Profile';
                    }
                }
            })
            .catch(() => {});
    })();

    async function logoutUser() {
        try {
            await fetch('../api/auth/logout.php');
            localStorage.clear();
            window.location.href = 'index.php';
        } catch (e) {
            window.location.href = 'index.php';
        }
    }
    </script>
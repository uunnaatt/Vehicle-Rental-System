<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include '../includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<main class="tracking-page">
    <div class="tracking-container">
        <div class="tracking-header">
            <h1>Track My Rental</h1>
            <p>Live location for your currently active booking.</p>
        </div>
        <div class="tracking-layout">
            <div class="tracking-map-wrap">
                <div id="user-tracking-map"></div>
            </div>
            <div class="tracking-info-wrap">
                <h3>Booking Details</h3>
                <div id="tracking-info">Loading active booking...</div>
            </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/tracking.js"></script>
</body>
</html>

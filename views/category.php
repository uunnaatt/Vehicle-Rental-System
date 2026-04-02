<?php include '../includes/header.php'; ?>

<main class="category-page">
    <div class="category-container">
        <!-- Header Section -->
        <div class="category-header">
            <div class="header-content">
                <h1 class="page-heading">WELCOME TO SAWARI</h1>
                <p class="page-subtitle">Nepal's No.1 rental platform</p>
            </div>
            
            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search your dream car...">
                <button class="search-btn">🔍</button>
            </div>
        </div>

        <!-- Category Title -->
        <div class="category-title-section">
            <h2 class="category-title" id="category-name">Best selling</h2>
        </div>

        <!-- Car Cards Grid -->
        <div class="car-grid" id="car-grid">
            <!-- Cars will be loaded here by JavaScript -->
        </div>
    </div>
</main>

<script src="../assets/js/category.js"></script>
</body>
</html>
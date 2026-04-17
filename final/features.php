<?php
// Features page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - Online Order System</title>
    <link rel="stylesheet" href="css/features.css">
</head>
<body>
    <header class="site-header">
        <div class="site-brand">Online Order System</div>
        <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span aria-hidden="true">☰</span>
        </button>
        <nav class="site-nav" id="site-nav">
            <a href="index.php" class="nav-link">Home</a>
            <a href="features.php" class="nav-link active">Features</a>
            <a href="order.php" class="nav-link">Order</a>
            <a href="invoice.php" class="nav-link">Invoice</a>
        </nav>
    </header>

    <main class="page-container">
        <section class="hero-panel">
            <p class="eyebrow">Project Features</p>
            <h1>Explore the features of the order and billing system.</h1>
            <p class="hero-copy">Each tab has a dedicated page and a polished card-based design that matches the screenshot aesthetic.</p>
        </section>

        <section class="features-grid">
            <article class="feature-card">
                <span class="feature-icon">🧩</span>
                <h3>Modular PHP Pages</h3>
                <p>Separate pages for Home, Features, Order, and Invoice keep the app organized and easy to maintain.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon">📱</span>
                <h3>Responsive UI</h3>
                <p>Design adapts across desktop and mobile while preserving the screenshot's neat card layout.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon">⚙️</span>
                <h3>Order & Billing Logic</h3>
                <p>Product selection, quantity calculation, and discount handling work together to generate a final total.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon">📄</span>
                <h3>Clean Summary Cards</h3>
                <p>Invoices and order summaries are displayed using clean cards and structured spacing.</p>
            </article>
        </section>
    </main>

    <footer class="site-footer">
    <div class="footer-container">

        <div class="footer-branding">
            <div class="footer-logo">Online Order System</div>
            <p class="footer-copy">
                A complete web development practice site that blends PHP, HTML, and CSS for professional order and billing workflows.
            </p>
        </div>

        <div class="footer-links">
            <div>
                <h4>Quick Links</h4>
                <a href="index.php">Home</a>
                <a href="features.php">Features</a>
                <a href="order.php">Order</a>
                <a href="invoice.php">Invoice</a>
            </div>

            <div>
                <h4>Support</h4>
                <a href="#">Docs</a>
                <a href="#">Contact</a>
                <a href="#">Privacy</a>
            </div>
        </div>

        <div class="footer-note">
            Designed for modern product ordering with clean UI and responsive layout.
        </div>

    </div>

    <div class="footer-bottom">
        © 2026 Online Order System. Crafted with care for web development practice.
    </div>
</footer>
<script src="js/site.js" defer></script>
</body>
</html>

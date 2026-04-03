<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';

$pageTitle = 'Home';
$pageStyles = ['css/pages/home.css'];

include 'includes/header.php';
include 'includes/user_prefs_popup.php';
?>
<section class="page-hero hero-dot-grid home-hero reveal-on-scroll" aria-label="Home hero">
    <div class="hero-orbs">
        <span class="hero-orb hero-orb--indigo" aria-hidden="true"></span>
        <span class="hero-orb hero-orb--emerald" aria-hidden="true"></span>
    </div>
    <div class="hero-line" aria-hidden="true"></div>
    <div class="container hero-content">
        <h1>
            <span class="hero-title-line">Discover the Degree</span>
            <span class="hero-highlight hero-title-line">Built for Your Z-Score</span>
        </h1>
        <p class="page-hero-meta">
            300+ programs across 25+ Sri Lankan universities — matched instantly to your ambitions and A/L results.
        </p>
        <div class="hero-stats-grid">
            <article class="stat-card">
                <div class="stat-count" data-target-number="17" data-suffix="+">0</div>
                <p class="stat-label">Universities</p>
            </article>
            <article class="stat-card">
                <div class="stat-count" data-target-number="150" data-suffix="+">0</div>
                <p class="stat-label">Degree programs</p>
            </article>
            <article class="stat-card">
                <div class="stat-count" data-target-number="15000" data-suffix="+">0</div>
                <p class="stat-label">Students guided</p>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/user_prefs_reports.php'; ?>

<section class="section-shell bento-section" aria-label="Bento grid">
    <div class="container">
        <div class="bento-section-header">
            <h2>Your complete guide to university life in Sri Lanka</h2>
            <p class="section-subtitle">A polished, modern experience. Browse curated university paths and get instant Z-score clarity, completely hassle free.</p>
        </div>
        <div class="hero-flow-slider" aria-label="Highlights slider">
            <article class="flow-card is-browse" aria-label="Browse Universities card">
                <h3>Browse Universities</h3>
                <p>Navigate tiered campuses, heritage rituals, and future-ready programs with guided stories.</p>
                <a class="btn btn-primary" href="universities.php">View Universities</a>
            </article>
            <article class="flow-card is-gallery" aria-label="View Gallery card">
                <h3>View Gallery</h3>
                <p>Explore campus stories through labs, green spaces, and student life moments.</p>
                <a class="btn btn-primary" href="gallery.php">Open Gallery</a>
            </article>
            <article class="flow-card is-finder" aria-label="Z-Score Finder card">
                <h3>Z-Score Finder</h3>
                <p>Enter your stream and score to instantly discover degrees matched to your results.</p>
                <a class="btn btn-primary" href="finder.php">Launch Finder</a>
            </article>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>



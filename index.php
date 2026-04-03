<?php
require_once 'includes/db.php';

$pageTitle = 'Home';
$pageStyles = ['css/pages/home.css', 'css/pages/reports.css'];

// --- STATS LOGIC ---
// Helper to get highest preferred stream
function getHighestStream($conn, $gender) {
    $sql = "SELECT stream, COUNT(*) as count FROM student_preferences WHERE gender = ? GROUP BY stream ORDER BY count DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $gender);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['stream'];
    }
    return 'N/A';
}

// Helper to get top 5 highly demanded degrees
function getTopDegrees($conn, $genderCond = "") {
    $where = "";
    if ($genderCond === 'Male' || $genderCond === 'Female') {
        $safeGender = $conn->real_escape_string($genderCond);
        $where = "WHERE gender = '$safeGender'";
    }

    // Get total votes
    $totalSql = "SELECT COUNT(*) as total FROM student_preferences $where";
    $totalRes = $conn->query($totalSql);
    $totalVotes = 0;
    if ($totalRes && $row = $totalRes->fetch_assoc()) {
        $totalVotes = $row['total'];
    }
    
    if ($totalVotes == 0) return [];
    
    $sql = "SELECT preferred_degree, COUNT(*) as count, (COUNT(*) * 100.0 / $totalVotes) as percentage 
            FROM student_preferences 
            $where 
            GROUP BY preferred_degree 
            ORDER BY count DESC 
            LIMIT 5";
            
    $res = $conn->query($sql);
    
    $topDegrees = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $topDegrees[] = $row;
        }
    }
    return $topDegrees;
}

$highest_stream_girls = getHighestStream($conn, 'Female');
$highest_stream_boys = getHighestStream($conn, 'Male');

$top_degrees_overall = getTopDegrees($conn);
$top_degrees_boys = getTopDegrees($conn, 'Male');
$top_degrees_girls = getTopDegrees($conn, 'Female');
// -------------------

include 'includes/header.php';
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
<div class="page-transition" aria-hidden="true"></div>
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

<!-- STUDENT REPORTS SECTION -->
<section class="section-shell" aria-label="Student Preferences Report" style="padding-top: 4rem; padding-bottom: 4rem; background: var(--surface-light);">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Student Preference Insights</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-6); margin-bottom: 3rem;">
            <div class="flow-card" style="background: var(--surface-dark); color: var(--text-on-dark-primary); padding: 1.5rem; border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: 0.5rem; font-size: var(--text-lg); color: var(--text-on-dark-secondary);">Highest Preferred Stream (Girls)</h3>
                <p style="font-size: var(--text-2xl); font-weight: bold; color: var(--emerald-400);"><?php echo htmlspecialchars($highest_stream_girls); ?></p>
            </div>
            
            <div class="flow-card" style="background: var(--surface-dark); color: var(--text-on-dark-primary); padding: 1.5rem; border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: 0.5rem; font-size: var(--text-lg); color: var(--text-on-dark-secondary);">Highest Preferred Stream (Boys)</h3>
                <p style="font-size: var(--text-2xl); font-weight: bold; color: var(--accent-400);"><?php echo htmlspecialchars($highest_stream_boys); ?></p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-8);">
            
            <!-- Overall Top Degrees -->
            <div>
                <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--light-300); padding-bottom: 0.5rem;">Highest Demanding Degrees (Overall)</h3>
                <?php if (empty($top_degrees_overall)): ?>
                    <p style="color: var(--text-on-light-secondary);">No data available yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($top_degrees_overall as $index => $deg): ?>
                            <li style="margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 600;"><?php echo ($index + 1) . '. ' . htmlspecialchars($deg['preferred_degree']); ?></span>
                                    <span><?php echo number_format($deg['percentage'], 1); ?>%</span>
                                </div>
                                <div style="width: 100%; background: var(--light-200); height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?php echo $deg['percentage']; ?>%; background: var(--emerald-500); height: 100%;"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Top Degrees Boys -->
            <div>
                <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--light-300); padding-bottom: 0.5rem;">Highest Demanding Degrees (Boys)</h3>
                <?php if (empty($top_degrees_boys)): ?>
                    <p style="color: var(--text-on-light-secondary);">No data available yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($top_degrees_boys as $index => $deg): ?>
                            <li style="margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 600;"><?php echo ($index + 1) . '. ' . htmlspecialchars($deg['preferred_degree']); ?></span>
                                    <span><?php echo number_format($deg['percentage'], 1); ?>%</span>
                                </div>
                                <div style="width: 100%; background: var(--light-200); height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?php echo $deg['percentage']; ?>%; background: var(--accent-500); height: 100%;"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Top Degrees Girls -->
            <div>
                <h3 style="margin-bottom: 1rem; border-bottom: 2px solid var(--light-300); padding-bottom: 0.5rem;">Highest Demanding Degrees (Girls)</h3>
                <?php if (empty($top_degrees_girls)): ?>
                    <p style="color: var(--text-on-light-secondary);">No data available yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($top_degrees_girls as $index => $deg): ?>
                            <li style="margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-weight: 600;"><?php echo ($index + 1) . '. ' . htmlspecialchars($deg['preferred_degree']); ?></span>
                                    <span><?php echo number_format($deg['percentage'], 1); ?>%</span>
                                </div>
                                <div style="width: 100%; background: var(--light-200); height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?php echo $deg['percentage']; ?>%; background: var(--emerald-400); height: 100%;"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/preference_popup.php'; ?>

<?php
require_once 'includes/db.php';
require_once 'includes/require_user_details.php';
require_once 'includes/ui-helpers.php';

$pageTitle = 'Universities';
$pageStyles = ['css/pages/universities.css'];

$searchTerm = trim($_GET['search'] ?? '');
$universities = [];
$whereClause = ' WHERE name IS NOT NULL AND TRIM(name) <> ? AND name <> ?';
$params = [];
$paramTypes = 'ss';

$placeholderNames = ['Unknown University', 'University of Trincomalee'];
$params[] = $placeholderNames[0];
$params[] = $placeholderNames[1];

if ($searchTerm !== '') {
    $whereClause .= ' AND (name LIKE ? OR location LIKE ? OR description LIKE ?)';
    $like = "%{$searchTerm}%";
    $params = array_merge($params, [$like, $like, $like]);
    $paramTypes .= 'sss';
}

$query = 'SELECT * FROM universities' . $whereClause . ' ORDER BY id DESC';
$stmt = $conn->prepare($query);
if ($stmt) {
    if ($params) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $universities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

foreach ($universities as &$university) {
    $uniName = $university['name'];

    $streamStmt = $conn->prepare('SELECT DISTINCT stream FROM university_degrees WHERE university = ?');
    $streams = [];
    if ($streamStmt) {
        $streamStmt->bind_param('s', $uniName);
        $streamStmt->execute();
        $streamResult = $streamStmt->get_result();
        while ($row = $streamResult->fetch_assoc()) {
            if (!empty($row['stream']) && $row['stream'] !== 'Other Programs') {
                $streams[] = $row['stream'];
            }
        }
        $streamStmt->close();
    }
    $university['stream_list'] = $streams ? implode(', ', $streams) : 'All';

    $degreeCountStmt = $conn->prepare('SELECT COUNT(*) AS total FROM university_degrees WHERE university = ?');
    $degreeCount = 0;
    if ($degreeCountStmt) {
        $degreeCountStmt->bind_param('s', $uniName);
        $degreeCountStmt->execute();
        $degreeCountResult = $degreeCountStmt->get_result();
        $degreeCount = (int) ($degreeCountResult->fetch_assoc()['total'] ?? 0);
        $degreeCountStmt->close();
    }
    $university['degree_count'] = $degreeCount;
}
unset($university);
$rankedIds = array_map(function ($item) {
    return $item['id'];
}, array_slice($universities, 0, 2));

include 'includes/header.php';
?>
<style>
    .page-hero {
        background-color: #0A0E1A !important;
    }
</style>
<section class="page-hero reveal-on-scroll" aria-label="Universities hero">
    <div class="container">
        <p class="eyebrow">Universities</p>
        <h1>Universities in Sri Lanka</h1>
        <p class="page-hero-meta">Universities in Sri Lanka provide competitive, high-quality education that shapes skilled professionals and drives national development.
</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span>Universities</span>
        </div>
    </div>
</section>

<section class="section-shell" aria-label="Search universities">
    <div class="container">
        <form class="search-panel reveal-on-scroll" method="GET" action="universities.php">
            <div class="search-input-wrapper">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    type="search"
                    id="uniSearchInput"
                    name="search"
                    class="search-input"
                    placeholder="Search universities (e.g. 'Colombo', 'Peradeniya')..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
        </form>
        <p class="results-summary reveal-on-scroll">
            Showing <?php echo count($universities); ?> universities<?php echo $searchTerm !== '' ? ' for "' . htmlspecialchars($searchTerm) . '"' : ''; ?>.
            <?php if ($searchTerm !== ''): ?>
                <a href="universities.php">Show all</a>
            <?php endif; ?>
        </p>
        <?php if ($universities): ?>
            <div class="university-grid">
                <?php foreach ($universities as $university): ?>
                    <?php $image = getUniversityImagePath($university); ?>
                    <article class="university-card reveal-on-scroll" data-streams="<?php echo htmlspecialchars($university['stream_list']); ?>">
                        <div class="university-image">
                            <?php if ($image): ?>
                                <img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?php echo htmlspecialchars($university['name']); ?>">
                            <?php else: ?>
                                <div class="university-image university-image--placeholder"></div>
                            <?php endif; ?>
                            <div class="image-overlay">
                                <div class="location-tag"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo htmlspecialchars(getUniversityLocation($university)); ?></div>
                                <?php if (!empty($university['type'])): ?>
                                    <span class="type-badge type-<?php echo strtolower($university['type']); ?>"><?php echo htmlspecialchars($university['type']); ?></span>
                                <?php else: ?>
                                    <span class="type-badge type-government">Government</span>
                                <?php endif; ?>
                                <div class="university-name"><?php echo htmlspecialchars($university['name']); ?></div>
                            </div>
                        </div>
                        <div class="university-content">
                            <p><?php echo htmlspecialchars(getUniversityDescription($university)); ?></p>
                            <div class="pills">
                                <span class="pill degree-pill"><?php echo $university['degree_count']; ?> Degrees</span>
                            <?php if (!empty($university['stream_list']) && $university['stream_list'] !== 'All'): ?>
                                <?php foreach (explode(', ', $university['stream_list']) as $stream): ?>
                                    <span class="pill"><?php echo htmlspecialchars($stream); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </div>
                            <div class="program-cta">
                                <a class="btn btn-primary" href="university.php?id=<?php echo $university['id']; ?>">View Programs →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-results">No universities match that search yet — try another keyword.</p>
        <?php endif; ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>

<?php
require_once "includes/db.php";

$totO_res = $conn->query("SELECT COUNT(*) as c FROM user_preferences");
$totO = ($r = $totO_res->fetch_assoc()) && $r['c'] > 0 ? $r['c'] : 1;

$totB_res = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE gender='Male'");
$totB = ($r = $totB_res->fetch_assoc()) && $r['c'] > 0 ? $r['c'] : 1;

$totG_res = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE gender='Female'");
$totG = ($r = $totG_res->fetch_assoc()) && $r['c'] > 0 ? $r['c'] : 1;

// 1. Stream Preferences
$streamLabels = [];
$streamBoysData = [];
$streamGirlsData = [];

$streamRes = $conn->query("SELECT DISTINCT stream FROM user_preferences WHERE stream IS NOT NULL AND stream != ''");
$allStreams = [];
if ($streamRes) {
    while ($r = $streamRes->fetch_assoc()) {
        $allStreams[] = $r['stream'];
    }
}
if (empty($allStreams)) {
    $allStreams = ['Physical Science', 'Biological Science', 'Commerce', 'Arts', 'Engineering Technology', 'Biosystems Technology'];
}

foreach ($allStreams as $s) {
    $streamLabels[] = $s;
    
    $sb = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE stream='" . $conn->real_escape_string($s) . "' AND gender='Male'")->fetch_assoc()['c'];
    $streamBoysData[] = round(($sb / $totB) * 100, 1);
    
    $sg = $conn->query("SELECT COUNT(*) as c FROM user_preferences WHERE stream='" . $conn->real_escape_string($s) . "' AND gender='Female'")->fetch_assoc()['c'];
    $streamGirlsData[] = round(($sg / $totG) * 100, 1);
}

// Function to get top degrees with university
function getTopDegrees($conn, $gender, $totalCount) {
    $labels = [];
    $data = [];
    $where = $gender ? "WHERE gender='" . $conn->real_escape_string($gender) . "'" : "";
    $q = "SELECT degree, university, COUNT(*) as c FROM user_preferences $where GROUP BY degree, university ORDER BY c DESC LIMIT 5";
    $res = $conn->query($q);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $shortUni = strlen($r['university']) > 25 ? substr($r['university'], 0, 25) . "..." : $r['university'];
            // Append short university name to the label
            $labels[] = $r['degree'] . " (" . $shortUni . ")";
            $data[] = round(($r['c'] / $totalCount) * 100, 1);
        }
    }
    return ['labels' => $labels, 'data' => $data];
}

$degBoys = getTopDegrees($conn, 'Male', $totB);
$degGirls = getTopDegrees($conn, 'Female', $totG);
$degOverall = getTopDegrees($conn, null, $totO);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<section class="section-shell" style="padding-top: 3rem; background: var(--surface-light); padding-bottom: 5rem;">
    <div class="container">
        <h2 style="text-align:center; margin-bottom: 3rem; font-size: 2.2rem; color: var(--dark-800);">Student Preference Analytics</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 2rem;">
            
            <!-- Stream Preferences (Both Boys and Girls) -->
            <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: var(--primary-600); margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-chart-line"></i> Stream Preferences</h3>
                <p style="font-size: 0.95rem; color: var(--dark-500); margin-bottom: 1.5rem;">Comparison between boys and girls across all collected preferences.</p>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="streamChart"></canvas>
                </div>
                <?php if(empty(array_filter($streamBoysData)) && empty(array_filter($streamGirlsData))) echo '<p style="text-align:center; margin-top: -150px; color: var(--dark-400);">Not enough data yet.</p>'; ?>
            </div>
            
            <!-- Highest Demanded Degrees - Boys -->
            <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: var(--primary-600); margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-male"></i> Highest Demand: Boys</h3>
                <p style="font-size: 0.95rem; color: var(--dark-500); margin-bottom: 1.5rem;">Top 5 degrees demanded specifically by male users (includes the preferred University).</p>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="boysChart"></canvas>
                </div>
                <?php if(empty($degBoys['labels'])) echo '<p style="text-align:center; margin-top: -150px; color: var(--dark-400);">Not enough data yet.</p>'; ?>
            </div>

            <!-- Highest Demanded Degrees - Girls -->
            <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: var(--primary-600); margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-female"></i> Highest Demand: Girls</h3>
                <p style="font-size: 0.95rem; color: var(--dark-500); margin-bottom: 1.5rem;">Top 5 degrees demanded specifically by female users (includes the preferred University).</p>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="girlsChart"></canvas>
                </div>
                <?php if(empty($degGirls['labels'])) echo '<p style="text-align:center; margin-top: -150px; color: var(--dark-400);">Not enough data yet.</p>'; ?>
            </div>

            <!-- Highest Demanded Degrees - Overall -->
            <div style="background: white; padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md); border: 1px solid var(--light-300);">
                <h3 style="color: var(--primary-600); margin-bottom: 0.5rem; font-size: 1.5rem;"><i class="fas fa-globe"></i> Highest Demand: Overall</h3>
                <p style="font-size: 0.95rem; color: var(--dark-500); margin-bottom: 1.5rem;">The top 5 overall demanded degrees combined (includes the preferred University).</p>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="overallChart"></canvas>
                </div>
                <?php if(empty($degOverall['labels'])) echo '<p style="text-align:center; margin-top: -150px; color: var(--dark-400);">Not enough data yet.</p>'; ?>
            </div>
            
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function createBarChart(ctxId, labels, datasetsData) {
        var canvas = document.getElementById(ctxId);
        if(!canvas) return;
        var ctx = canvas.getContext('2d');
        if(labels.length === 0) return; // Prevent rendering empty charts
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasetsData
            },
            options: {
                indexAxis: 'y', // Makes it horizontal
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: false, // hide completely so there's no bottom axis
                        max: 100,
                        beginAtZero: true
                    },
                    y: {
                        grid: {
                            display: false, // hide grid lines behind bars
                            drawBorder: false // hide axis line
                        },
                        ticks: {
                            callback: function(value, index, values) { 
                                // Shorten y-axis labels if they are extremely long
                                let lbl = labels[index];
                                return lbl.length > 40 ? lbl.substring(0, 40) + '...' : lbl;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: datasetsData.length > 1 // Only show legend if multiple datasets (streams)
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) { return labels[context[0].dataIndex]; },
                            label: function(context) { return context.dataset.label + ': ' + context.parsed.x + '%'; }
                        }
                    }
                }
            }
        });
    }

    // Stream Analytics Bar Chart
    createBarChart('streamChart', <?php echo json_encode($streamLabels); ?>, [
        {
            label: 'Boys',
            data: <?php echo json_encode($streamBoysData); ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.85)', // Blue
            maxBarThickness: 12,
            borderRadius: 4
        },
        {
            label: 'Girls',
            data: <?php echo json_encode($streamGirlsData); ?>,
            backgroundColor: 'rgba(236, 72, 153, 0.85)', // Pink
            maxBarThickness: 12,
            borderRadius: 4
        }
    ]);

    // Boys Demanded Degrees Bar Chart
    createBarChart('boysChart', <?php echo json_encode($degBoys['labels']); ?>, [{
        label: 'Demand',
        data: <?php echo json_encode($degBoys['data']); ?>,
        backgroundColor: 'rgba(59, 130, 246, 0.85)', // Blue
        maxBarThickness: 12,
        borderRadius: 4
    }]);

    // Girls Demanded Degrees Bar Chart
    createBarChart('girlsChart', <?php echo json_encode($degGirls['labels']); ?>, [{
        label: 'Demand',
        data: <?php echo json_encode($degGirls['data']); ?>,
        backgroundColor: 'rgba(236, 72, 153, 0.85)', // Pink
        maxBarThickness: 12,
        borderRadius: 4
    }]);

    // Overall Demanded Degrees Bar Chart
    createBarChart('overallChart', <?php echo json_encode($degOverall['labels']); ?>, [{
        label: 'Demand',
        data: <?php echo json_encode($degOverall['data']); ?>,
        backgroundColor: 'rgba(16, 185, 129, 0.85)', // Green
        maxBarThickness: 12,
        borderRadius: 4
    }]);
});
</script>
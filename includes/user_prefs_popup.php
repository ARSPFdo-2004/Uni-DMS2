<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_prefs_set'])) {
    return; // Already set, don't show popup
}

$unis = [];
$res = $conn->query("SELECT DISTINCT university_name FROM flat_zscores ORDER BY university_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $unis[] = $row["university_name"];
    }
}
?>

<style>
/* Lightweight Popup CSS */
.pref-popup-overlay {
    position: fixed;
    top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(255, 255, 255, 0.85); /* Light translucent overlay, not dark */
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 1;
    transition: opacity 0.3s ease;
}
.pref-popup-box {
    background: white;
    padding: 2rem;
    border-radius: var(--radius-lg, 12px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid var(--light-300, #eee);
}
.pref-popup-box h2 {
    margin-top: 0;
    font-size: 1.5rem;
    color: var(--dark-800, #333);
}
/* Form styling */
.pref-form-group {
    margin-bottom: 1rem;
}
.pref-form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--dark-700, #444);
}
.pref-form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--light-400, #ccc);
    border-radius: var(--radius-sm, 6px);
}
</style>

<div id="prefPopupOverlay" class="pref-popup-overlay">
    <div class="pref-popup-box">
        <h2>Welcome to Uni-DMS</h2>
        <p style="color: #666; margin-bottom: 1.5rem; font-size: 0.95rem;">Please enter your email to continue.</p>
        
        <!-- Step 1: Email Check -->
        <form id="prefEmailForm" onsubmit="checkPrefEmail(event)">
            <div class="pref-form-group">
                <label for="prefEmail">Email Address</label>
                <input type="email" id="prefEmail" class="pref-form-control" required />
            </div>
            <button type="submit" id="prefEmailBtn" class="btn btn-primary" style="width: 100%;">Continue</button>
        </form>

        <!-- Step 2: Full Details (Hidden initially) -->
        <form id="prefDetailsForm" style="display: none;" onsubmit="submitPrefDetails(event)">
            <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">It looks like you're new! Please tell us a bit about your preferences.</p>
            
            <div class="pref-form-group">
                <label for="prefName">Name</label>
                <input type="text" id="prefName" class="pref-form-control" required />
            </div>
            
            <div class="pref-form-group">
                <label>Gender</label>
                <div>
                    <label style="display: inline-block; margin-right: 1rem; font-weight: normal;"><input type="radio" name="prefGender" value="Male" required> Male</label>
                    <label style="display: inline-block; font-weight: normal;"><input type="radio" name="prefGender" value="Female"> Female</label>
                </div>
            </div>

            <div class="pref-form-group">
                <label for="prefStream">Select your stream preference according to your career dream</label>
                <select id="prefStream" class="pref-form-control" required>
                    <option value="">-- Select Stream --</option>
                    <option value="Physical Science">Physical Science</option>
                    <option value="Biological Science">Biological Science</option>
                    <option value="Commerce">Commerce</option>
                    <option value="Arts">Arts</option>
                    <option value="Engineering Technology">Engineering Technology</option>
                    <option value="Biosystems Technology">Biosystems Technology</option>
                </select>
            </div>

            <div class="pref-form-group">
                <label for="prefUni">Preferred University</label>
                <select id="prefUni" class="pref-form-control" onchange="loadPrefDegrees()" required>
                    <option value="">-- Select University --</option>
                    <?php foreach($unis as $u): ?>
                        <option value="<?php echo htmlspecialchars($u); ?>"><?php echo htmlspecialchars($u); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pref-form-group">
                <label for="prefDegree">Preferred Degree</label>
                <input type="text" id="prefDegree" list="prefDegreeList" class="pref-form-control" placeholder="Type to search degrees..." required autocomplete="off" />
                <datalist id="prefDegreeList"></datalist>
            </div>

            <button type="submit" id="prefDetailsBtn" class="btn btn-primary" style="width: 100%;">Submit & Continue</button>
        </form>
    </div>
</div>

<script>
async function checkPrefEmail(e) {
    e.preventDefault();
    const email = document.getElementById("prefEmail").value;
    const btn = document.getElementById("prefEmailBtn");
    
    btn.disabled = true;
    btn.innerText = "Checking...";
    
    const fd = new FormData();
    fd.append("email", email);
    
    try {
        const res = await fetch("api_user_prefs.php", {method:"POST", body:fd});
        const data = await res.json();
        
        if (data.success && data.found) {
            // Returning user, grant access
            closePrefPopup();
        } else {
            // New user, show step 2
            document.getElementById("prefEmailForm").style.display = "none";
            document.getElementById("prefDetailsForm").style.display = "block";
        }
    } catch(err) {
        alert("Network error. Please try again.");
    } finally {
        btn.disabled = false;
        btn.innerText = "Continue";
    }
}

async function loadPrefDegrees() {
    const uni = document.getElementById("prefUni").value;
    const list = document.getElementById("prefDegreeList");
    const input = document.getElementById("prefDegree");
    list.innerHTML = "";
    input.value = "";
    
    if(!uni) return;
    
    try {
        const res = await fetch("api_user_prefs.php?action=get_degrees&university="+encodeURIComponent(uni));
        const degrees = await res.json();
        degrees.forEach(d => {
            const opt = document.createElement("option");
            opt.value = d;
            list.appendChild(opt);
        });
    } catch(err) {
        console.error(err);
    }
}

async function submitPrefDetails(e) {
    e.preventDefault();
    const email = document.getElementById("prefEmail").value;
    const name = document.getElementById("prefName").value;
    const genderEl = document.querySelector("input[name=prefGender]:checked");
    const gender = genderEl ? genderEl.value : "";
    const stream = document.getElementById("prefStream").value;
    const uni = document.getElementById("prefUni").value;
    const degree = document.getElementById("prefDegree").value;
    
    const btn = document.getElementById("prefDetailsBtn");
    btn.disabled = true;
    btn.innerText = "Saving...";
    
    const fd = new FormData();
    fd.append("email", email);
    fd.append("name", name);
    fd.append("gender", gender);
    fd.append("stream", stream);
    fd.append("university", uni);
    fd.append("degree", degree);
    
    try {
        const res = await fetch("api_user_prefs.php", {method:"POST", body:fd});
        const data = await res.json();
        
        if (data.success) {
            closePrefPopup();
        } else {
            alert("Error: " + (data.error || "Unknown"));
        }
    } catch(err) {
        alert("Network error.");
    } finally {
        btn.disabled = false;
        btn.innerText = "Submit & Continue";
    }
}

function closePrefPopup() {
    const overlay = document.getElementById("prefPopupOverlay");
    overlay.style.opacity = "0";
    setTimeout(() => {
        overlay.style.display = "none";
        // Reload page if there's a require_prefs flag to process logic or update reports
        const urlParams = new URLSearchParams(window.location.search);
        if(urlParams.get('require_prefs')) {
            window.location.href = 'index.php'; // remove flag
        } else {
            window.location.reload(); // to refresh charts
        }
    }, 300);
}
</script>
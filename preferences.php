<?php
require_once "includes/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$redirect_url = empty($_SESSION['redirect_after_details']) ? 'index.php' : $_SESSION['redirect_after_details'];

if (isset($_SESSION["user_details_set"]) && $_SESSION["user_details_set"]) { 
    header("Location: " . $redirect_url); 
    exit; 
}

$pageTitle = "Student Preferences";
$pageStyles = ["css/pages/login.css"];
include "includes/header.php";

$unis = [];
$res = $conn->query("SELECT DISTINCT university_name FROM flat_zscores ORDER BY university_name ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $unis[] = $row["university_name"];
    }
}
?>

<section class="page-hero reveal-on-scroll">
    <div class="container text-center">
        <p class="eyebrow">Onboarding</p>
        <h1>Customize Your Experience</h1>
        <p class="page-hero-meta">Are you already a user? Just enter your email. If not, please fill out your details below.</p>
    </div>
</section>

<section class="section-shell">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-8); align-items: start;">
            
            <!-- Section 1: Existing Users -->
            <div class="login-card reveal-on-scroll" style="background: var(--surface-light); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md);">
                <h2 style="font-size: var(--text-2xl); font-weight: bold; margin-bottom: 1rem;">Existing User</h2>
                <p style="color: var(--text-on-light-secondary); margin-bottom: 1.5rem;">Enter your email to skip re-entering your preferences and continue straight to the system.</p>
                <form onsubmit="checkEmail(event)">
                    <div style="margin-bottom: 1rem;">
                        <label for="pref-email-only" style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Email Address</label>
                        <input type="email" id="pref-email-only" class="form-input" style="width: 100%; padding: 0.75rem; border: 1px solid var(--light-300); border-radius: var(--radius-sm);" required />
                    </div>
                    <button type="submit" id="btn-email" class="btn btn-secondary" style="width: 100%;">Continue with Email</button>
                </form>
            </div>

            <!-- Section 2: New Users -->
            <div class="login-card reveal-on-scroll" style="background: var(--surface-light); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-light-md);">
                <h2 style="font-size: var(--text-2xl); font-weight: bold; margin-bottom: 1rem;">New User</h2>
                <p style="color: var(--text-on-light-secondary); margin-bottom: 1.5rem;">Fill out your details to help us personalize your university guide.</p>
                <form onsubmit="submitDetails(event)">
                    <div style="margin-bottom: 1rem;">
                        <label for="pref-email-new" style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Email Address</label>
                        <input type="email" id="pref-email-new" class="form-input" style="width: 100%; padding: 0.75rem; border: 1px solid var(--light-300); border-radius: var(--radius-sm);" required />
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="pref-name" style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Name</label>
                        <input type="text" id="pref-name" class="form-input" style="width: 100%; padding: 0.75rem; border: 1px solid var(--light-300); border-radius: var(--radius-sm);" required />
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Gender</label>
                        <label style="display: inline-flex; align-items: center; margin-right: 1.5rem; font-weight: normal;">
                            <input type="radio" name="pref-gender" value="Male" style="margin-right: 0.5rem;" required> Male
                        </label>
                        <label style="display: inline-flex; align-items: center; font-weight: normal;">
                            <input type="radio" name="pref-gender" value="Female" style="margin-right: 0.5rem;"> Female
                        </label>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="pref-stream" style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Select your stream preference according to your carrier dream</label>
                        <select id="pref-stream" class="form-input" style="width: 100%; padding: 0.75rem; border: 1px solid var(--light-300); border-radius: var(--radius-sm);" required>
                            <option value="">-- Select Stream --</option>
                            <option value="Physical Science">Physical Science</option>
                            <option value="Biological Science">Biological Science</option>
                            <option value="Commerce">Commerce</option>
                            <option value="Arts">Arts</option>
                            <option value="Engineering Technology">Engineering Technology</option>
                            <option value="Biosystems Technology">Biosystems Technology</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="pref-uni" style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Select preferred university</label>
                        <select id="pref-uni" class="form-input" onchange="loadDegrees()" style="width: 100%; padding: 0.75rem; border: 1px solid var(--light-300); border-radius: var(--radius-sm);" required>
                            <option value="">-- Select University --</option>
                            <?php foreach($unis as $u): ?>
                                <option value="<?php echo htmlspecialchars($u); ?>"><?php echo htmlspecialchars($u); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <label for="pref-degree" style="display: block; font-weight: bold; margin-bottom: 0.5rem; color: var(--text-on-light-primary);">Select your preferred degree</label>
                        <input type="text" id="pref-degree" list="degree-list" class="form-input" placeholder="Type to search degrees..." style="width: 100%; padding: 0.75rem; border: 1px solid var(--light-300); border-radius: var(--radius-sm);" required />
                        <datalist id="degree-list"></datalist>
                    </div>

                    <button type="submit" id="btn-submit" class="btn" style="width: 100%; background: #6366F1; border-color: #6366F1; color: white;">Submit & Continue</button>
                </form>
            </div>

        </div>
    </div>
</section>

<script>
const targetUrl = <?php echo json_encode($redirect_url); ?>;

async function checkEmail(e) {
    e.preventDefault();
    const em = document.getElementById("pref-email-only").value;
    if(!em) return;
    
    const btn = document.getElementById("btn-email");
    btn.innerText = "Checking...";
    btn.disabled = true;
    
    const fd = new FormData();
    fd.append("email", em);
    
    try {
        const r = await fetch("api_preferences.php", {method:"POST", body:fd});
        const d = await r.json();
        
        if (d.success && d.found) {
            window.location.href = targetUrl;
        } else {
            alert("Email not found. Please use the New User section to submit your details.");
            btn.innerText = "Continue with Email";
            btn.disabled = false;
        }
    } catch(err) {
        alert("Network Error");
        btn.innerText = "Continue with Email";
        btn.disabled = false;
    }
}

async function loadDegrees() {
    const u = document.getElementById("pref-uni").value;
    const l = document.getElementById("degree-list");
    const i = document.getElementById("pref-degree");
    l.innerHTML = "";
    i.value = "";
    
    if(!u) return;
    
    try {
        const r = await fetch("api_preferences.php?action=get_degrees&university="+encodeURIComponent(u));
        const ds = await r.json();
        ds.forEach(d => {
            const o = document.createElement("option");
            o.value = d;
            l.appendChild(o);
        });
    } catch(err) {
        console.error("Error loading degrees:", err);
    }
}

async function submitDetails(e) {
    e.preventDefault();
    
    const em = document.getElementById("pref-email-new").value;
    const nm = document.getElementById("pref-name").value;
    const gEl = document.querySelector("input[name=pref-gender]:checked");
    const gnd = gEl ? gEl.value : "";
    const st = document.getElementById("pref-stream").value;
    const u = document.getElementById("pref-uni").value;
    const dg = document.getElementById("pref-degree").value;
    
    if(!em || !nm || !gnd || !st || !u || !dg) {
        return alert("Please fill all fields.");
    }
    
    const btn = document.getElementById("btn-submit");
    btn.innerText = "Submitting...";
    btn.disabled = true;
    
    const fd = new FormData();
    fd.append("email", em);
    fd.append("name", nm);
    fd.append("gender", gnd);
    fd.append("stream", st);
    fd.append("university", u);
    fd.append("degree", dg);
    
    try {
        const r = await fetch("api_preferences.php", {method:"POST", body:fd});
        const d = await r.json();
        
        if (d.success) {
            window.location.href = targetUrl;
        } else {
            alert("Error: " + (d.error || "Unknown"));
            btn.innerText = "Submit & Continue";
            btn.disabled = false;
        }
    } catch(err) {
        alert("Network error");
        btn.innerText = "Submit & Continue";
        btn.disabled = false;
    }
}
</script>

<?php include "includes/footer.php"; ?>

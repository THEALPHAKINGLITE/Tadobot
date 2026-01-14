<?php
session_start();
$storage_dir = "uploads/";
$data_file = "download_counts.json";

if (!file_exists($storage_dir)) mkdir($storage_dir, 0777, true);
$counts = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
$total_downloads = array_sum($counts);

$today_start = strtotime('today');
$today_uploads = 0;
$all_files = array_diff(scandir($storage_dir), array('.', '..'));
foreach ($all_files as $f) {
    if (filemtime($storage_dir . $f) >= $today_start) {
        $today_uploads++;
    }
}

if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $path = $storage_dir . $file;
    if (file_exists($path)) {
        $counts[$file] = ($counts[$file] ?? 0) + 1;
        file_put_contents($data_file, json_encode($counts));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile($path);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tadoboy VPN Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;500&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #00d2ff; --glass: rgba(255, 255, 255, 0.08); --bg: #000510; }
        .theme-white { --primary: #555; --bg: #f0f0f0; color: #222 !important; }
        .theme-black { --primary: #ffffff; --bg: #000000; }
        .theme-blue { --primary: #00d2ff; --bg: #001a33; }
        .theme-red { --primary: #ff4444; --bg: #2b0000; }

        body { margin: 0; background: var(--bg); color: white; font-family: 'Roboto', sans-serif; height: 100vh; display: flex; flex-direction: column; overflow: hidden; transition: background 0.5s ease; }
        
        /* Splash Styles */
        #splash { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000510; display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 3000; transition: opacity 0.8s ease; }
        
        /* Image Box Border */
        .logo-container {
            padding: 10px;
            border: 2px solid var(--primary);
            background: rgba(0, 210, 255, 0.05);
            box-shadow: 0 0 15px var(--primary);
            border-radius: 12px;
            animation: breathe 3s infinite ease-in-out;
        }

        .logo-img { 
            width: 150px; /* Resized image */
            height: 150px; 
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }

        /* Neon Marquee Feature */
        .marquee-container { background: rgba(0, 210, 255, 0.1); border-top: 1px solid var(--primary); border-bottom: 1px solid var(--primary); padding: 5px 0; overflow: hidden; white-space: nowrap; margin-bottom: 10px; }
        .marquee-text { display: inline-block; animation: scroll 15s linear infinite; font-family: 'Orbitron'; font-size: 0.6rem; color: var(--primary); text-transform: uppercase; }
        @keyframes scroll { from { transform: translateX(100%); } to { transform: translateX(-100%); } }

        /* Menu and UI Elements */
        .menu-btn { position: fixed; top: 15px; right: 15px; z-index: 2000; cursor: pointer; background: var(--primary); padding: 8px 12px; border-radius: 5px; color: black; font-weight: bold; border: none; box-shadow: 0 0 10px var(--primary); font-size: 0.8rem; }
        .side-nav { position: fixed; right: -250px; top: 0; width: 250px; height: 100%; background: rgba(0,0,0,0.95); z-index: 1999; transition: 0.4s; padding-top: 80px; backdrop-filter: blur(15px); border-left: 2px solid var(--primary); }
        .side-nav.active { right: 0; }
        .nav-item { padding: 15px 25px; }
        
        #toast { visibility: hidden; width: 80%; max-width: 300px; background-color: var(--primary); color: #000; text-align: center; border-radius: 10px; padding: 12px; position: fixed; z-index: 4000; left: 50%; bottom: 80px; transform: translateX(-50%); font-weight: bold; font-family: 'Orbitron'; font-size: 0.75rem; }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }

        .container { max-width: 900px; margin: 0 auto; padding: 15px; width: 100%; flex: 1; display: flex; flex-direction: column; overflow-y: auto; box-sizing: border-box; }
        header { text-align: center; padding: 12px; background: var(--glass); backdrop-filter: blur(15px); border-radius: 15px; margin-bottom: 10px; border: 1px solid var(--primary); }
        
        .tab-bar { display: flex; background: var(--glass); border-radius: 50px; padding: 4px; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .tab-btn { background: transparent; border: none; color: inherit; padding: 10px 5px; border-radius: 30px; cursor: pointer; font-family: 'Orbitron'; font-size: 0.6rem; transition: 0.3s; flex: 1; }
        .tab-btn.active { background: var(--primary); color: black; box-shadow: 0 0 10px var(--primary); }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: slideUp 0.4s ease; }

        .glass-card { background: var(--glass); backdrop-filter: blur(12px); border-radius: 15px; padding: 15px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 15px; }
        .btn { background: var(--primary); color: black; border: none; padding: 6px 14px; border-radius: 30px; font-weight: 700; cursor: pointer; font-family: 'Orbitron'; font-size: 0.65rem; }
        
        .dev-btn { display: block; width: 100%; margin: 8px 0; text-align: center; padding: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--primary); border-radius: 8px; color: var(--primary); text-decoration: none; font-size: 0.75rem; font-family: 'Orbitron'; box-sizing: border-box; }

        /* Animations */
        @keyframes breathe { 0%, 100% { transform: scale(1); opacity: 0.8; } 50% { transform: scale(1.05); opacity: 1; } }
        @keyframes fadein { from { bottom: 0; opacity: 0; } to { bottom: 80px; opacity: 1; } }
        @keyframes fadeout { from { bottom: 80px; opacity: 1; } to { bottom: 0; opacity: 0; } }
        @keyframes slideUp { from { transform: translateY(15px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .color-dot { display: inline-block; width: 32px; height: 32px; border-radius: 50%; margin: 8px; cursor: pointer; border: 2px solid rgba(255,255,255,0.5); }
        .badge { background: #ff4444; color: white; padding: 2px 6px; border-radius: 8px; font-size: 0.55rem; vertical-align: middle; }
    </style>
</head>
<body id="mainBody">

    <div id="toast">Action Successful!</div>

    <div id="splash">
        <div class="logo-container">
            <img src="https://i.ibb.co/Jj5NTsCh/tourl-1768380487537.jpg" class="logo-img">
        </div>
        <h1 style="font-family: 'Orbitron'; color: var(--primary); margin-top: 20px; font-size: 2rem; text-shadow: 0 0 10px var(--primary);">TADOBOY OFFICIAL</h1>
    </div>

    <button class="menu-btn" onclick="toggleMenu()">☰</button>

    <div class="side-nav" id="mySidenav">
        <div class="nav-item">
            <p style="font-family: 'Orbitron'; font-size: 0.7rem; color: var(--primary);">THEMES</p>
            <div style="display: flex; flex-wrap: wrap; justify-content: center;">
                <div class="color-dot" style="background: white;" onclick="setTheme('white')"></div>
                <div class="color-dot" style="background: black;" onclick="setTheme('black')"></div>
                <div class="color-dot" style="background: #00d2ff;" onclick="setTheme('blue')"></div>
                <div class="color-dot" style="background: #ff4444;" onclick="setTheme('red')"></div>
            </div>
        </div>
        <hr style="opacity: 0.1;">
        <div class="nav-item">
            <p style="font-family: 'Orbitron'; font-size: 0.7rem; color: var(--primary);">CONTACTS</p>
            <a href="tel:+263781125707" class="dev-btn">ADMIN ALPHA</a>
            <a href="https://wa.me/263788145066" class="dev-btn">TADOBOY</a>
        </div>
    </div>

    <div class="container">
        <header>
            <h1 style="font-family: 'Orbitron'; font-size: 0.85rem; letter-spacing: 1px; color: var(--primary); margin: 0;">PREMIUM SECURE HUB</h1>
        </header>

        <div class="marquee-container">
            <div class="marquee-text">
                🚨 SYSTEM UPDATE: ALL CONFIGS REFRESHED FOR 2026 • ⚡ HIGH SPEED DOWNLOADS ENABLED • 🔐 ENCRYPTION: AES-256 ACTIVE • 🚀 WELCOME TO THE FUTURE OF VPN •
            </div>
        </div>

        <div class="tab-bar">
            <button class="tab-btn active" onclick="openTab(event, 'files')">FILES</button>
            <button class="tab-btn" onclick="openTab(event, 'updates')">NEW <span class="badge"><?php echo $today_uploads; ?></span></button>
            <button class="tab-btn" onclick="openTab(event, 'stats')">STATS</button>
        </div>

        <div id="files" class="tab-content active">
            <div class="glass-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <?php if (empty($all_files)): ?>
                        <tr><td style="text-align: center; padding: 20px; font-size: 0.8rem; opacity: 0.5;">No files found in directory.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_files as $file): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px 0; font-size: 0.7rem;"><?php echo $file; ?></td>
                            <td style="text-align: right;">
                                <button onclick="confirmDownload('<?php echo urlencode($file); ?>')" class="btn">GET</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div id="updates" class="tab-content">
            <div class="glass-card">
                <h4 style="font-family: 'Orbitron'; color: var(--primary); margin-top:0; font-size: 0.8rem;">TODAY'S UPLOADS</h4>
                <div style="font-size: 0.75rem; border-left: 2px solid var(--primary); padding-left: 10px;">
                    <?php 
                    $found = false;
                    foreach ($all_files as $f) {
                        if (filemtime($storage_dir . $f) >= $today_start) {
                            echo "• " . $f . "<br>";
                            $found = true;
                        }
                    }
                    if (!$found) echo "No files uploaded today.";
                    ?>
                </div>
            </div>
        </div>

        <div id="stats" class="tab-content">
            <div class="glass-card" style="text-align: center;">
                <p style="font-family: 'Orbitron'; font-size: 0.6rem; opacity: 0.7;">TOTAL HUB TRAFFIC</p>
                <h2 style="font-family: 'Orbitron'; color: var(--primary); margin: 5px 0; font-size: 1.5rem;"><?php echo $total_downloads; ?></h2>
                <small style="font-size: 0.6rem;">SUCCESSFUL CONNECTIONS</small>
            </div>
        </div>
    </div>

    <footer style="padding: 12px; background: var(--glass); backdrop-filter: blur(15px); border-top: 1px solid var(--primary); text-align: center;">
        <div style="display: flex; justify-content: space-around; align-items: center; margin-bottom: 5px;">
            <div style="font-family: 'Orbitron'; font-size: 0.5rem; color: #00ff00;">
                <span style="display: inline-block; width: 5px; height: 5px; background: #00ff00; border-radius: 50%; box-shadow: 0 0 5px #00ff00; margin-right: 3px; animation: breathe 2s infinite;"></span>
                ONLINE
            </div>
            <div id="greeting" style="font-family: 'Orbitron'; font-size: 0.5rem; color: var(--primary);">WELCOME</div>
            <div id="digitalClock" style="font-family: 'Orbitron'; font-size: 0.55rem; color: #fff;">00:00:00</div>
        </div>
        <p style="font-family: 'Orbitron'; font-size: 0.65rem; margin: 3px 0; letter-spacing: 1px; color: var(--primary);">
            TADOBOY X ALPHA 2026
        </p>
    </footer>

    <script>
        setTimeout(() => { 
            const s = document.getElementById('splash');
            s.style.opacity = '0'; 
            setTimeout(() => { s.style.display = 'none'; }, 800); 
        }, 2500);

        function toggleMenu() { document.getElementById("mySidenav").classList.toggle("active"); }

        function setTheme(theme) {
            document.getElementById("mainBody").className = "theme-" + theme;
            showToast("Theme: " + theme.toUpperCase());
            toggleMenu();
        }

        function showToast(msg) {
            var x = document.getElementById("toast");
            x.innerText = msg;
            x.className = "show";
            setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
        }

        function openTab(evt, tabName) {
            var i, tc = document.getElementsByClassName("tab-content"), tb = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tc.length; i++) tc[i].style.display = "none";
            for (i = 0; i < tb.length; i++) tb[i].classList.remove("active");
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }

        function confirmDownload(fileName) {
            if (confirm("Access Premium File?")) {
                showToast("Downloading...");
                setTimeout(() => { window.location.href = "?download=" + fileName; }, 500);
            }
        }

        function updateFooter() {
            const now = new Date();
            const h = now.getHours();
            document.getElementById('digitalClock').textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
            let greet = (h < 12) ? "MORNING" : (h < 18) ? "AFTERNOON" : "EVENING";
            document.getElementById('greeting').textContent = "GOOD " + greet;
        }
        setInterval(updateFooter, 1000);
        updateFooter();
    </script>
</body>
</html>

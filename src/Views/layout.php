<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Cloudflare DNS Manager') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . '/../../public/assets/style.css') ?>">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        .nav-menu {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--text-main);
        }
        .nav-link.active {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.25rem;
        }
    </style>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<div class="container">
    <div class="header">
        <div>
            <h1>DNS Manager</h1>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Logged in as <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <nav class="nav-menu">
                <a href="?route=dashboard" class="nav-link <?= ($_GET['route'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="?route=domains" class="nav-link <?= ($_GET['route'] ?? '') === 'domains' ? 'active' : '' ?>">Domains</a>
                <a href="?route=users" class="nav-link <?= ($_GET['route'] ?? '') === 'users' ? 'active' : '' ?>">Users</a>
            </nav>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle theme"></button>
            <a href="?route=logout" class="btn-sm btn-danger" style="text-decoration: none; padding: 0.75rem 1rem;">Logout</a>
        </div>
    </div>
    
    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <?= $content ?? '' ?>

</div>
<?php else: ?>
    <!-- For login page, we don't display the logged-in header -->
    <div style="position: absolute; top: 2rem; right: 2rem;">
        <button id="themeToggle" class="theme-toggle" aria-label="Toggle theme"></button>
    </div>
    <?= $content ?? '' ?>
<?php endif; ?>

<script>
    // Theme Toggle Logic
    const themeToggleBtn = document.getElementById('themeToggle');
    const htmlEl = document.documentElement;
    
    const moonIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;
    const sunIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;

    function updateThemeIcon() {
        if(htmlEl.getAttribute('data-theme') === 'light') {
            themeToggleBtn.innerHTML = moonIcon;
        } else {
            themeToggleBtn.innerHTML = sunIcon;
        }
    }
    
    if (themeToggleBtn) {
        updateThemeIcon();
        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlEl.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlEl.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        });
    }
</script>

</body>
</html>

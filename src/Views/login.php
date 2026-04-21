<?php
$title = "Login - Cloudflare DNS Manager";
ob_start();
?>

<div class="container" style="max-width: 400px; margin-top: 10vh;">
    <div class="card">
        <h2 class="card-title" style="text-align: center; font-size: 2rem; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="?route=login">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" style="width: 100%;">Sign In</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>

<?php
require_once __DIR__ . '/config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cloudflare DNS Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        input[type="password"] {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; margin-top: 10vh;">
    <div class="card">
        <h2 class="card-title" style="text-align: center; font-size: 2rem; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
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

</body>
</html>

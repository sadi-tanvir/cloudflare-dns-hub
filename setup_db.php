<?php
require_once __DIR__ . '/config.php';

$hash = password_hash('password123', PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES ('admin', ?)");
        $stmt->execute([$hash]);
        echo "Admin user created successfully! Username: admin | Password: password123\n";
    } else {
        echo "Admin user already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error inserting user: " . $e->getMessage() . "\n";
}

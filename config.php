<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$apiToken = $_ENV['CLOUDFLARE_API_TOKEN'] ?? $_SERVER['CLOUDFLARE_API_TOKEN'] ?? '';

// Database Connection
$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbName = $_ENV['DB_NAME'] ?? 'cloudflare_management';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch domains from Database instead of env
$domains = [];
try {
    $stmt = $pdo->query("SELECT domain_name FROM domains ORDER BY domain_name ASC");
    while ($row = $stmt->fetch()) {
        $domains[] = $row['domain_name'];
    }
} catch (PDOException $e) {
    // If table doesn't exist yet, leave empty
}

if (empty($domains)) {
    $domains = ['example.com']; // Fallback
}

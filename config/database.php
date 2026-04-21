<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

function getDbConnection() {
    $dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbName = $_ENV['DB_NAME'] ?? 'cloudflare_management';
    $dbUser = $_ENV['DB_USER'] ?? 'root';
    $dbPass = $_ENV['DB_PASS'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

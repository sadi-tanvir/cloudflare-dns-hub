<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$apiToken = $_ENV['CLOUDFLARE_API_TOKEN'] ?? $_SERVER['CLOUDFLARE_API_TOKEN'] ?? '';

// Parse comma separated domains
$domainsStr = $_ENV['ALLOWED_DOMAINS'] ?? $_SERVER['ALLOWED_DOMAINS'] ?? 'bsdbdisp.com';
$domains = array_filter(array_map('trim', explode(',', $domainsStr)));

if (empty($domains)) {
    $domains = ['bsdbdisp.com'];
}

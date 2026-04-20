<?php
// Simple .env loader
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    foreach ($envVars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

$apiToken = $_ENV['CLOUDFLARE_API_TOKEN'] ?? getenv('CLOUDFLARE_API_TOKEN') ?? '';

// Parse comma separated domains
$domainsStr = $_ENV['ALLOWED_DOMAINS'] ?? getenv('ALLOWED_DOMAINS') ?? 'bsdbdisp.com';
$domains = array_filter(array_map('trim', explode(',', $domainsStr)));

if (empty($domains)) {
    $domains = ['bsdbdisp.com'];
}

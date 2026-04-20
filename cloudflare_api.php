<?php
// Helper to make API requests
function cloudflareApiRequest($endpoint, $method = 'GET', $data = null) {
    global $apiToken;
    $url = 'https://api.cloudflare.com/client/v4/' . $endpoint;
    
    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

function getZoneId($domain) {
    $zoneRes = cloudflareApiRequest('zones?name=' . urlencode($domain));
    if ($zoneRes['code'] == 200 && !empty($zoneRes['body']['result'])) {
        return ['success' => true, 'zone_id' => $zoneRes['body']['result'][0]['id']];
    } else {
        $errorDetails = $zoneRes['body']['errors'][0]['message'] ?? 'Unknown error';
        if ($zoneRes['code'] != 200) {
            return ['success' => false, 'error' => "Could not connect to Cloudflare API. HTTP Code: {$zoneRes['code']} - {$errorDetails}"];
        } else {
            return ['success' => false, 'error' => "Could not find zone for $domain. Ensure the domain is added to this Cloudflare account."];
        }
    }
}

function getDnsRecords($zoneId) {
    $recordsRes = cloudflareApiRequest("zones/{$zoneId}/dns_records?per_page=100");
    if ($recordsRes['code'] == 200 && $recordsRes['body']['success']) {
        return ['success' => true, 'records' => $recordsRes['body']['result']];
    }
    return ['success' => false, 'records' => []];
}

function addDnsRecord($zoneId, $type, $name, $content, $ttl, $proxied) {
    $postData = [
        'type' => $type,
        'name' => $name,
        'content' => $content,
        'ttl' => $ttl,
        'proxied' => $proxied
    ];
    
    $addRes = cloudflareApiRequest("zones/{$zoneId}/dns_records", 'POST', $postData);
    if ($addRes['code'] == 200 && $addRes['body']['success']) {
        return ['success' => true];
    } else {
        $errorDetails = $addRes['body']['errors'][0]['message'] ?? 'Unknown error';
        return ['success' => false, 'error' => "Failed to add record: " . $errorDetails];
    }
}

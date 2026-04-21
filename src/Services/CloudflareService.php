<?php

class CloudflareService {
    private $apiToken;
    private $baseUrl = 'https://api.cloudflare.com/client/v4/';

    public function __construct($apiToken) {
        $this->apiToken = $apiToken;
    }

    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->baseUrl . ltrim($endpoint, '/');
        
        $ch = curl_init($url);
        
        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => "cURL Error: $error"];
        }
        
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300 && isset($decodedResponse['success']) && $decodedResponse['success'] === true) {
            return ['success' => true, 'data' => $decodedResponse];
        } else {
            $errorMsg = 'Unknown error from Cloudflare API.';
            if (isset($decodedResponse['errors']) && is_array($decodedResponse['errors']) && count($decodedResponse['errors']) > 0) {
                $errorMsg = $decodedResponse['errors'][0]['message'];
            }
            return ['success' => false, 'error' => $errorMsg, 'http_code' => $httpCode];
        }
    }

    public function getZoneId($domainName) {
        $res = $this->makeRequest('GET', 'zones?name=' . urlencode($domainName));
        if ($res['success']) {
            if (count($res['data']['result']) > 0) {
                return ['success' => true, 'zone_id' => $res['data']['result'][0]['id']];
            } else {
                return ['success' => false, 'error' => "Domain not found in your Cloudflare account."];
            }
        }
        return $res;
    }

    public function getDnsRecords($zoneId) {
        $res = $this->makeRequest('GET', "zones/$zoneId/dns_records?per_page=100");
        if ($res['success']) {
            return ['success' => true, 'records' => $res['data']['result']];
        }
        return $res;
    }

    public function addDnsRecord($zoneId, $type, $name, $content, $ttl, $proxied) {
        $data = [
            'type' => $type,
            'name' => $name,
            'content' => $content,
            'ttl' => $ttl,
            'proxied' => $proxied
        ];
        return $this->makeRequest('POST', "zones/$zoneId/dns_records", $data);
    }

    public function updateDnsRecord($zoneId, $recordId, $type, $name, $content, $ttl, $proxied) {
        $data = [
            'type' => $type,
            'name' => $name,
            'content' => $content,
            'ttl' => $ttl,
            'proxied' => $proxied
        ];
        return $this->makeRequest('PUT', "zones/$zoneId/dns_records/$recordId", $data);
    }

    public function deleteDnsRecord($zoneId, $recordId) {
        return $this->makeRequest('DELETE', "zones/$zoneId/dns_records/$recordId");
    }
}

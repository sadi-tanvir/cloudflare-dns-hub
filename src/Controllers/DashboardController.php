<?php

class DashboardController {
    private $domainModel;
    private $cloudflareService;

    public function __construct($domainModel, $cloudflareService) {
        $this->domainModel = $domainModel;
        $this->cloudflareService = $cloudflareService;
    }

    public function index() {
        $domains = $this->domainModel->getAllDomains();
        $domainList = array_column($domains, 'domain_name');
        
        $selectedDomain = $_GET['domain'] ?? ($domainList[0] ?? null);
        $errorMsg = '';
        $successMsg = '';
        $zoneId = '';
        $dnsRecords = [];

        if (!$selectedDomain) {
            $errorMsg = "No domains found. Please add a domain first.";
            require_once __DIR__ . '/../Views/dashboard.php';
            return;
        }

        // 1. Get Zone ID
        $zoneData = $this->cloudflareService->getZoneId($selectedDomain);
        if ($zoneData['success']) {
            $zoneId = $zoneData['zone_id'];
        } else {
            $errorMsg = $zoneData['error'];
        }

        // 2. Handle Form Submissions (Add, Edit, Delete)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $zoneId) {
            if ($_POST['action'] === 'delete_record' && isset($_POST['record_id'])) {
                $deleteRes = $this->cloudflareService->deleteDnsRecord($zoneId, $_POST['record_id']);
                if ($deleteRes['success']) {
                    $successMsg = "DNS Record deleted successfully!";
                } else {
                    $errorMsg = $deleteRes['error'];
                }
            } else {
                $type = $_POST['type'] ?? 'A';
                $name = trim($_POST['name'] ?? '');
                if ($name === '@') {
                    $name = $selectedDomain;
                } elseif (strpos($name, $selectedDomain) === false && $name !== '') {
                    $name = $name . '.' . $selectedDomain;
                }
                
                $content = trim($_POST['content'] ?? '');
                $ttl = (int)($_POST['ttl'] ?? 1);
                $proxied = isset($_POST['proxied']) ? true : false;
                
                if ($_POST['action'] === 'add_record') {
                    $addRes = $this->cloudflareService->addDnsRecord($zoneId, $type, $name, $content, $ttl, $proxied);
                    if ($addRes['success']) {
                        $successMsg = "DNS Record added successfully!";
                    } else {
                        $errorMsg = $addRes['error'];
                    }
                } elseif ($_POST['action'] === 'edit_record' && isset($_POST['record_id'])) {
                    $updateRes = $this->cloudflareService->updateDnsRecord($zoneId, $_POST['record_id'], $type, $name, $content, $ttl, $proxied);
                    if ($updateRes['success']) {
                        $successMsg = "DNS Record updated successfully!";
                    } else {
                        $errorMsg = $updateRes['error'];
                    }
                }
            }
        }

        // 3. Fetch DNS Records
        if ($zoneId) {
            $recordsData = $this->cloudflareService->getDnsRecords($zoneId);
            if ($recordsData['success']) {
                $dnsRecords = $recordsData['records'];
            }
        }

        require_once __DIR__ . '/../Views/dashboard.php';
    }
}

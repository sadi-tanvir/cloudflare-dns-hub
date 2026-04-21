<?php

class DomainController {
    private $domainModel;

    public function __construct($domainModel) {
        $this->domainModel = $domainModel;
    }

    public function index() {
        $errorMsg = '';
        $successMsg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            $domainId = $_POST['domain_id'] ?? null;
            $domainName = trim($_POST['domain_name'] ?? '');

            try {
                if ($action === 'add' && !empty($domainName)) {
                    $this->domainModel->addDomain($domainName);
                    $successMsg = 'Domain added successfully.';
                } elseif ($action === 'edit' && $domainId && !empty($domainName)) {
                    $this->domainModel->updateDomain($domainId, $domainName);
                    $successMsg = 'Domain updated successfully.';
                } elseif ($action === 'delete' && $domainId) {
                    $this->domainModel->deleteDomain($domainId);
                    $successMsg = 'Domain deleted successfully.';
                } else {
                    $errorMsg = 'Invalid request or empty domain name.';
                }
            } catch (Exception $e) {
                $errorMsg = 'Error processing request.';
            }
        }

        $domains = $this->domainModel->getAllDomains();
        require_once __DIR__ . '/../Views/domains.php';
    }
}

<?php

class Domain {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllDomains() {
        try {
            $stmt = $this->pdo->query("SELECT id, domain_name FROM domains ORDER BY domain_name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return []; // Return empty if table doesn't exist
        }
    }

    public function getDomainById($id) {
        $stmt = $this->pdo->prepare("SELECT id, domain_name FROM domains WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addDomain($domainName) {
        $stmt = $this->pdo->prepare("INSERT INTO domains (domain_name) VALUES (?)");
        return $stmt->execute([$domainName]);
    }

    public function updateDomain($id, $domainName) {
        $stmt = $this->pdo->prepare("UPDATE domains SET domain_name = ? WHERE id = ?");
        return $stmt->execute([$domainName, $id]);
    }

    public function deleteDomain($id) {
        $stmt = $this->pdo->prepare("DELETE FROM domains WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

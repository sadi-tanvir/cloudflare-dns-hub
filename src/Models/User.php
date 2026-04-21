<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT id, username FROM users ORDER BY username ASC");
        return $stmt->fetchAll();
    }

    public function addUser($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $hashedPassword]);
    }

    public function updateUserPassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function deleteUser($id) {
        // Prevent deleting the very last user
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        $count = $stmt->fetchColumn();
        if ($count <= 1) {
            return false; // Cannot delete the last user
        }

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

<?php

class AuthController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=dashboard');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if ($username && $password) {
                $user = $this->userModel->getUserByUsername($username);
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    header('Location: index.php?route=dashboard');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                $error = 'Please enter username and password.';
            }
        }

        require_once __DIR__ . '/../Views/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?route=login');
        exit;
    }
}

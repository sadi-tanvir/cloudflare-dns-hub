<?php

class UserController {
    private $userModel;

    public function __construct($userModel) {
        $this->userModel = $userModel;
    }

    public function index() {
        $errorMsg = '';
        $successMsg = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            try {
                if ($action === 'add') {
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    if ($username && $password) {
                        $this->userModel->addUser($username, $password);
                        $successMsg = 'User added successfully.';
                    } else {
                        $errorMsg = 'Username and password are required.';
                    }
                } elseif ($action === 'change_password') {
                    $userId = $_POST['user_id'] ?? null;
                    $newPassword = $_POST['new_password'] ?? '';
                    if ($userId && $newPassword) {
                        $this->userModel->updateUserPassword($userId, $newPassword);
                        $successMsg = 'Password updated successfully.';
                    } else {
                        $errorMsg = 'Password cannot be empty.';
                    }
                } elseif ($action === 'delete') {
                    $userId = $_POST['user_id'] ?? null;
                    if ($userId) {
                        // Ensure a user cannot delete themselves to avoid lockout? Or let them.
                        if ($userId == $_SESSION['user_id']) {
                            // Can delete themselves if they want, but probably shouldn't.
                            $errorMsg = 'You cannot delete your currently logged-in account.';
                        } else {
                            $res = $this->userModel->deleteUser($userId);
                            if ($res) {
                                $successMsg = 'User deleted successfully.';
                            } else {
                                $errorMsg = 'Cannot delete the last remaining user.';
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // E.g. Duplicate username
                $errorMsg = 'Error processing request. Make sure usernames are unique.';
            }
        }

        $users = $this->userModel->getAllUsers();
        require_once __DIR__ . '/../Views/users.php';
    }
}

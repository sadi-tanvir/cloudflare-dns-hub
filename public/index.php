<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Domain.php';
// We'll require controllers and services as needed

$pdo = getDbConnection();
$userModel = new User($pdo);
$domainModel = new Domain($pdo);
$apiToken = $_ENV['CLOUDFLARE_API_TOKEN'] ?? $_SERVER['CLOUDFLARE_API_TOKEN'] ?? '';

// Basic Routing logic
$route = $_GET['route'] ?? 'dashboard';

// Check Auth for protected routes
$publicRoutes = ['login'];

if (!in_array($route, $publicRoutes) && !isset($_SESSION['user_id'])) {
    header('Location: index.php?route=login');
    exit;
}

// Controller Dispatcher
switch ($route) {
    case 'login':
        require_once __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new AuthController($userModel);
        $controller->login();
        break;

    case 'logout':
        require_once __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new AuthController($userModel);
        $controller->logout();
        break;

    case 'dashboard':
        require_once __DIR__ . '/../src/Services/CloudflareService.php';
        require_once __DIR__ . '/../src/Controllers/DashboardController.php';
        
        $cloudflareService = new CloudflareService($apiToken);
        $controller = new DashboardController($domainModel, $cloudflareService);
        $controller->index();
        break;

    case 'domains':
        require_once __DIR__ . '/../src/Controllers/DomainController.php';
        $controller = new DomainController($domainModel);
        $controller->index();
        break;

    case 'users':
        require_once __DIR__ . '/../src/Controllers/UserController.php';
        $controller = new UserController($userModel);
        $controller->index();
        break;

    default:
        die('404 Not Found');
}

<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/Database.php';

// Simple Router
$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'index';

switch ($controller) {
    case 'auth':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $auth = new AuthController();
        if (method_exists($auth, $action)) {
            $auth->$action();
        } else {
            $auth->index();
        }
        break;
    case 'dashboard':
        require_once __DIR__ . '/app/controllers/DashboardController.php';
        $dash = new DashboardController();
        if (method_exists($dash, $action)) {
            $dash->$action();
        } else {
            $dash->index();
        }
        break;
    default:
        echo "404 Not Found";
        break;
}
?>

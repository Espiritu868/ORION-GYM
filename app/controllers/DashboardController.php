<?php
session_start();

class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php?controller=auth&action=index');
            exit;
        }

        require_once dirname(__DIR__) . '/views/dashboard.php';
    }
}
?>

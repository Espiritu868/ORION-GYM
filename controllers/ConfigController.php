<?php
session_start();
require_once '../includes/functions.php';

// Check auth
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';
$controller = new ConfigController();

switch ($action) {
    case 'getReceiptConfig':
        $controller->getReceiptConfig();
        break;
    case 'saveReceiptConfig':
        $controller->saveReceiptConfig();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

class ConfigController {
    private $configFile = '../config/receipt_config.json';

    public function getReceiptConfig() {
        if (!hasPerm('administracion_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        if (file_exists($this->configFile)) {
            $data = json_decode(file_get_contents($this->configFile), true);
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            // Default data
            $default = [
                'gym_name' => 'ORION GYM',
                'rtn' => '',
                'show_rtn' => true,
                'phone' => '',
                'show_phone' => true,
                'address' => '',
                'show_address' => true,
                'social' => '',
                'show_social' => true,
                'footer' => '¡Gracias por su preferencia!'
            ];
            echo json_encode(['success' => true, 'data' => $default]);
        }
    }

    public function saveReceiptConfig() {
        if (!hasPerm('administracion_edit')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $data = [
            'gym_name' => trim($_POST['gym_name'] ?? ''),
            'rtn' => trim($_POST['rtn'] ?? ''),
            'show_rtn' => isset($_POST['show_rtn']) ? true : false,
            'phone' => trim($_POST['phone'] ?? ''),
            'show_phone' => isset($_POST['show_phone']) ? true : false,
            'address' => trim($_POST['address'] ?? ''),
            'show_address' => isset($_POST['show_address']) ? true : false,
            'social' => trim($_POST['social'] ?? ''),
            'show_social' => isset($_POST['show_social']) ? true : false,
            'footer' => trim($_POST['footer'] ?? '')
        ];

        if (file_put_contents($this->configFile, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo de configuración. Verifica los permisos de escritura en la carpeta config.']);
        }
    }
}

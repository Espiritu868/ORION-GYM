<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Client.php';

class ClientController {
    
    public function list() {
        if (!hasPerm('clientes_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado para ver clientes']);
            return;
        }

        $clientModel = new Client();
        $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
        $clients = $clientModel->listAll($activeOnly);
        echo json_encode(['success' => true, 'data' => $clients]);
    }

    public function save() {
        $id = $_POST['id_cliente'] ?? '';
        
        if (empty($id) && !hasPerm('clientes_create')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear clientes']);
            return;
        }
        if (!empty($id) && !hasPerm('clientes_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar clientes']);
            return;
        }

        $clientModel = new Client();
        
        $data = [
            'identidad' => $_POST['identidad'] ?? '',
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'fotografia' => $_POST['fotografia'] ?? '' 
        ];

        if (empty($data['nombre_completo'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
            return;
        }

        if (empty($id)) {
            $success = $clientModel->create($data);
            $msg = 'Cliente registrado correctamente.';
        } else {
            $success = $clientModel->update($id, $data);
            $msg = 'Cliente actualizado correctamente.';
        }

        echo json_encode(['success' => $success, 'message' => $success ? $msg : 'Error al guardar el cliente.']);
    }

    public function deactivate() {
        if (!hasPerm('clientes_delete')) return;
        $id = $_POST['id_cliente'] ?? '';
        $clientModel = new Client();
        $success = $clientModel->changeState($id, 0);
        echo json_encode(['success' => $success, 'message' => $success ? 'Cliente desactivado (baja lógica).' : 'Error al desactivar.']);
    }

    public function activate() {
        if (!hasPerm('clientes_delete')) return;
        $id = $_POST['id_cliente'] ?? '';
        $clientModel = new Client();
        $success = $clientModel->changeState($id, 1);
        echo json_encode(['success' => $success, 'message' => $success ? 'Cliente reactivado.' : 'Error al reactivar.']);
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new ClientController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

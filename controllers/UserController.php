<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/User.php';

class UserController {
    
    public function list() {
        if (!hasPerm('usuarios_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado para ver usuarios']);
            return;
        }

        $userModel = new User();
        $stateFilter = $_GET['active'] ?? '1';
        if ($stateFilter === 'true') $stateFilter = '1';
        if ($stateFilter === 'false') $stateFilter = 'all';
        $users = $userModel->listAll($stateFilter);
        echo json_encode(['success' => true, 'data' => $users]);
    }

    public function save() {
        $id = $_POST['id_usuario'] ?? '';
        
        if (empty($id) && !hasPerm('usuarios_create')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear usuarios']);
            return;
        }
        if (!empty($id) && !hasPerm('usuarios_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar usuarios']);
            return;
        }

        $userModel = new User();
        $username = $_POST['nombre_usuario'] ?? '';
        $password = $_POST['password'] ?? '';
        $permissions = isset($_POST['permisos']) ? json_decode($_POST['permisos'], true) : [];

        if (empty($username)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario es obligatorio.']);
            return;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario solo puede contener letras, números y guiones bajos.']);
            return;
        }

        if (empty($id)) {
            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios.']);
                return;
            }
            $success = $userModel->create($username, $password, $permissions);
            $msg = 'Usuario registrado correctamente.';
        } else {
            // Edit
            $success = $userModel->update($id, $username, $password, $permissions);
            $msg = 'Usuario actualizado correctamente.';
        }

        if ($success === true) {
            echo json_encode(['success' => true, 'message' => $msg]);
        } else {
            echo json_encode(['success' => false, 'message' => is_string($success) ? 'Error DB: ' . $success : 'Error al guardar el usuario.']);
        }
    }

    public function deactivate() {
        if (!hasPerm('usuarios_delete')) return;
        $id = $_POST['id_usuario'] ?? '';
        
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propio usuario.']);
            return;
        }

        $userModel = new User();
        $success = $userModel->changeState($id, 0);
        echo json_encode(['success' => $success, 'message' => $success ? 'Usuario desactivado.' : 'Error al desactivar.']);
    }

    public function activate() {
        if (!hasPerm('usuarios_delete')) return;
        $id = $_POST['id_usuario'] ?? '';
        $userModel = new User();
        $success = $userModel->changeState($id, 1);
        echo json_encode(['success' => $success, 'message' => $success ? 'Usuario reactivado.' : 'Error al reactivar.']);
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new UserController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Plan.php';

class PlanController {
    
    public function list() {
        if (!hasPerm('administracion_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $planModel = new Plan();
        $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
        $planes = $planModel->listAll($activeOnly);
        echo json_encode(['success' => true, 'data' => $planes]);
    }

    public function save() {
        $id = $_POST['id_plan'] ?? '';
        
        if (empty($id) && !hasPerm('administracion_create')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        if (!empty($id) && !hasPerm('administracion_edit')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $data = [
            'nombre_plan' => trim($_POST['nombre_plan'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'precio' => floatval(str_replace(',', '', $_POST['precio'] ?? '0')),
            'duracion_dias' => intval($_POST['duracion_dias'] ?? 0)
        ];

        if (empty($data['nombre_plan']) || $data['precio'] <= 0 || $data['duracion_dias'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos. Verifica el nombre, precio y duración.']);
            return;
        }

        $planModel = new Plan();

        if (empty($id)) {
            $success = $planModel->create($data);
            $msg = 'Plan creado correctamente.';
        } else {
            $success = $planModel->update($id, $data);
            $msg = 'Plan actualizado correctamente.';
        }

        echo json_encode(['success' => $success, 'message' => $success ? $msg : 'Error al guardar el plan.']);
    }

    public function toggle() {
        if (!hasPerm('administracion_delete')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $id = $_POST['id_plan'] ?? '';
        $state = intval($_POST['estado'] ?? 0);
        
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $planModel = new Plan();

        if ($state === 0 && $planModel->hasActiveMemberships($id)) {
            echo json_encode(['success' => false, 'message' => 'No se puede desactivar el plan porque hay clientes con membresías activas o pendientes usándolo.']);
            return;
        }

        $success = $planModel->changeState($id, $state);
        $msg = $state === 1 ? 'Plan activado.' : 'Plan desactivado.';
        
        echo json_encode(['success' => $success, 'message' => $success ? $msg : 'Error al cambiar estado.']);
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new PlanController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

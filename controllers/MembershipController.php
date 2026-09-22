<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Membership.php';
require_once dirname(__DIR__) . '/models/Client.php';
require_once dirname(__DIR__) . '/models/Plan.php';

class MembershipController {
    
    public function list() {
        if (!hasPerm('membresias_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $membershipModel = new Membership();
        $filterStatus = $_GET['status'] ?? 'todas';
        $membresias = $membershipModel->listAll($filterStatus);

        // Calculate days left for UI
        $today = new DateTime();
        foreach ($membresias as &$m) {
            $m['estado_membresia'] = trim($m['estado_membresia']);
            if (empty($m['fecha_fin'])) {
                $m['dias_restantes'] = 0;
                $m['estado_ui'] = $m['estado_membresia'];
                continue;
            }
            $endDate = new DateTime($m['fecha_fin']);
            $diff = $today->diff($endDate);
            $m['dias_restantes'] = (int)$diff->format('%R%a'); // e.g. +13 or -5
            
            // Re-evaluate 'Por Vencer' logic strictly for the UI badges
            if ($m['estado_membresia'] === 'Activa' && $m['dias_restantes'] >= 0 && $m['dias_restantes'] <= 3) {
                $m['estado_ui'] = 'Por Vencer';
            } else {
                $m['estado_ui'] = $m['estado_membresia'];
            }
        }

        echo json_encode(['success' => true, 'data' => $membresias]);
    }

    public function assign() {
        if (!hasPerm('membresias_create')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $idCliente = $_POST['id_cliente'] ?? '';
        $idPlan = $_POST['id_plan'] ?? '';
        $fechaInicio = $_POST['fecha_inicio'] ?? date('Y-m-d');

        if (empty($idCliente) || empty($idPlan)) {
            echo json_encode(['success' => false, 'message' => 'Cliente y Plan son requeridos.']);
            return;
        }

        $membershipModel = new Membership();
        $success = $membershipModel->assign($idCliente, $idPlan, $fechaInicio);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Membresía asignada correctamente (Pendiente de Pago).' : 'Error al asignar membresía.'
        ]);
    }

    public function update() {
        if (!hasPerm('membresias_edit')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $idMembresia = $_POST['id_membresia'] ?? '';
        $idPlan = $_POST['id_plan'] ?? '';
        $fechaInicio = $_POST['fecha_inicio'] ?? '';

        if (empty($idMembresia) || empty($idPlan) || empty($fechaInicio)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos.']);
            return;
        }

        $membershipModel = new Membership();
        $success = $membershipModel->updatePending($idMembresia, $idPlan, $fechaInicio);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Membresía actualizada correctamente.' : 'Error al actualizar o la membresía ya no está pendiente.'
        ]);
    }

    public function renew() {
        if (!hasPerm('membresias_edit')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $idMembresia = $_POST['id_membresia'] ?? '';
        if (empty($idMembresia)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $membershipModel = new Membership();
        $success = $membershipModel->renew($idMembresia);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Membresía renovada.' : 'Error al renovar.'
        ]);
    }

    public function cancel() {
        if (!hasPerm('membresias_delete')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $idMembresia = $_POST['id_membresia'] ?? '';
        if (empty($idMembresia)) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        $membershipModel = new Membership();
        $success = $membershipModel->cancel($idMembresia);

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Membresía cancelada.' : 'Error al cancelar.'
        ]);
    }
    
    public function getFormData() {
        // Returns lists of clients and plans for the Assign dropdowns
        $clientModel = new Client();
        $planModel = new Plan();
        
        $clients = $clientModel->listAll(true); // only active clients
        $plans = $planModel->listAll(true); // only active plans
        
        echo json_encode(['success' => true, 'clients' => $clients, 'plans' => $plans]);
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new MembershipController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

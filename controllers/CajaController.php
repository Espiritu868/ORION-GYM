<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/Caja.php';

class CajaController {
    
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'listClientsForPayment':
                $this->listClientsForPayment();
                break;
            case 'processMembershipPayment':
                $this->processMembershipPayment();
                break;
            case 'getHistorialHoy':
                $this->getHistorialHoy();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }
    
    public function getHistorialHoy() {
        if (!hasPerm('caja_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $cajaModel = new Caja();
        $data = $cajaModel->getHistorialHoy();
        echo json_encode(['success' => true, 'data' => $data]);
    }
    
    public function listClientsForPayment() {
        if (!hasPerm('caja_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $cajaModel = new Caja();
        $clientes = $cajaModel->listClientsForPayment();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }
    
    public function processMembershipPayment() {
        if (!hasPerm('caja_view')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $idCliente = $_POST['id_cliente'] ?? null;
        $idPlan = $_POST['id_plan'] ?? null;
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        $idMembresia = !empty($_POST['id_membresia']) ? $_POST['id_membresia'] : null;
        $estadoMemb = trim($_POST['estado_memb'] ?? '');
        
        $metodoPago = $_POST['metodo_pago'] ?? 'Efectivo';
        $banco = $_POST['banco'] ?? null;
        $referencia = $_POST['referencia'] ?? null;
        
        $idUsuario = $_SESSION['user_id'] ?? 1; // Fallback to 1 if not set

        if (!$idCliente || !$idPlan || $cantidad < 1) {
            echo json_encode(['success' => false, 'message' => 'Datos de cobro incompletos.']);
            return;
        }

        $cajaModel = new Caja();
        $result = $cajaModel->processMembershipPayment($idCliente, $idPlan, $cantidad, $idMembresia, $estadoMemb, $idUsuario, $metodoPago, $banco, $referencia);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Pago procesado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Error al procesar el pago.']);
        }
    }
}

// Simple router
if (isset($_GET['action'])) {
    $controller = new CajaController();
    $controller->handleRequest();
}

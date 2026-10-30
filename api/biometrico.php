<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/models/Membership.php';

// Token de seguridad para comunicación entre el Agente Local y el Servidor Web
define('BIOMETRIC_API_KEY', 'ORION_GYM_ZK_SECURE_TOKEN_2026');

// Obtener headers de forma segura (compatible con Apache y CLI)
$authHeader = '';
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
}
if (empty($authHeader)) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_GET['token'] ?? '');
}

if (strpos($authHeader, 'Bearer ') === 0) {
    $authHeader = substr($authHeader, 7);
}

if ($authHeader !== BIOMETRIC_API_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado. Token de API inválido.']);
    exit;
}

$action = $_GET['action'] ?? '';
$db = (new Database())->getConnection();

// Actualizar automáticamente membresías vencidas
try {
    $membershipModel = new Membership();
    $membershipModel->autoUpdateExpired();
} catch (Exception $e) {
    // Continuar si hay algún fallo menor de actualización
}

switch ($action) {
    /**
     * 1. GET /api/biometrico.php?action=users_status
     * Devuelve qué IDs de clientes están ACTIVOS (con membresía vigente) 
     * y cuáles están BLOQUEADOS (vencidos o inactivos) para sincronizar el ZKTeco.
     */
    case 'users_status':
        try {
            $sql = "
                SELECT 
                    c.id_cliente,
                    c.nombre_completo,
                    c.estado as cliente_activo,
                    CASE 
                        WHEN c.estado = 1 AND EXISTS (
                            SELECT 1 FROM MEMBRESIAS m 
                            WHERE m.id_cliente = c.id_cliente 
                              AND m.estado_membresia = 'Activa'
                              AND m.fecha_fin >= CAST(GETDATE() AS DATE)
                        ) THEN 1 
                        ELSE 0 
                    END as puede_acceder,
                    (
                        SELECT TOP 1 m2.fecha_fin 
                        FROM MEMBRESIAS m2 
                        WHERE m2.id_cliente = c.id_cliente AND m2.estado_membresia = 'Activa'
                        ORDER BY m2.fecha_fin DESC
                    ) as fecha_vence
                FROM CLIENTES c
            ";
            $stmt = $db->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'total' => count($users),
                'users' => $users
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener usuarios: ' . $e->getMessage()]);
        }
        break;

    /**
     * 2. POST /api/biometrico.php?action=log_attendance
     * Recibe una marcación del ZKTeco, valida y registra en la tabla ASISTENCIA.
     */
    case 'log_attendance':
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data || empty($data['id_cliente'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Falta el id_cliente']);
            exit;
        }

        $idCliente = intval($data['id_cliente']);
        $fechaHora = !empty($data['timestamp']) ? $data['timestamp'] : date('Y-m-d H:i:s');

        try {
            // Verificar cliente
            $stmtC = $db->prepare("SELECT id_cliente, nombre_completo, estado FROM CLIENTES WHERE id_cliente = ?");
            $stmtC->execute([$idCliente]);
            $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                echo json_encode([
                    'success' => false,
                    'status' => 'NOT_FOUND',
                    'message' => "Cliente ID #$idCliente no registrado en el sistema.",
                    'grant_access' => false
                ]);
                exit;
            }

            // Verificar membresía activa
            $stmtM = $db->prepare("
                SELECT TOP 1 m.fecha_fin, p.nombre_plan 
                FROM MEMBRESIAS m
                JOIN PLANES p ON m.id_plan = p.id_plan
                WHERE m.id_cliente = ? AND m.estado_membresia = 'Activa' AND m.fecha_fin >= CAST(GETDATE() AS DATE)
                ORDER BY m.fecha_fin DESC
            ");
            $stmtM->execute([$idCliente]);
            $membresia = $stmtM->fetch(PDO::FETCH_ASSOC);

            // Evitar duplicar asistencias en menos de 2 minutos (por si el usuario deja el dedo pegado)
            $stmtDup = $db->prepare("
                SELECT TOP 1 id_asistencia 
                FROM ASISTENCIA 
                WHERE id_cliente = ? AND fecha_hora >= DATEADD(minute, -2, GETDATE())
            ");
            $stmtDup->execute([$idCliente]);
            $isDuplicate = $stmtDup->fetch();

            if ($cliente['estado'] == 1 && $membresia) {
                $insertSql = "INSERT INTO ASISTENCIA (id_cliente, fecha_hora, fecha_biometrico) VALUES (?, GETDATE(), ?)";
                $stmtIns = $db->prepare($insertSql);
                $stmtIns->execute([$idCliente, !empty($fechaHora) ? $fechaHora : null]);

                echo json_encode([
                    'success' => true,
                    'status' => 'ALLOWED',
                    'grant_access' => true,
                    'cliente' => $cliente['nombre_completo'],
                    'plan' => $membresia['nombre_plan'],
                    'vence' => $membresia['fecha_fin'],
                    'message' => 'ACCESO PERMITIDO'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'status' => 'DENIED',
                    'grant_access' => false,
                    'cliente' => $cliente['nombre_completo'],
                    'message' => 'ACCESO DENEGADO (Membresía Vencida o Cliente Inactivo)'
                ]);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
        }
        break;

    /**
     * 3. GET /api/biometrico.php?action=live_stream
     * Últimos accesos en tiempo real para que la pantalla de Check-In web se refresque sola.
     */
    case 'live_stream':
        try {
            $stmt = $db->query("
                SELECT TOP 10 
                    a.id_asistencia,
                    a.id_cliente,
                    a.fecha_hora,
                    c.nombre_completo,
                    c.identidad,
                    CASE WHEN DATALENGTH(c.fotografia) > 0 THEN 1 ELSE 0 END as tiene_foto
                FROM ASISTENCIA a
                JOIN CLIENTES c ON a.id_cliente = c.id_cliente
                ORDER BY a.fecha_hora DESC
            ");
            echo json_encode([
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode([
            'success' => true,
            'message' => 'API de Integración Biométrica Orion Gym v1.0',
            'available_actions' => ['users_status', 'log_attendance', 'live_stream']
        ]);
        break;
}

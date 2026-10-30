<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

class RutinaAvanzadaController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function get_catalogo() {
        $stmt = $this->conn->query("SELECT * FROM CATALOGO_EJERCICIOS ORDER BY grupo_muscular, nombre");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    public function get_rutina_dia() {
        $id_cliente = $_GET['id_cliente'] ?? 0;
        $dia_semana = $_GET['dia_semana'] ?? 0;

        $stmt = $this->conn->prepare("SELECT id_rutina_dia FROM RUTINA_DIAS WHERE id_cliente = :idc AND dia_semana = :dia");
        $stmt->execute([':idc' => $id_cliente, ':dia' => $dia_semana]);
        $rutina_dia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rutina_dia) {
            echo json_encode(['success' => true, 'data' => []]);
            return;
        }

        $id_rutina_dia = $rutina_dia['id_rutina_dia'];

        $stmt_ej = $this->conn->prepare("
            SELECT re.id_rutina_ej, re.id_ejercicio, re.orden, re.descanso_segundos, c.nombre 
            FROM RUTINA_EJERCICIOS re 
            JOIN CATALOGO_EJERCICIOS c ON re.id_ejercicio = c.id_ejercicio 
            WHERE re.id_rutina_dia = :id 
            ORDER BY re.orden
        ");
        $stmt_ej->execute([':id' => $id_rutina_dia]);
        $ejercicios = $stmt_ej->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ejercicios as &$ej) {
            $stmt_ser = $this->conn->prepare("SELECT * FROM RUTINA_SERIES WHERE id_rutina_ej = :id ORDER BY numero_serie");
            $stmt_ser->execute([':id' => $ej['id_rutina_ej']]);
            $ej['series'] = $stmt_ser->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success' => true, 'data' => $ejercicios]);
    }

    public function save_rutina_dia() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id_cliente = $data['id_cliente'] ?? 0;
            $dia_semana = $data['dia_semana'] ?? 0;
            $ejercicios = $data['ejercicios'] ?? [];

            try {
                $this->conn->beginTransaction();

                // Get or create RUTINA_DIAS
                $stmt = $this->conn->prepare("SELECT id_rutina_dia FROM RUTINA_DIAS WHERE id_cliente = :idc AND dia_semana = :dia");
                $stmt->execute([':idc' => $id_cliente, ':dia' => $dia_semana]);
                $rutina_dia = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($rutina_dia) {
                    $id_rutina_dia = $rutina_dia['id_rutina_dia'];
                    // Delete old exercises (cascade will delete series)
                    $del = $this->conn->prepare("DELETE FROM RUTINA_EJERCICIOS WHERE id_rutina_dia = :id");
                    $del->execute([':id' => $id_rutina_dia]);
                } else {
                    $ins = $this->conn->prepare("INSERT INTO RUTINA_DIAS (id_cliente, dia_semana) VALUES (:idc, :dia)");
                    $ins->execute([':idc' => $id_cliente, ':dia' => $dia_semana]);
                    $id_rutina_dia = $this->conn->lastInsertId();
                }

                $orden = 1;
                foreach ($ejercicios as $ej) {
                    $ins_ej = $this->conn->prepare("INSERT INTO RUTINA_EJERCICIOS (id_rutina_dia, id_ejercicio, orden, descanso_segundos) VALUES (:ird, :iej, :ord, :desc)");
                    $ins_ej->execute([
                        ':ird' => $id_rutina_dia,
                        ':iej' => $ej['id_ejercicio'],
                        ':ord' => $orden++,
                        ':desc' => $ej['descanso_segundos'] ?? 120
                    ]);
                    $id_rutina_ej = $this->conn->lastInsertId();

                    $num_serie = 1;
                    foreach ($ej['series'] as $s) {
                        $ins_ser = $this->conn->prepare("INSERT INTO RUTINA_SERIES (id_rutina_ej, numero_serie, lbs_obj, reps_obj, rpe_obj, tipo_serie) VALUES (:ire, :ns, :lbs, :reps, :rpe, :tipo)");
                        $ins_ser->execute([
                            ':ire' => $id_rutina_ej,
                            ':ns' => $num_serie++,
                            ':lbs' => $s['lbs_obj'] ?? 0,
                            ':reps' => $s['reps_obj'] ?? '',
                            ':rpe' => 0,
                            ':tipo' => $s['tipo_serie'] ?? 'N'
                        ]);
                    }
                }

                $this->conn->commit();
                echo json_encode(['success' => true, 'message' => 'Rutina guardada exitosamente.']);
            } catch (Exception $e) {
                $this->conn->rollBack();
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }
}

$action = $_GET['action'] ?? '';
$controller = new RutinaAvanzadaController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>

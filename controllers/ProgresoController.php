<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

class ProgresoController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function guardar_medida() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id_cliente = $_POST['id_cliente'] ?? 0;
                $peso = $_POST['peso'] ?? 0;
                $pecho = $_POST['pecho'] ?? 0;
                $brazo = $_POST['brazo'] ?? 0;
                $cintura = $_POST['cintura'] ?? 0;
                $cadera = $_POST['cadera'] ?? 0;
                $pierna = $_POST['pierna'] ?? 0;
                $pantorrilla = $_POST['pantorrilla'] ?? 0;

                $sql = "INSERT INTO MEDIDAS_CLIENTE (id_cliente, peso, pecho, brazo, cintura, cadera, pierna, pantorrilla) 
                        VALUES (:id_cliente, :peso, :pecho, :brazo, :cintura, :cadera, :pierna, :pantorrilla)";
                $stmt = $this->conn->prepare($sql);
                
                $stmt->execute([
                    ':id_cliente' => $id_cliente,
                    ':peso' => $peso,
                    ':pecho' => $pecho,
                    ':brazo' => $brazo,
                    ':cintura' => $cintura,
                    ':cadera' => $cadera,
                    ':pierna' => $pierna,
                    ':pantorrilla' => $pantorrilla
                ]);

                echo json_encode(['success' => true, 'message' => 'Medidas guardadas correctamente.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }

    public function guardar_rutina() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id_cliente = $_POST['id_cliente'] ?? 0;
                $dia_semana = $_POST['dia_semana'] ?? 0;
                $descripcion = $_POST['descripcion_rutina'] ?? '';

                if(empty($descripcion)){
                    $sql = "DELETE FROM RUTINAS_CLIENTE WHERE id_cliente = :id_cliente AND dia_semana = :dia_semana";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([':id_cliente' => $id_cliente, ':dia_semana' => $dia_semana]);
                } else {
                    $sql_check = "SELECT id_rutina FROM RUTINAS_CLIENTE WHERE id_cliente = :id_cliente AND dia_semana = :dia_semana";
                    $stmt_check = $this->conn->prepare($sql_check);
                    $stmt_check->execute([':id_cliente' => $id_cliente, ':dia_semana' => $dia_semana]);
                    
                    if($stmt_check->fetch()) {
                        $sql = "UPDATE RUTINAS_CLIENTE SET descripcion_rutina = :desc WHERE id_cliente = :id_cliente AND dia_semana = :dia_semana";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute([':desc' => $descripcion, ':id_cliente' => $id_cliente, ':dia_semana' => $dia_semana]);
                    } else {
                        $sql = "INSERT INTO RUTINAS_CLIENTE (id_cliente, dia_semana, descripcion_rutina) VALUES (:id_cliente, :dia_semana, :desc)";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->execute([':id_cliente' => $id_cliente, ':dia_semana' => $dia_semana, ':desc' => $descripcion]);
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Rutina actualizada correctamente.']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }
}

// Router
$action = $_GET['action'] ?? '';
$controller = new ProgresoController();
if (method_exists($controller, $action)) {
    $controller->$action();
}
?>
